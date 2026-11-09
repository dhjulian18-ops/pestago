<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\OrderItem;
use App\Models\VendorProfile;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * List all conversations for the current user
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'vendor') {
            $vendor = $user->vendorProfile;
            $conversations = Conversation::with(['customer', 'vendorProfile', 'admin', 'latestMessage', 'orderItem.service'])
                ->where(function ($q) use ($user, $vendor) {
                    if ($vendor) {
                        $q->where('vendor_profile_id', $vendor->id);
                    }
                    // Vendor also sees their support chat with CS
                    $q->orWhere(function ($sub) use ($user) {
                        $sub->where('customer_id', $user->id)->where('type', 'support');
                    });
                })
                ->orderByDesc('last_message_at')
                ->paginate(20);
        } else {
            $conversations = Conversation::with(['customer', 'vendorProfile', 'admin', 'latestMessage', 'orderItem.service'])
                ->where('customer_id', $user->id)
                ->orderByDesc('last_message_at')
                ->paginate(20);
        }

        return view('chat.index', compact('conversations'));
    }

    /**
     * Show a single conversation
     */
    public function show(Conversation $conversation)
    {
        $user = auth()->user();

        // Authorization
        if ($conversation->type === 'support') {
            if ($conversation->customer_id !== $user->id && !$user->isAdmin()) {
                abort(403, 'Akses tidak diizinkan.');
            }
        } elseif ($user->role === 'vendor') {
            $vendor = $user->vendorProfile;
            if (!$vendor || $conversation->vendor_profile_id !== $vendor->id) {
                abort(403, 'Akses tidak diizinkan.');
            }
        } else {
            if ($conversation->customer_id !== $user->id) {
                abort(403, 'Akses tidak diizinkan.');
            }
        }

        $conversation->load(['customer.vendorProfile', 'vendorProfile', 'admin', 'orderItem.service', 'messages.sender']);

        // Mark messages as read
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('chat.show', compact('conversation'));
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $user = auth()->user();

        // Authorization
        if ($conversation->type === 'support') {
            if ($conversation->customer_id !== $user->id && !$user->isAdmin()) {
                abort(403, 'Akses tidak diizinkan.');
            }
        } elseif ($user->role === 'vendor') {
            $vendor = $user->vendorProfile;
            if (!$vendor || $conversation->vendor_profile_id !== $vendor->id) {
                abort(403, 'Akses tidak diizinkan.');
            }
        } else {
            if ($conversation->customer_id !== $user->id) {
                abort(403, 'Akses tidak diizinkan.');
            }
        }

        $request->validate([
            'message' => 'required|string|max:2000',
            'attachment' => 'nullable|file|max:5120',
        ]);

        $attachment = null;
        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment')->store('chat_attachments', 'public');
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $request->message,
            'attachment' => $attachment,
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Notifications
        if ($conversation->type === 'support') {
            if ($user->isAdmin()) {
                Notification::create([
                    'user_id' => $conversation->customer_id,
                    'type' => 'support_reply',
                    'title' => 'Balasan dari Customer Care PestaGo',
                    'message' => \Illuminate\Support\Str::limit($request->message, 50),
                    'data' => ['conversation_id' => $conversation->id],
                ]);
            } else {
                // Notifikasi ke seluruh staf admin/CS
                $senderDesc = $user->name . ($user->role === 'vendor' ? ' (' . ($user->vendorProfile->business_name ?? 'Vendor') . ')' : '');
                $admins = User::where('role', 'super_admin')
                    ->orWhere(function ($q) {
                        $q->where('role', 'admin')->where('permissions', 'like', '%chats%');
                    })->get();

                foreach ($admins as $adm) {
                    Notification::create([
                        'user_id' => $adm->id,
                        'type' => 'support_chat',
                        'title' => 'Pesan Bantuan CS Baru',
                        'message' => "{$senderDesc}: " . \Illuminate\Support\Str::limit($request->message, 50),
                        'data' => ['conversation_id' => $conversation->id],
                    ]);
                }
            }
        } else {
            if ($user->role === 'vendor') {
                $recipientId = $conversation->customer_id;
                $senderName = $user->vendorProfile->business_name ?? $user->name;
            } else {
                $recipientId = $conversation->vendorProfile->user_id;
                $senderName = $user->name;
            }

            Notification::create([
                'user_id' => $recipientId,
                'type' => 'new_message',
                'title' => 'Pesan Baru',
                'message' => "{$senderName}: " . \Illuminate\Support\Str::limit($request->message, 50),
                'data' => ['conversation_id' => $conversation->id],
            ]);
        }

        return back()->with('success', 'Pesan terkirim.');
    }

    /**
     * Start/find a support conversation with PestaGo Customer Care
     */
    public function startSupportChat(Request $request)
    {
        $user = auth()->user();

        $conversation = Conversation::where('type', 'support')
            ->where('customer_id', $user->id)
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'type' => 'support',
                'customer_id' => $user->id,
                'support_subject' => $request->subject ?: 'Bantuan & Layanan Pelanggan PestaGo',
                'last_message_at' => now(),
            ]);

            // Pesan sambutan otomatis dari akun Super Admin / Customer Care
            $admin = User::where('role', 'super_admin')->first();
            if ($admin) {
                Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $admin->id,
                    'message' => "Halo {$user->name}! Selamat datang di Pusat Bantuan & Customer Care PestaGo. Ada yang bisa tim kami bantu hari ini?",
                ]);
            }
        }

        if ($request->filled('message')) {
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'message' => $request->message,
            ]);
            $conversation->update(['last_message_at' => now()]);
        }

        return redirect()->route('chat.show', $conversation);
    }

    /**
     * Start/find a conversation with a vendor
     */
    public function startConversation(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'customer') abort(403);

        $request->validate([
            'vendor_profile_id' => 'required|exists:vendor_profiles,id',
            'message' => 'required|string|max:2000',
        ]);

        $vendorProfileId = $request->vendor_profile_id;

        $conversation = Conversation::where('type', 'vendor')
            ->where('customer_id', $user->id)
            ->where('vendor_profile_id', $vendorProfileId)
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'type' => 'vendor',
                'customer_id' => $user->id,
                'vendor_profile_id' => $vendorProfileId,
                'last_message_at' => now(),
            ]);
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $request->message,
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Notify vendor
        $vendorProfile = VendorProfile::find($vendorProfileId);
        Notification::create([
            'user_id' => $vendorProfile->user_id,
            'type' => 'new_message',
            'title' => 'Pesan Baru dari Customer',
            'message' => "{$user->name}: " . \Illuminate\Support\Str::limit($request->message, 50),
            'data' => ['conversation_id' => $conversation->id],
        ]);

        return redirect()->route('chat.show', $conversation)
            ->with('success', 'Percakapan dimulai.');
    }
}

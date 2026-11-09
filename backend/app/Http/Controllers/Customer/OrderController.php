<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderProgressProof;
use App\Models\Payment;
use App\Models\Notification;
use App\Models\Refund;
use App\Models\Service;
use App\Services\OrderWorkflowService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderWorkflowService $workflow;

    public function __construct(OrderWorkflowService $workflow)
    {
        $this->workflow = $workflow;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $status = $request->get('status', '');

        $query = Order::with(['items.vendorProfile', 'items.service', 'payment', 'event'])
            ->where('user_id', $user->id);

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->orderByDesc('created_at')->paginate(10);

        return view('customer.orders.index', compact('orders', 'status'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) abort(403);

        $order->load([
            'items.service.location',
            'items.vendorProfile',
            'items.review',
            'items.progressProofs.uploader',
            'payment',
            'event',
            'refunds',
        ]);

        // Jika ada item yang ditolak, siapkan alternatif vendor
        $alternatives = collect();
        $rejectedItems = $order->items->where('status', 'rejected');
        if ($rejectedItems->isNotEmpty()) {
            $rejectedItem = $rejectedItems->first();
            $alternatives = Service::with(['vendorProfile', 'primaryImage'])
                ->where('status', 'published')
                ->where('is_active', true)
                ->where('id', '!=', $rejectedItem->service_id)
                ->where('category_id', $rejectedItem->service->category_id ?? null)
                ->whereHas('vendorProfile', fn($q) => $q->where('status', 'active')->where('id', '!=', $rejectedItem->vendor_profile_id))
                ->orderByDesc('rating_avg')
                ->limit(6)
                ->get();
        }

        return view('customer.orders.show', compact('order', 'alternatives'));
    }

    public function payment(Order $order)
    {
        if ($order->user_id !== auth()->id()) abort(403);
        if (!in_array($order->status, ['pending_payment'])) {
            return redirect()->route('customer.orders.show', $order)
                ->with('info', 'Pesanan ini sudah tidak dalam status menunggu pembayaran.');
        }

        $order->load(['items.service', 'user', 'payment']);
        $payment = $order->payment;

        // Check if there's already a pending Core API charge
        $existingCharge = null;
        if ($payment && $payment->gateway_response) {
            $gr = $payment->gateway_response;
            if (is_array($gr) && isset($gr['transaction_status']) && $gr['transaction_status'] === 'pending') {
                $existingCharge = [
                    'success' => true,
                    'payment_method' => $payment->payment_method,
                    'expiry_time' => $gr['expiry_time'] ?? null,
                ];
                if (isset($gr['va_numbers'][0])) {
                    $existingCharge['va_number'] = $gr['va_numbers'][0]['va_number'];
                    $existingCharge['bank'] = $gr['va_numbers'][0]['bank'];
                }
                if (isset($gr['bill_key'])) {
                    $existingCharge['bill_key'] = $gr['bill_key'];
                    $existingCharge['biller_code'] = $gr['biller_code'] ?? '';
                }
                if (isset($gr['permata_va_number'])) {
                    $existingCharge['va_number'] = $gr['permata_va_number'];
                    $existingCharge['bank'] = 'permata';
                }
                if (isset($gr['actions'])) {
                    foreach ($gr['actions'] as $action) {
                        if ($action['name'] === 'generate-qr-code') $existingCharge['qr_url'] = $action['url'];
                        if ($action['name'] === 'deeplink-redirect') $existingCharge['deeplink'] = $action['url'];
                    }
                }
            }
        }

        return view('customer.orders.payment', compact('order', 'payment', 'existingCharge'));
    }

    public function confirmPayment(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) abort(403);

        $request->validate([
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $payment = $order->payment;
        if (!$payment || $payment->status !== 'pending') {
            return back()->with('error', 'Pembayaran tidak valid.');
        }

        $path = $request->file('payment_proof')->store('payment_proofs', 'public');

        $gatewayResponse = $payment->gateway_response ?? [];
        $gatewayResponse['proof'] = $path;
        $gatewayResponse['submitted_at'] = now()->toISOString();

        $payment->update([
            'status' => 'processing',
            'gateway_response' => $gatewayResponse,
        ]);

        $order->update(['status' => 'payment_processing']);

        // Notifikasi ke admin
        $admins = \App\Models\User::where('role', 'super_admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'payment_submitted',
                'title' => 'Bukti Pembayaran Dikirim',
                'message' => "Order #{$order->order_number} memerlukan verifikasi pembayaran.",
                'data' => ['order_id' => $order->id],
            ]);
        }

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Bukti pembayaran berhasil dikirim. Admin akan memverifikasi dalam 1x24 jam.');
    }

    /**
     * Customer konfirmasi acara selesai untuk 1 order item
     */
    public function confirmItemComplete(Request $request, OrderItem $item)
    {
        $order = $item->order;
        if ($order->user_id !== auth()->id()) abort(403);

        if (!in_array($item->status, ['in_progress', 'completed'])) {
            return back()->with('error', 'Item belum bisa dikonfirmasi selesai pada status ini.');
        }

        try {
            $result = $this->workflow->customerConfirmComplete($item, auth()->id());
            return back()->with('success', "Layanan \"{$item->service_name_snapshot}\" dikonfirmasi selesai! Saldo vendor akan dirilis.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Customer pilih vendor pengganti (setelah vendor menolak)
     */
    public function chooseReplacement(Request $request, OrderItem $rejectedItem)
    {
        $order = $rejectedItem->order;
        if ($order->user_id !== auth()->id()) abort(403);

        $request->validate([
            'new_service_id' => 'required|exists:services,id',
        ]);

        if ($rejectedItem->status !== 'rejected') {
            return back()->with('error', 'Item ini tidak dalam status ditolak.');
        }

        $newService = Service::with('vendorProfile')
            ->where('status', 'published')
            ->where('is_active', true)
            ->findOrFail($request->new_service_id);

        try {
            $newItem = $this->workflow->replaceWithNewService($rejectedItem, $newService, $order);
            return back()->with('success', "Vendor pengganti \"{$newService->name}\" berhasil dipilih. Menunggu konfirmasi vendor baru.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Customer ajukan refund untuk item yang ditolak
     */
    public function requestItemRefund(Request $request, OrderItem $item)
    {
        $order = $item->order;
        if ($order->user_id !== auth()->id()) abort(403);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($item->status !== 'rejected') {
            return back()->with('error', 'Refund hanya bisa diajukan untuk item yang ditolak vendor.');
        }

        $payment = $order->payment;
        if (!$payment) {
            return back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        Refund::create([
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'payment_id' => $payment->id,
            'amount' => $item->subtotal,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        // Notifikasi ke admin
        $admins = \App\Models\User::where('role', 'super_admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'refund_requested',
                'title' => 'Pengajuan Refund',
                'message' => "Customer {$order->user->name} mengajukan refund untuk \"{$item->service_name_snapshot}\" (Order #{$order->order_number}).",
                'data' => ['order_id' => $order->id, 'order_item_id' => $item->id],
            ]);
        }

        return back()->with('success', 'Pengajuan pengembalian dana berhasil dikirim. Admin akan memproses dalam 1-3 hari kerja.');
    }

    /**
     * Upload foto bukti progres oleh customer
     */
    public function uploadProof(Request $request, OrderItem $item)
    {
        $order = $item->order;
        if ($order->user_id !== auth()->id()) abort(403);

        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'caption' => 'nullable|string|max:500',
            'type' => 'required|in:setup,in_progress,completion',
        ]);

        $path = $request->file('image')->store('progress_proofs', 'public');

        OrderProgressProof::create([
            'order_item_id' => $item->id,
            'uploaded_by' => auth()->id(),
            'type' => $request->type,
            'image_path' => $path,
            'caption' => $request->caption,
        ]);

        return back()->with('success', 'Foto bukti progres berhasil diunggah.');
    }

    public function cancelItem(Request $request, OrderItem $item)
    {
        $order = $item->order;
        if ($order->user_id !== auth()->id()) abort(403);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if (!in_array($item->status, ['pending_confirmation', 'confirmed'])) {
            return back()->with('error', 'Item ini tidak bisa dibatalkan pada status saat ini.');
        }

        $item->update([
            'status' => 'cancelled',
            'vendor_notes' => 'Dibatalkan oleh customer: ' . $request->reason,
        ]);

        // Cek apakah semua item sudah dibatalkan
        $allCancelled = $order->items()->where('status', '!=', 'cancelled')->count() === 0;
        if ($allCancelled) {
            $order->update(['status' => 'cancelled']);
        }

        Notification::create([
            'user_id' => $item->vendorProfile->user_id,
            'type' => 'order_cancelled',
            'title' => 'Pesanan Dibatalkan',
            'message' => "Pesanan '{$item->service_name_snapshot}' dibatalkan oleh customer: {$request->reason}",
            'data' => ['order_item_id' => $item->id],
        ]);

        return back()->with('success', 'Item berhasil dibatalkan.');
    }

    /**
     * Invoice view
     */
    public function invoice(Order $order)
    {
        if ($order->user_id !== auth()->id()) abort(403);

        $order->load(['items.service', 'items.vendorProfile', 'payment', 'event', 'user']);

        $platformFeePercentage = \App\Models\PlatformSetting::platformFeePercentage();
        $taxPercentage = \App\Models\PlatformSetting::taxPercentage();

        return view('customer.orders.invoice', compact('order', 'platformFeePercentage', 'taxPercentage'));
    }

    /**
     * Create Midtrans Core API charge (AJAX, custom payment UI)
     */
    public function createCharge(Request $request, Order $order, \App\Services\MidtransService $midtransService)
    {
        if ($order->user_id !== auth()->id()) abort(403);
        if ($order->status !== 'pending_payment') {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak menunggu pembayaran.'], 400);
        }

        $request->validate([
            'payment_method' => 'required|in:bca,bni,bri,mandiri,permata,gopay,qris',
        ]);

        $result = $midtransService->createCoreCharge($order, $request->payment_method);
        return response()->json($result);
    }

    /**
     * Check payment status (AJAX polling)
     */
    public function checkPaymentStatus(Order $order)
    {
        if ($order->user_id !== auth()->id()) abort(403);

        $order->refresh();
        $payment = $order->payment;

        return response()->json([
            'order_status' => $order->status,
            'payment_status' => $payment ? $payment->status : 'unknown',
            'is_paid' => $order->status === 'paid' || ($payment && $payment->status === 'paid'),
        ]);
    }

    /**
     * Payment success page
     */
    public function paymentSuccess(Order $order)
    {
        if ($order->user_id !== auth()->id()) abort(403);

        $order->load(['items.service', 'items.vendorProfile', 'payment', 'event']);

        return view('customer.orders.payment-success', compact('order'));
    }
}

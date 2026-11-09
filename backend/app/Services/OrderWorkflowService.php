<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class OrderWorkflowService
{
    protected EscrowService $escrow;

    public function __construct(EscrowService $escrow)
    {
        $this->escrow = $escrow;
    }

    /**
     * Vendor konfirmasi pesanan
     */
    public function vendorConfirm(OrderItem $item): OrderItem
    {
        if ($item->status !== 'pending_confirmation') {
            throw new \Exception('Item tidak bisa dikonfirmasi pada status ini.');
        }

        $item->update([
            'status' => 'confirmed',
            'vendor_responded_at' => now(),
        ]);

        // Update order status
        $this->syncOrderStatus($item->order);

        // Notifikasi ke customer
        Notification::create([
            'user_id' => $item->order->user_id,
            'type' => 'order_confirmed',
            'title' => 'Pesanan Dikonfirmasi! ✅',
            'message' => "Vendor {$item->vendorProfile->business_name} telah mengkonfirmasi pesanan \"{$item->service_name_snapshot}\".",
            'data' => ['order_id' => $item->order_id, 'order_item_id' => $item->id],
        ]);

        return $item;
    }

    /**
     * Vendor tolak pesanan — sistem akan rekomendasikan vendor serupa
     */
    public function vendorReject(OrderItem $item, string $reason): array
    {
        if ($item->status !== 'pending_confirmation') {
            throw new \Exception('Item tidak bisa ditolak pada status ini.');
        }

        $item->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'vendor_responded_at' => now(),
        ]);

        // Jika non-tunai, refund escrow
        if (!$item->order->isCashPayment() && $item->order->payment?->status === 'paid') {
            $this->escrow->refundEscrow($item, "Ditolak vendor: {$reason}");
        }

        // Cari layanan serupa untuk rekomendasi
        $alternatives = Service::with(['vendorProfile', 'primaryImage'])
            ->where('status', 'published')
            ->where('is_active', true)
            ->where('id', '!=', $item->service_id) // Bukan layanan yang sama
            ->where('category_id', $item->service->category_id ?? null)
            ->whereHas('vendorProfile', fn($q) => $q->where('status', 'active')->where('id', '!=', $item->vendor_profile_id))
            ->orderByDesc('rating_avg')
            ->limit(6)
            ->get();

        $this->syncOrderStatus($item->order);

        // Notifikasi ke customer
        Notification::create([
            'user_id' => $item->order->user_id,
            'type' => 'order_rejected',
            'title' => 'Pesanan Ditolak ❌',
            'message' => "Vendor {$item->vendorProfile->business_name} menolak pesanan \"{$item->service_name_snapshot}\". Alasan: {$reason}. Anda bisa memilih vendor pengganti atau mengajukan pengembalian dana.",
            'data' => ['order_id' => $item->order_id, 'order_item_id' => $item->id],
        ]);

        return [
            'item' => $item,
            'alternatives' => $alternatives,
        ];
    }

    /**
     * Vendor mulai mengerjakan pesanan
     */
    public function vendorStartProgress(OrderItem $item): OrderItem
    {
        if ($item->status !== 'confirmed') {
            throw new \Exception('Item harus dikonfirmasi dulu sebelum mulai dikerjakan.');
        }

        $item->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $this->syncOrderStatus($item->order);

        Notification::create([
            'user_id' => $item->order->user_id,
            'type' => 'order_in_progress',
            'title' => 'Vendor Mulai Bekerja 🔧',
            'message' => "{$item->vendorProfile->business_name} mulai mengerjakan \"{$item->service_name_snapshot}\".",
            'data' => ['order_id' => $item->order_id, 'order_item_id' => $item->id],
        ]);

        return $item;
    }

    /**
     * Customer konfirmasi acara selesai → saldo dirilis ke vendor
     */
    public function customerConfirmComplete(OrderItem $item, ?int $userId = null): array
    {
        if (!in_array($item->status, ['in_progress', 'completed'])) {
            throw new \Exception('Item tidak bisa dikonfirmasi selesai pada status ini.');
        }

        $item->update([
            'status' => 'completed',
            'completed_at' => $item->completed_at ?? now(),
            'customer_confirmed_at' => now(),
        ]);

        // Rilis escrow ke vendor (hanya untuk non-tunai)
        $releaseResult = null;
        if (!$item->order->isCashPayment() && $item->order->payment?->status === 'paid') {
            $releaseResult = $this->escrow->releaseToVendor($item);
        }

        $this->syncOrderStatus($item->order);

        // Notifikasi ke vendor: saldo dirilis
        Notification::create([
            'user_id' => $item->vendorProfile->user_id,
            'type' => 'escrow_released',
            'title' => 'Saldo Dirilis! 💰',
            'message' => "Customer telah mengkonfirmasi acara selesai untuk \"{$item->service_name_snapshot}\". Saldo sudah dirilis ke akun Anda.",
            'data' => ['order_item_id' => $item->id],
        ]);

        return [
            'item' => $item,
            'release_result' => $releaseResult,
        ];
    }

    /**
     * Customer pilih vendor pengganti (setelah vendor menolak)
     */
    public function replaceWithNewService(OrderItem $rejectedItem, Service $newService, Order $order): OrderItem
    {
        return DB::transaction(function () use ($rejectedItem, $newService, $order) {
            // Buat order item baru dengan layanan pengganti
            $newItem = OrderItem::create([
                'order_id' => $order->id,
                'vendor_profile_id' => $newService->vendor_profile_id,
                'service_id' => $newService->id,
                'service_name_snapshot' => $newService->name,
                'price_snapshot' => $newService->price,
                'price_unit_snapshot' => $newService->price_unit,
                'quantity' => $rejectedItem->quantity,
                'subtotal' => $newService->price * $rejectedItem->quantity,
                'booking_date' => $rejectedItem->booking_date,
                'customer_notes' => $rejectedItem->customer_notes,
                'status' => 'pending_confirmation',
            ]);

            // Update total order
            $this->recalculateOrderTotal($order);

            // Jika non-tunai, escrow-in saldo vendor baru
            if (!$order->isCashPayment() && $order->payment?->status === 'paid') {
                $this->escrow->escrowIn($newItem);
            }

            // Notifikasi ke vendor baru
            Notification::create([
                'user_id' => $newService->vendorProfile->user_id,
                'type' => 'order_new',
                'title' => 'Pesanan Baru!',
                'message' => "Ada pesanan baru dari {$order->user->name} untuk layanan \"{$newService->name}\".",
                'data' => ['order_id' => $order->id, 'order_item_id' => $newItem->id],
            ]);

            return $newItem;
        });
    }

    /**
     * Sinkronisasi status order berdasarkan status semua item
     */
    public function syncOrderStatus(Order $order): void
    {
        $order->refresh();
        $items = $order->items;
        $statuses = $items->pluck('status')->unique();

        $newStatus = $order->status; // Default: tidak berubah

        if ($statuses->count() === 1) {
            $onlyStatus = $statuses->first();
            $newStatus = match ($onlyStatus) {
                'confirmed' => 'confirmed',
                'in_progress' => 'in_progress',
                'completed' => 'completed',
                'cancelled' => 'cancelled',
                'rejected' => 'cancelled',
                default => $order->status,
            };
        } else {
            // Mixed statuses
            if ($statuses->contains('confirmed') || $statuses->contains('pending_confirmation')) {
                $newStatus = 'partially_confirmed';
            }
            if ($statuses->contains('in_progress')) {
                $newStatus = 'in_progress';
            }
            // Jika semua yang aktif sudah completed
            $activeItems = $items->whereNotIn('status', ['cancelled', 'rejected']);
            if ($activeItems->isNotEmpty() && $activeItems->every(fn($i) => $i->status === 'completed')) {
                $newStatus = 'completed';
            }
        }

        if ($newStatus !== $order->status) {
            $order->update(['status' => $newStatus]);
        }
    }

    /**
     * Recalculate order total setelah ada perubahan item
     */
    protected function recalculateOrderTotal(Order $order): void
    {
        $order->refresh();
        $activeItems = $order->items()->whereNotIn('status', ['cancelled', 'rejected'])->get();
        $subtotal = $activeItems->sum('subtotal');
        $platformFee = round($subtotal * (\App\Models\PlatformSetting::platformFeePercentage() / 100));
        $order->update([
            'subtotal' => $subtotal,
            'platform_fee' => $platformFee,
            'total_amount' => $subtotal + $platformFee,
        ]);
    }
}

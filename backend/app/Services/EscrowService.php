<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\PlatformSetting;
use App\Models\VendorWallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class EscrowService
{
    /**
     * Masukkan dana ke escrow vendor saat customer bayar (non-tunai)
     */
    public function escrowIn(OrderItem $item): WalletTransaction
    {
        return DB::transaction(function () use ($item) {
            $vendorProfile = $item->vendorProfile;
            $wallet = $vendorProfile->getOrCreateWallet();

            // Hitung amount untuk vendor (setelah potong platform fee per item)
            $amount = (float) $item->subtotal;

            // Tambah pending_balance
            $wallet->increment('pending_balance', $amount);

            return WalletTransaction::create([
                'vendor_wallet_id' => $wallet->id,
                'order_item_id' => $item->id,
                'type' => 'escrow_in',
                'amount' => $amount,
                'description' => "Pembayaran escrow dari pesanan #{$item->order->order_number} — {$item->service_name_snapshot}",
            ]);
        });
    }

    /**
     * Rilis saldo escrow ke balance vendor (setelah acara selesai + approved)
     * Dipotong pajak sesuai setting admin
     */
    public function releaseToVendor(OrderItem $item): array
    {
        return DB::transaction(function () use ($item) {
            $vendorProfile = $item->vendorProfile;
            $wallet = $vendorProfile->getOrCreateWallet();

            $grossAmount = (float) $item->subtotal;
            $taxPercentage = PlatformSetting::taxPercentage();
            $taxAmount = round($grossAmount * ($taxPercentage / 100));
            $netAmount = $grossAmount - $taxAmount;

            // Pindahkan dari pending_balance ke balance (dikurangi pajak)
            $wallet->decrement('pending_balance', $grossAmount);
            $wallet->increment('balance', $netAmount);
            $wallet->increment('total_earned', $netAmount);

            // Buat 2 transaksi: released + tax_deducted
            $releaseTxn = WalletTransaction::create([
                'vendor_wallet_id' => $wallet->id,
                'order_item_id' => $item->id,
                'type' => 'released',
                'amount' => $netAmount,
                'tax_amount' => $taxAmount,
                'description' => "Saldo dirilis untuk {$item->service_name_snapshot} (Order #{$item->order->order_number}). Pajak {$taxPercentage}%: Rp " . number_format($taxAmount, 0, ',', '.'),
            ]);

            $taxTxn = null;
            if ($taxAmount > 0) {
                $taxTxn = WalletTransaction::create([
                    'vendor_wallet_id' => $wallet->id,
                    'order_item_id' => $item->id,
                    'type' => 'tax_deducted',
                    'amount' => $taxAmount,
                    'description' => "Potongan pajak {$taxPercentage}% dari {$item->service_name_snapshot}",
                ]);
            }

            return [
                'release_transaction' => $releaseTxn,
                'tax_transaction' => $taxTxn,
                'gross_amount' => $grossAmount,
                'tax_amount' => $taxAmount,
                'net_amount' => $netAmount,
            ];
        });
    }

    /**
     * Refund escrow (vendor tolak atau customer batal)
     */
    public function refundEscrow(OrderItem $item, string $reason = ''): WalletTransaction
    {
        return DB::transaction(function () use ($item, $reason) {
            $vendorProfile = $item->vendorProfile;
            $wallet = $vendorProfile->getOrCreateWallet();
            $amount = (float) $item->subtotal;

            // Kurangi pending_balance
            $wallet->decrement('pending_balance', max(0, min($amount, (float) $wallet->pending_balance)));

            return WalletTransaction::create([
                'vendor_wallet_id' => $wallet->id,
                'order_item_id' => $item->id,
                'type' => 'refunded',
                'amount' => $amount,
                'description' => "Refund escrow: {$item->service_name_snapshot}. Alasan: {$reason}",
            ]);
        });
    }
}

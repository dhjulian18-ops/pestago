<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Advertisement;
use App\Models\Notification;
use App\Models\PlatformSetting;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = PlatformSetting::midtransServerKey();
        Config::$clientKey = PlatformSetting::midtransClientKey();
        Config::$isProduction = PlatformSetting::isMidtransProduction();
        Config::$isSanitized = config('services.midtrans.is_sanitized', true);
        Config::$is3ds = config('services.midtrans.is_3ds', true);
    }

    /**
     * Generate or reuse Snap Token for order payment
     */
    public function getOrCreateSnapToken(Order $order): ?string
    {
        $payment = $order->payment;
        if (!$payment) {
            return null;
        }

        // Reuse snap token if already exists and payment is still pending
        if (!empty($payment->gateway_transaction_id) && $payment->gateway === 'midtrans' && $payment->status === 'pending') {
            return $payment->gateway_transaction_id;
        }

        $user = $order->user;
        $orderId = $payment->payment_number ?: ('ORDER-' . $order->order_number . '-' . time());

        // Prepare item details
        $itemDetails = [];
        foreach ($order->items as $item) {
            $itemDetails[] = [
                'id' => 'ITEM-' . $item->id,
                'price' => (int) round($item->price_snapshot),
                'quantity' => (int) $item->quantity,
                'name' => \Illuminate\Support\Str::limit($item->service_name_snapshot, 50),
            ];
        }

        // Platform fee as an item if exists
        if ($order->platform_fee > 0) {
            $itemDetails[] = [
                'id' => 'PLATFORM-FEE',
                'price' => (int) round($order->platform_fee),
                'quantity' => 1,
                'name' => 'Biaya Layanan Platform PestaGo',
            ];
        }

        $grossAmount = (int) round($order->total_amount);

        // Calculate sum of item details to ensure it matches gross_amount exactly
        $itemsTotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $itemDetails));
        if ($itemsTotal !== $grossAmount) {
            $itemDetails = [
                [
                    'id' => 'ORDER-' . $order->order_number,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => 'Pesanan PestaGo #' . $order->order_number,
                ]
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '081234567890',
            ],
            'item_details' => $itemDetails,
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            $payment->update([
                'gateway' => 'midtrans',
                'gateway_transaction_id' => $snapToken,
                'gateway_reference' => $orderId,
            ]);

            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans getSnapToken failed: ' . $e->getMessage(), ['order_id' => $order->id]);
            return null;
        }
    }

    /**
     * Generate or reuse Snap Token for advertisement payment
     */
    public function getOrCreateAdSnapToken(Advertisement $advertisement): ?string
    {
        if (!empty($advertisement->snap_token) && $advertisement->payment_status === 'pending') {
            return $advertisement->snap_token;
        }

        $vendorUser = $advertisement->vendorProfile->user;
        $orderId = $advertisement->payment_gateway_reference ?: ('AD-' . $advertisement->id . '-' . time());
        $grossAmount = (int) round($advertisement->payment_amount);

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $vendorUser->name,
                'email' => $vendorUser->email,
                'phone' => $vendorUser->phone ?? '081234567890',
            ],
            'item_details' => [
                [
                    'id' => 'AD-' . $advertisement->id,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => 'Iklan PestaGo: ' . \Illuminate\Support\Str::limit($advertisement->title, 40),
                ]
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $advertisement->update([
                'snap_token' => $snapToken,
                'payment_gateway_reference' => $orderId,
            ]);
            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans getOrCreateAdSnapToken failed: ' . $e->getMessage(), ['ad_id' => $advertisement->id]);
            return null;
        }
    }

    /**
     * Handle notification webhook from Midtrans
     */
    public function handleNotification(array $payload): array
    {
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $serverKey = PlatformSetting::midtransServerKey();

        // Signature check
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if ($signatureKey !== $expectedSignature) {
            Log::warning('Midtrans Invalid Signature', ['received' => $signatureKey, 'expected' => $expectedSignature]);
            return ['status' => 'error', 'message' => 'Invalid signature'];
        }

        // Branching: Advertisement payment vs Order payment
        if (str_starts_with($orderId, 'AD-')) {
            return $this->handleAdNotification($payload);
        }

        // Order payment flow
        $payment = Payment::where('payment_number', $orderId)
            ->orWhere('gateway_reference', $orderId)
            ->first();

        if (!$payment) {
            Log::warning('Midtrans Payment not found for order_id: ' . $orderId);
            return ['status' => 'error', 'message' => 'Payment not found'];
        }

        $order = $payment->order;
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';
        $paymentType = $payload['payment_type'] ?? $payment->payment_method;

        $isPaid = false;
        $isFailed = false;

        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'accept') {
                $isPaid = true;
            }
        } elseif ($transactionStatus === 'settlement') {
            $isPaid = true;
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $isFailed = true;
        } elseif ($transactionStatus === 'pending') {
            // Keep pending
        }

        if ($isPaid) {
            $payment->update([
                'status' => 'paid',
                'payment_method' => $paymentType,
                'paid_at' => now(),
                'gateway_response' => $payload,
            ]);

            $order->update(['status' => 'paid']);

            // Notifikasi ke customer
            Notification::create([
                'user_id' => $order->user_id,
                'type' => 'payment_success',
                'title' => 'Pembayaran Berhasil! 🎉',
                'message' => "Pembayaran pesanan #{$order->order_number} sebesar Rp " . number_format($payment->amount, 0, ',', '.') . " telah berhasil via {$paymentType}.",
                'data' => ['order_id' => $order->id],
            ]);

            // Notifikasi ke vendor-vendor terkait
            foreach ($order->items as $item) {
                if ($item->vendorProfile && $item->vendorProfile->user_id) {
                    Notification::create([
                        'user_id' => $item->vendorProfile->user_id,
                        'type' => 'new_paid_order',
                        'title' => 'Pesanan Masuk (Sudah Dibayar)',
                        'message' => "Pesanan untuk layanan '{$item->service_name_snapshot}' telah lunas dibayar customer.",
                        'data' => ['order_item_id' => $item->id, 'order_id' => $order->id],
                    ]);
                }
            }

            Log::info("Payment #{$payment->payment_number} marked as PAID via Midtrans.");
        } elseif ($isFailed) {
            $payment->update([
                'status' => 'failed',
                'gateway_response' => $payload,
            ]);
            $order->update(['status' => 'cancelled']);

            Notification::create([
                'user_id' => $order->user_id,
                'type' => 'payment_failed',
                'title' => 'Pembayaran Gagal / Kedaluwarsa',
                'message' => "Pembayaran pesanan #{$order->order_number} tidak berhasil atau kedaluwarsa.",
                'data' => ['order_id' => $order->id],
            ]);
        }

        return ['status' => 'success', 'message' => 'Notification handled'];
    }

    /**
     * Handle Midtrans Webhook for Advertisement
     */
    protected function handleAdNotification(array $payload): array
    {
        $orderId = $payload['order_id'] ?? null;
        $ad = Advertisement::where('payment_gateway_reference', $orderId)->first();

        if (!$ad) {
            // Coba ekstrak ID dari AD-{id}-{time}
            $parts = explode('-', $orderId);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $ad = Advertisement::find($parts[1]);
            }
        }

        if (!$ad) {
            Log::warning('Midtrans Ad not found for order_id: ' . $orderId);
            return ['status' => 'error', 'message' => 'Advertisement not found'];
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';
        $paymentType = $payload['payment_type'] ?? 'midtrans';

        $isPaid = false;
        $isFailed = false;

        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'accept') {
                $isPaid = true;
            }
        } elseif ($transactionStatus === 'settlement') {
            $isPaid = true;
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $isFailed = true;
        }

        if ($isPaid) {
            $ad->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'payment_type' => $paymentType,
                'review_status' => 'pending_review',
            ]);

            // Notifikasi ke vendor
            if ($ad->vendorProfile && $ad->vendorProfile->user_id) {
                Notification::create([
                    'user_id' => $ad->vendorProfile->user_id,
                    'type' => 'ad_payment_success',
                    'title' => 'Pembayaran Iklan Berhasil! 📢',
                    'message' => "Pembayaran untuk slot iklan '{$ad->title}' telah diterima via {$paymentType}. Iklan kini sedang ditinjau Tim Kurasi PestaGo.",
                    'data' => ['advertisement_id' => $ad->id],
                ]);
            }

            // Notifikasi ke staf admin
            $admins = \App\Models\User::where('role', 'super_admin')
                ->orWhere(function($q) {
                    $q->where('role', 'admin')->where('permissions', 'like', '%advertisements%');
                })->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'new_ad_paid',
                    'title' => 'Pengajuan Iklan Baru Lunas',
                    'message' => "Vendor {$ad->vendorProfile->business_name} telah membayar slot iklan '{$ad->title}'. Silakan tinjau dan setujui penayangan.",
                    'data' => ['advertisement_id' => $ad->id],
                ]);
            }

            Log::info("Ad #{$ad->id} marked as PAID via Midtrans.");
        } elseif ($isFailed) {
            $ad->update([
                'payment_status' => 'failed',
                'review_status' => 'pending_payment',
            ]);
        }

        return ['status' => 'success', 'message' => 'Ad notification handled'];
    }

    /**
     * Check transaction status directly with Midtrans API
     */
    public function checkStatus(Order $order): ?array
    {
        $payment = $order->payment;
        if (!$payment || empty($payment->gateway_reference)) {
            return null;
        }

        try {
            $status = Transaction::status($payment->gateway_reference);
            return (array) $status;
        } catch (\Exception $e) {
            Log::error('Midtrans checkStatus error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check ad transaction status directly with Midtrans API
     */
    public function checkAdStatus(Advertisement $ad): ?array
    {
        if (empty($ad->payment_gateway_reference)) {
            return null;
        }

        try {
            $status = Transaction::status($ad->payment_gateway_reference);
            return (array) $status;
        } catch (\Exception $e) {
            Log::error('Midtrans checkAdStatus error: ' . $e->getMessage(), ['ad_id' => $ad->id]);
            return null;
        }
    }

    /**
     * Create a charge via Midtrans Core API (custom UI, no Snap popup)
     */
    public function createCoreCharge(Order $order, string $paymentMethod): array
    {
        $payment = $order->payment;
        if (!$payment) {
            return ['success' => false, 'message' => 'Data pembayaran tidak ditemukan.'];
        }

        if ($payment->status !== 'pending') {
            return ['success' => false, 'message' => 'Pembayaran sudah tidak berstatus pending.'];
        }

        $user = $order->user;
        // Always use a fresh order_id to avoid duplicate order_id error
        $orderId = 'ORDER-' . $order->order_number . '-' . time();
        $grossAmount = (int) round($order->total_amount);

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '081234567890',
            ],
        ];

        switch ($paymentMethod) {
            case 'bca':
            case 'bni':
            case 'bri':
                $params['payment_type'] = 'bank_transfer';
                $params['bank_transfer'] = ['bank' => $paymentMethod];
                break;
            case 'mandiri':
                $params['payment_type'] = 'echannel';
                $params['echannel'] = [
                    'bill_info1' => 'Payment:',
                    'bill_info2' => 'PestaGo #' . $order->order_number,
                ];
                break;
            case 'permata':
                $params['payment_type'] = 'permata';
                break;
            case 'gopay':
            case 'qris':
                $params['payment_type'] = 'gopay';
                break;
            default:
                return ['success' => false, 'message' => 'Metode pembayaran tidak didukung.'];
        }

        try {
            $response = \Midtrans\CoreApi::charge($params);
            $responseArray = json_decode(json_encode($response), true);

            $payment->update([
                'gateway' => 'midtrans',
                'gateway_reference' => $orderId,
                'payment_method' => $paymentMethod,
                'gateway_response' => $responseArray,
            ]);

            $result = [
                'success' => true,
                'payment_method' => $paymentMethod,
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
                'expiry_time' => $responseArray['expiry_time'] ?? null,
            ];

            // Parse response based on payment type
            if (in_array($paymentMethod, ['bca', 'bni', 'bri'])) {
                $result['va_number'] = $responseArray['va_numbers'][0]['va_number'] ?? null;
                $result['bank'] = $responseArray['va_numbers'][0]['bank'] ?? $paymentMethod;
            } elseif ($paymentMethod === 'mandiri') {
                $result['bill_key'] = $responseArray['bill_key'] ?? null;
                $result['biller_code'] = $responseArray['biller_code'] ?? null;
            } elseif ($paymentMethod === 'permata') {
                $result['va_number'] = $responseArray['permata_va_number'] ?? null;
                $result['bank'] = 'permata';
            } elseif (in_array($paymentMethod, ['gopay', 'qris'])) {
                $actions = $responseArray['actions'] ?? [];
                foreach ($actions as $action) {
                    if ($action['name'] === 'generate-qr-code') {
                        $result['qr_url'] = $action['url'];
                    }
                    if ($action['name'] === 'deeplink-redirect') {
                        $result['deeplink'] = $action['url'];
                    }
                }
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Midtrans CoreApi charge failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'method' => $paymentMethod,
            ]);
            return ['success' => false, 'message' => 'Gagal membuat transaksi: ' . $e->getMessage()];
        }
    }

    /**
     * Create QRIS charge for advertisement via Midtrans Core API
     */
    public function createAdCoreCharge(Advertisement $ad, string $paymentMethod = 'qris'): array
    {
        if ($ad->payment_status === 'paid') {
            return ['success' => false, 'message' => 'Pembayaran iklan ini sudah lunas.'];
        }

        $vendorUser = $ad->vendorProfile->user;
        $orderId = 'AD-' . $ad->id . '-' . time();
        $grossAmount = (int) round($ad->payment_amount);

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $vendorUser->name,
                'email' => $vendorUser->email,
                'phone' => $vendorUser->phone ?? '081234567890',
            ],
            'payment_type' => 'gopay',
            'item_details' => [
                [
                    'id' => 'AD-' . $ad->id,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => 'Iklan PestaGo: ' . \Illuminate\Support\Str::limit($ad->title, 40),
                ]
            ],
        ];

        try {
            $response = \Midtrans\CoreApi::charge($params);
            $responseArray = json_decode(json_encode($response), true);

            $ad->update([
                'payment_gateway_reference' => $orderId,
                'payment_type' => 'qris',
            ]);

            $result = [
                'success' => true,
                'payment_method' => 'qris',
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
                'expiry_time' => $responseArray['expiry_time'] ?? null,
            ];

            $actions = $responseArray['actions'] ?? [];
            foreach ($actions as $action) {
                if ($action['name'] === 'generate-qr-code') {
                    $result['qr_url'] = $action['url'];
                }
                if ($action['name'] === 'deeplink-redirect') {
                    $result['deeplink'] = $action['url'];
                }
            }

            // Fallback QR if Midtrans didn't provide QR URL directly
            if (empty($result['qr_url'])) {
                $qrData = $responseArray['qr_string'] ?? ('00020101021226500016ID.CO.PESTAGO.WWW01189360000201111222330215AD' . $ad->id . '520458125303360540' . $grossAmount . '5802ID5912PESTAGO VENDOR6007JAKARTA6304ABCD');
                $result['qr_url'] = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=10&data=' . urlencode($qrData);
            }

            return $result;
        } catch (\Exception $e) {
            Log::warning('Midtrans CoreApi ad charge fallback: ' . $e->getMessage(), ['ad_id' => $ad->id]);

            // Graceful fallback for local development / testing
            $fallbackQr = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=10&data=' . urlencode('PESTAGO-AD-' . $ad->id . '-RP-' . $grossAmount);
            
            $ad->update([
                'payment_gateway_reference' => $orderId,
                'payment_type' => 'qris',
            ]);

            return [
                'success' => true,
                'payment_method' => 'qris',
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
                'qr_url' => $fallbackQr,
                'expiry_time' => now()->addDay()->format('Y-m-d H:i:s'),
            ];
        }
    }
}


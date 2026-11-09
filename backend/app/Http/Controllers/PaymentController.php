<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Webhook notification from Midtrans
     */
    public function handleNotification(Request $request, MidtransService $midtransService)
    {
        Log::info('Midtrans Webhook Received:', $request->all());

        $result = $midtransService->handleNotification($request->all());

        if ($result['status'] === 'error') {
            return response()->json($result, 400);
        }

        return response()->json($result, 200);
    }

    /**
     * Sync order status directly from Midtrans (useful for local dev and immediate feedback)
     */
    public function syncStatus(Order $order, MidtransService $midtransService)
    {
        if ($order->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $payment = $order->payment;
        if (!$payment) {
            return back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        // If already paid
        if ($order->status === 'paid' || $payment->status === 'paid') {
            return redirect()->route('customer.orders.show', $order)
                ->with('success', 'Pembayaran telah lunas!');
        }

        $statusData = $midtransService->checkStatus($order);
        if ($statusData) {
            $midtransService->handleNotification($statusData);
            $order->refresh();

            if ($order->status === 'paid') {
                return redirect()->route('customer.orders.show', $order)
                    ->with('success', 'Pembayaran berhasil dikonfirmasi oleh sistem!');
            }
        }

        return redirect()->route('customer.orders.show', $order)
            ->with('info', 'Status pembayaran sedang diproses oleh penyedia pembayaran.');
    }

    /**
     * Sync advertisement status directly from Midtrans
     */
    public function syncAdStatus(\App\Models\Advertisement $advertisement, MidtransService $midtransService)
    {
        $vendor = auth()->user()->vendorProfile;
        if (!$vendor || $advertisement->vendor_profile_id !== $vendor->id) {
            abort(403);
        }

        if ($advertisement->payment_status === 'paid') {
            return redirect()->route('vendor.advertisements.index')
                ->with('success', 'Pembayaran slot iklan telah lunas dan siap diverifikasi tim kurasi!');
        }

        $statusData = $midtransService->checkAdStatus($advertisement);
        if ($statusData) {
            $midtransService->handleNotification($statusData);
            $advertisement->refresh();

            if ($advertisement->payment_status === 'paid') {
                return redirect()->route('vendor.advertisements.index')
                    ->with('success', 'Pembayaran iklan berhasil dikonfirmasi oleh Midtrans!');
            }
        }

        return redirect()->route('vendor.advertisements.payment', $advertisement)
            ->with('info', 'Status pembayaran sedang diverifikasi oleh penyedia pembayaran.');
    }
}

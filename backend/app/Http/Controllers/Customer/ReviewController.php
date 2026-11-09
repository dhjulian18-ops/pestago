<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Show review form for a completed order item
     */
    public function create(OrderItem $item)
    {
        $order = $item->order;
        if ($order->user_id !== auth()->id()) abort(403);

        // Only allow review on completed items
        if ($item->status !== 'completed') {
            return back()->with('error', 'Anda hanya bisa memberikan ulasan untuk pesanan yang sudah selesai.');
        }

        // Check if already reviewed
        if ($item->review) {
            return back()->with('info', 'Anda sudah memberikan ulasan untuk layanan ini.');
        }

        $item->load(['service', 'vendorProfile']);
        return view('customer.reviews.create', compact('item'));
    }

    /**
     * Store a new review
     */
    public function store(Request $request, OrderItem $item)
    {
        $order = $item->order;
        if ($order->user_id !== auth()->id()) abort(403);

        if ($item->status !== 'completed') {
            return back()->with('error', 'Anda hanya bisa memberikan ulasan untuk pesanan yang sudah selesai.');
        }

        if ($item->review) {
            return back()->with('info', 'Anda sudah memberikan ulasan untuk layanan ini.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
            'images.*' => 'nullable|image|max:2048',
        ]);

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('reviews', 'public');
            }
        }

        $review = Review::create([
            'order_item_id' => $item->id,
            'user_id' => auth()->id(),
            'service_id' => $item->service_id,
            'vendor_profile_id' => $item->vendor_profile_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'images' => $images ?: null,
            'is_visible' => true,
        ]);

        // Update vendor average rating & count
        $vendor = $item->vendorProfile;
        if ($vendor) {
            $avg = Review::where('vendor_profile_id', $vendor->id)
                ->where('is_visible', true)
                ->avg('rating');
            $count = Review::where('vendor_profile_id', $vendor->id)
                ->where('is_visible', true)
                ->count();
            $vendor->update([
                'rating_avg' => round($avg ?: 0, 2),
                'rating_count' => $count,
            ]);
        }

        // Update service average rating & count
        $service = $item->service;
        if ($service) {
            $sAvg = Review::where('service_id', $service->id)
                ->where('is_visible', true)
                ->avg('rating');
            $sCount = Review::where('service_id', $service->id)
                ->where('is_visible', true)
                ->count();
            $service->update([
                'rating_avg' => round($sAvg ?: 0, 2),
                'rating_count' => $sCount,
            ]);
        }

        // Auto konfirmasi penyelesaian acara & rilis dana escrow jika belum dikonfirmasi
        if (empty($item->customer_confirmed_at)) {
            $workflow = app(\App\Services\OrderWorkflowService::class);
            $workflow->customerConfirmComplete($item, auth()->id());
        }

        // Notify vendor
        Notification::create([
            'user_id' => $vendor->user_id,
            'type' => 'new_review',
            'title' => 'Ulasan Baru',
            'message' => auth()->user()->name . " memberikan ulasan {$request->rating}★ untuk '{$item->service_name_snapshot}'.",
            'data' => ['review_id' => $review->id, 'service_id' => $item->service_id],
        ]);

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Terima kasih atas ulasan Anda!');
    }
}

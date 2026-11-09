<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Display customer's wishlisted services
     */
    public function index(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('info', 'Silakan masuk untuk melihat layanan favorit Anda.');
        }

        $user = auth()->user();
        $wishlists = Wishlist::where('user_id', $user->id)
            ->with(['service.primaryImage', 'service.vendorProfile', 'service.location', 'service.category'])
            ->latest()
            ->paginate(12);

        $totalWishlists = Wishlist::where('user_id', $user->id)->count();

        return view('customer.wishlist', compact('wishlists', 'totalWishlists'));
    }

    /**
     * AJAX Toggle favorite status of a service
     */
    public function toggle(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'unauthenticated' => true,
                'redirect' => route('login'),
                'message' => 'Silakan masuk untuk menyimpan layanan ke favorit.'
            ], 401);
        }

        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
        ]);

        $userId = auth()->id();
        $serviceId = $validated['service_id'];

        $existing = Wishlist::where('user_id', $userId)
            ->where('service_id', $serviceId)
            ->first();

        if ($existing) {
            $existing->delete();
            $isFavorite = false;
            $message = 'Layanan dihapus dari favorit.';
        } else {
            Wishlist::create([
                'user_id' => $userId,
                'service_id' => $serviceId,
            ]);
            $isFavorite = true;
            $message = 'Layanan berhasil ditambahkan ke favorit!';
        }

        $count = Wishlist::where('user_id', $userId)->count();

        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite,
            'count' => $count,
            'message' => $message,
        ]);
    }

    /**
     * Remove a service from wishlist
     */
    public function destroy(Service $service)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        Wishlist::where('user_id', auth()->id())
            ->where('service_id', $service->id)
            ->delete();

        if (request()->expectsJson()) {
            $count = Wishlist::where('user_id', auth()->id())->count();
            return response()->json([
                'success' => true,
                'count' => $count,
                'message' => 'Layanan berhasil dihapus dari favorit.'
            ]);
        }

        return redirect()->back()->with('success', 'Layanan berhasil dihapus dari daftar favorit Anda.');
    }
}


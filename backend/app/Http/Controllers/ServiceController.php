<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceLocation;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with(['vendorProfile', 'primaryImage', 'location', 'category'])
            ->where('status', 'published')
            ->where('is_active', true)
            ->whereHas('vendorProfile', fn($q) => $q->where('status', 'active'));

        // Filter kategori
        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        // Filter kota
        if ($request->filled('city')) {
            $query->whereHas('location', fn($q) => $q->where('city', 'like', '%' . $request->city . '%'));
        }

        // Filter harga
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        // Filter rating
        if ($request->filled('min_rating')) {
            $query->where('rating_avg', '>=', $request->min_rating);
        }

        // Filter vendor
        $activeVendor = null;
        if ($request->filled('vendor')) {
            $activeVendor = \App\Models\VendorProfile::with(['category', 'user'])->where('slug', $request->vendor)->first();
            $query->whereHas('vendorProfile', fn($q) => $q->where('slug', $request->vendor));
        }

        // Pencarian nama (search atau q dari navbar)
        $search = $request->input('search') ?: $request->input('q');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('vendorProfile', fn($vq) => $vq->where('business_name', 'like', "%{$search}%"));
            });
        }

        // Sorting
        $sort = $request->get('sort', 'rating');
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            'popular' => $query->orderByDesc('booking_count'),
            default => $query->orderByDesc('rating_avg'),
        };

        $services = $query->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        // Iklan Banner Kategori
        $categoryAds = collect();
        if ($request->filled('category')) {
            $categoryAds = \App\Models\Advertisement::with(['vendorProfile.user'])
                ->where('review_status', 'approved')
                ->where('payment_status', 'paid')
                ->where('placement', 'category_banner')
                ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()->startOfDay()))
                ->latest()
                ->get();
        }

        // Iklan Pencarian Bersponsor
        $sponsoredAds = collect();
        if ($search) {
            $sponsoredAds = \App\Models\Advertisement::with(['vendorProfile.user'])
                ->where('review_status', 'approved')
                ->where('payment_status', 'paid')
                ->where('placement', 'search_sponsored')
                ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()->startOfDay()))
                ->latest()
                ->get();
        }

        return view('services.index', compact('services', 'categories', 'sort', 'categoryAds', 'sponsoredAds', 'activeVendor'));
    }

    public function show(string $slug)
    {
        $service = Service::with([
            'vendorProfile.category',
            'vendorProfile.reviews' => fn($q) => $q->where('is_visible', true)->latest()->limit(5),
            'images',
            'location',
            'category',
            'reviews' => fn($q) => $q->with('user')->where('is_visible', true)->latest()->limit(10),
        ])
        ->where('slug', $slug)
        ->where('status', 'published')
        ->where('is_active', true)
        ->firstOrFail();

        // Layanan lain dari vendor yang sama
        $relatedServices = Service::with(['primaryImage'])
            ->where('vendor_profile_id', $service->vendor_profile_id)
            ->where('id', '!=', $service->id)
            ->where('status', 'published')
            ->where('is_active', true)
            ->limit(4)
            ->get();

        // Layanan serupa dari vendor lain
        $similarServices = Service::with(['vendorProfile', 'primaryImage', 'location'])
            ->where('category_id', $service->category_id)
            ->where('id', '!=', $service->id)
            ->where('status', 'published')
            ->where('is_active', true)
            ->orderByDesc('rating_avg')
            ->limit(6)
            ->get();

        // Ambil ketersediaan 6 bulan ke depan untuk kalender (dari tabel availabilities dan order aktif)
        $bookedDatesAvail = $service->availabilities()
            ->where('date', '>=', today())
            ->where('date', '<=', today()->addMonths(6))
            ->whereIn('status', ['booked', 'blocked', 'pending'])
            ->pluck('date')
            ->map(fn($d) => $d instanceof \Carbon\Carbon ? $d->format('Y-m-d') : date('Y-m-d', strtotime($d)))
            ->toArray();

        $bookedDatesOrders = \App\Models\OrderItem::where('service_id', $service->id)
            ->where('booking_date', '>=', today())
            ->where('booking_date', '<=', today()->addMonths(6))
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->pluck('booking_date')
            ->map(fn($d) => $d instanceof \Carbon\Carbon ? $d->format('Y-m-d') : date('Y-m-d', strtotime($d)))
            ->toArray();

        $bookedDates = array_values(array_unique(array_merge($bookedDatesAvail, $bookedDatesOrders)));

        // Ambil acara aktif milik customer jika sedang login
        $userEvents = collect();
        if (auth()->check() && auth()->user()->role === 'customer') {
            $userEvents = \App\Models\Event::where('user_id', auth()->id())
                ->where('status', 'planning')
                ->orderBy('event_date')
                ->get();
        }

        return view('services.show', compact('service', 'relatedServices', 'similarServices', 'bookedDates', 'userEvents'));
    }
}

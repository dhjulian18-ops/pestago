<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\Promo;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\VendorProfile;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Papan Iklan Utama (Atas) — Khusus Admin PestaGo
        $homeCarouselAds = Advertisement::with(['vendorProfile.user'])
            ->where('review_status', 'approved')
            ->where('payment_status', 'paid')
            ->where('placement', 'home_carousel')
            ->where('is_admin', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()->startOfDay());
            })
            ->latest()
            ->get();

        // Papan Iklan Khusus Vendor (Billboard Vendor Pilihan)
        $vendorBillboardAds = Advertisement::with(['vendorProfile.user'])
            ->where('review_status', 'approved')
            ->where('payment_status', 'paid')
            ->where('placement', 'vendor_billboard')
            ->where('is_admin', false)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()->startOfDay());
            })
            ->latest()
            ->limit(10)
            ->get();

        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $featuredVendors = VendorProfile::with(['services.primaryImage', 'category'])
            ->where('status', 'active')
            ->whereHas('services', fn($q) => $q->where('status', 'published')->where('is_active', true))
            ->withCount(['reviews' => fn($q) => $q->where('is_visible', true)])
            ->orderByDesc('rating_avg')
            ->limit(8)
            ->get();

        $promos = Promo::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $testimonials = Testimonial::where('is_active', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $featuredServices = Service::with(['category', 'vendorProfile', 'primaryImage', 'location'])
            ->where('status', 'published')
            ->where('is_active', true)
            ->orderByDesc('rating_avg')
            ->limit(10)
            ->get();

        $popularServices = Service::with(['category', 'vendorProfile', 'primaryImage', 'location'])
            ->where('status', 'published')
            ->where('is_active', true)
            ->orderByDesc('booking_count')
            ->orderByDesc('rating_avg')
            ->limit(10)
            ->get();

        $nearestServices = Service::with(['category', 'vendorProfile', 'primaryImage', 'location'])
            ->where('status', 'published')
            ->where('is_active', true)
            ->limit(10)
            ->get();

        return view('home.index', compact(
            'homeCarouselAds',
            'vendorBillboardAds',
            'categories',
            'featuredVendors',
            'promos',
            'testimonials',
            'featuredServices',
            'popularServices',
            'nearestServices'
        ));
    }

    public function about()
    {
        return view('home.about');
    }

    public function contact()
    {
        return view('home.contact');
    }

    public function howItWorks()
    {
        return view('home.how-it-works');
    }
}

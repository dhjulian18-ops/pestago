<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Event;
use App\Models\Order;
use App\Models\Notification;
use Illuminate\Http\Request;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Ringkasan acara
        $upcomingEvents = Event::where('user_id', $user->id)
            ->where('event_date', '>=', today())
            ->orderBy('event_date')
            ->limit(3)
            ->get();

        // Ringkasan pesanan terbaru
        $recentOrders = Order::with(['items.service', 'items.vendorProfile'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Notifikasi belum dibaca
        $unreadNotifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $cartCount = Cart::where('user_id', $user->id)->count();

        $stats = [
            'total_events' => Event::where('user_id', $user->id)->count(),
            'active_orders' => Order::where('user_id', $user->id)
                ->whereIn('status', ['paid', 'partially_confirmed', 'confirmed', 'in_progress'])
                ->count(),
            'completed_orders' => Order::where('user_id', $user->id)->where('status', 'completed')->count(),
        ];

        return view('customer.dashboard', compact(
            'upcomingEvents', 'recentOrders', 'unreadNotifications', 'cartCount', 'stats'
        ));
    }
}

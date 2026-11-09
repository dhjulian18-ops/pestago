<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::where('user_id', auth()->id())
            ->orderByDesc('event_date')
            ->paginate(10);

        return view('customer.events.index', compact('events'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $eventTypes = [
            'pernikahan' => 'Pernikahan',
            'ulang_tahun' => 'Ulang Tahun',
            'wisuda' => 'Wisuda',
            'seminar' => 'Seminar',
            'khitanan' => 'Khitanan',
            'corporate' => 'Acara Perusahaan',
            'lainnya' => 'Acara Lainnya',
        ];
        return view('customer.events.create', compact('categories', 'eventTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'event_type' => 'required|string|max:50',
            'event_date' => 'required|date|after_or_equal:today',
            'event_time' => 'nullable|date_format:H:i',
            'location_address' => 'nullable|string|max:500',
            'location_city' => 'nullable|string|max:100',
            'location_latitude' => 'nullable|numeric|between:-90,90',
            'location_longitude' => 'nullable|numeric|between:-180,180',
            'guest_count' => 'nullable|integer|min:1|max:100000',
            'budget' => 'nullable|numeric|min:0',
            'requirements' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        $event = Event::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'event_type' => $request->event_type,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'location_address' => $request->location_address,
            'location_city' => $request->location_city,
            'location_latitude' => $request->location_latitude,
            'location_longitude' => $request->location_longitude,
            'guest_count' => $request->guest_count,
            'budget' => $request->budget,
            'requirements' => $request->requirements,
            'notes' => $request->notes,
        ]);

        return redirect()->route('customer.events.show', $event)
            ->with('success', 'Acara berhasil dibuat! Sekarang cari layanan yang Anda butuhkan.');
    }

    public function show(Event $event)
    {
        if ($event->user_id !== auth()->id()) abort(403);

        $event->load(['orders.items.vendorProfile', 'orders.items.service', 'carts.service']);

        // Rekomendasi vendor berdasarkan kategori kebutuhan
        $recommendations = collect();
        if ($event->requirements && count($event->requirements) > 0) {
            // STRICT: Hanya ambil layanan dari kategori yang dipilih oleh customer!
            $query = \App\Models\Service::with(['vendorProfile', 'primaryImage', 'location', 'category'])
                ->where('status', 'published')
                ->where('is_active', true)
                ->whereHas('vendorProfile', fn($q) => $q->where('status', 'active'))
                ->whereIn('category_id', $event->requirements);

            // Coba cari yang dalam rentang budget terlebih dahulu
            $budgetFiltered = (clone $query)
                ->when($event->budget && $event->budget > 0, fn($q) => $q->where('price', '<=', $event->budget))
                ->orderByDesc('rating_avg')
                ->limit(12)
                ->get();

            if ($budgetFiltered->isNotEmpty()) {
                $recommendations = $budgetFiltered;
            } else {
                // Jika budget lebih rendah dari harga layanan yang ada,
                // TETAP tampilkan layanan dari kategori yang dipilih (diurutkan dari harga terendah),
                // JANGAN PERNAH mengambil kategori lain yang tidak dipilih!
                $recommendations = $query
                    ->orderBy('price', 'asc')
                    ->limit(12)
                    ->get();
            }
        } else {
            // Hanya jika customer TIDAK memilih kategori kebutuhan apa pun
            $recommendations = \App\Models\Service::with(['vendorProfile', 'primaryImage', 'location', 'category'])
                ->where('status', 'published')
                ->where('is_active', true)
                ->whereHas('vendorProfile', fn($q) => $q->where('status', 'active'))
                ->orderByDesc('is_featured')
                ->orderByDesc('rating_avg')
                ->limit(8)
                ->get();
        }

        return view('customer.events.show', compact('event', 'recommendations'));
    }

    public function edit(Event $event)
    {
        if ($event->user_id !== auth()->id()) abort(403);

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $eventTypes = [
            'pernikahan' => 'Pernikahan',
            'ulang_tahun' => 'Ulang Tahun',
            'wisuda' => 'Wisuda',
            'seminar' => 'Seminar',
            'khitanan' => 'Khitanan',
            'corporate' => 'Acara Perusahaan',
            'lainnya' => 'Acara Lainnya',
        ];
        return view('customer.events.edit', compact('event', 'categories', 'eventTypes'));
    }

    public function update(Request $request, Event $event)
    {
        if ($event->user_id !== auth()->id()) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'event_type' => 'required|string|max:50',
            'event_date' => 'required|date|after_or_equal:today',
            'location_address' => 'nullable|string|max:500',
            'location_city' => 'nullable|string|max:100',
            'guest_count' => 'nullable|integer|min:1',
            'budget' => 'nullable|numeric|min:0',
            'requirements' => 'nullable|array',
        ]);

        $event->update($request->only([
            'name', 'event_type', 'event_date', 'event_time',
            'location_address', 'location_city',
            'location_latitude', 'location_longitude',
            'guest_count', 'budget', 'requirements', 'notes',
        ]));

        return redirect()->route('customer.events.show', $event)
            ->with('success', 'Acara berhasil diperbarui.');
    }
}

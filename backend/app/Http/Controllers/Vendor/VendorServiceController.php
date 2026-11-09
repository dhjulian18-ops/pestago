<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceImage;
use App\Models\ServiceLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class VendorServiceController extends Controller
{
    protected function getVendor()
    {
        return auth()->user()->vendorProfile;
    }

    public function index()
    {
        $vendor = $this->getVendor();
        $services = Service::with(['primaryImage', 'category', 'location'])
            ->where('vendor_profile_id', $vendor->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('vendor.services.index', compact('services', 'vendor'));
    }

    public function create()
    {
        $vendor = $this->getVendor();
        if ($vendor->status !== 'active') {
            return redirect()->route('vendor.dashboard')
                ->with('warning', 'Akun vendor Anda harus aktif sebelum menambah layanan.');
        }

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('vendor.services.create', compact('categories', 'vendor'));
    }

    public function store(Request $request)
    {
        $vendor = $this->getVendor();
        if ($vendor->status !== 'active') abort(403, 'Akun vendor tidak aktif.');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:5000',
            'price' => 'required|numeric|min:0',
            'price_unit' => 'required|in:per_acara,per_orang,per_hari,per_paket,penawaran',
            'capacity_min' => 'nullable|integer|min:1',
            'capacity_max' => 'nullable|integer|min:1',
            'facilities' => 'nullable|string',
            'cancellation_policy' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:8',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:3072',
            // Lokasi
            'location_type' => 'required|in:fixed,mobile,area',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Buat slug unik
        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;
        while (Service::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $service = Service::create([
            'vendor_profile_id' => $vendor->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'price' => $request->price,
            'price_unit' => $request->price_unit,
            'capacity_min' => $request->capacity_min,
            'capacity_max' => $request->capacity_max,
            'facilities' => $request->facilities ? array_filter(array_map('trim', explode(',', $request->facilities))) : null,
            'cancellation_policy' => $request->cancellation_policy,
            'status' => 'published',
            'is_active' => true,
        ]);

        // Upload foto
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('services', 'public');
                ServiceImage::create([
                    'service_id' => $service->id,
                    'image_path' => $path,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        }

        // Simpan lokasi
        ServiceLocation::create([
            'service_id' => $service->id,
            'address' => $request->address,
            'city' => $request->city,
            'district' => $request->district,
            'province' => $request->province,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_type' => $request->location_type,
        ]);

        return redirect()->route('vendor.services.index')
            ->with('success', 'Layanan berhasil ditambahkan!');
    }

    public function edit(Service $service)
    {
        $vendor = $this->getVendor();
        if ($service->vendor_profile_id !== $vendor->id) abort(403);

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $service->load(['images', 'location']);

        return view('vendor.services.edit', compact('service', 'categories', 'vendor'));
    }

    public function update(Request $request, Service $service)
    {
        $vendor = $this->getVendor();
        if ($service->vendor_profile_id !== $vendor->id) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:5000',
            'price' => 'required|numeric|min:0',
            'price_unit' => 'required|in:per_acara,per_orang,per_hari,per_paket,penawaran',
            'capacity_min' => 'nullable|integer|min:1',
            'capacity_max' => 'nullable|integer|min:1',
            'new_images' => 'nullable|array|max:8',
            'new_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:3072',
            'location_type' => 'required|in:fixed,mobile,area',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $service->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'price_unit' => $request->price_unit,
            'capacity_min' => $request->capacity_min,
            'capacity_max' => $request->capacity_max,
            'facilities' => $request->facilities ? array_filter(array_map('trim', explode(',', $request->facilities))) : null,
            'cancellation_policy' => $request->cancellation_policy,
        ]);

        // Upload foto baru
        if ($request->hasFile('new_images')) {
            $existingCount = ServiceImage::where('service_id', $service->id)->count();
            foreach ($request->file('new_images') as $index => $image) {
                $path = $image->store('services', 'public');
                ServiceImage::create([
                    'service_id' => $service->id,
                    'image_path' => $path,
                    'is_primary' => $existingCount === 0 && $index === 0,
                    'sort_order' => $existingCount + $index,
                ]);
            }
        }

        // Update lokasi
        ServiceLocation::updateOrCreate(
            ['service_id' => $service->id],
            [
                'address' => $request->address,
                'city' => $request->city,
                'district' => $request->district,
                'province' => $request->province,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'location_type' => $request->location_type,
            ]
        );

        return redirect()->route('vendor.services.index')
            ->with('success', 'Layanan berhasil diperbarui!');
    }

    public function destroy(Service $service)
    {
        $vendor = $this->getVendor();
        if ($service->vendor_profile_id !== $vendor->id) abort(403);

        // Soft delete - ubah status jadi draft
        $service->update(['status' => 'draft', 'is_active' => false]);

        return back()->with('success', 'Layanan berhasil dinonaktifkan.');
    }

    public function deleteImage(ServiceImage $image)
    {
        $vendor = $this->getVendor();
        if ($image->service->vendor_profile_id !== $vendor->id) abort(403);

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('success', 'Foto berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvertisementPlan;
use Illuminate\Http\Request;

class AdvertisementPlanController extends Controller
{
    /**
     * List all advertisement plans/options
     */
    public function index()
    {
        $plans = AdvertisementPlan::whereIn('placement_key', ['home_carousel', 'vendor_billboard'])
            ->orderBy('sort_order')
            ->get();
        return view('admin.advertisements.plans.index', compact('plans'));
    }

    /**
     * Form to create a new ad plan
     */
    public function create()
    {
        return view('admin.advertisements.plans.create');
    }

    /**
     * Store a new ad plan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'placement_key' => 'required|string|max:50|unique:advertisement_plans,placement_key|regex:/^[a-z0-9_]+$/',
            'name' => 'required|string|max:100',
            'badge' => 'nullable|string|max:30',
            'badge_bg_color' => 'nullable|string|max:20',
            'badge_text_color' => 'nullable|string|max:20',
            'description' => 'required|string|max:500',
            'price_basic' => 'required|numeric|min:0',
            'days_basic' => 'required|integer|min:1',
            'price_pro' => 'required|numeric|min:0',
            'days_pro' => 'required|integer|min:1',
            'price_business' => 'required|numeric|min:0',
            'days_business' => 'required|integer|min:1',
            'price_per_day' => 'nullable|numeric|min:0',
            'min_days_custom' => 'nullable|integer|min:1',
            'max_days_custom' => 'nullable|integer|min:1',
            'canvas_title' => 'nullable|string|max:150',
            'canvas_guideline' => 'nullable|string|max:1000',
            'canvas_ratio' => 'nullable|string|max:20',
            'canvas_orientation' => 'nullable|string|max:30',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? (AdvertisementPlan::max('sort_order') + 1);

        AdvertisementPlan::create($validated);

        return redirect()->route('admin.ad-plans.index')
            ->with('success', "Opsi iklan '{$validated['name']}' berhasil ditambahkan.");
    }

    /**
     * Edit form for an ad plan
     */
    public function edit(AdvertisementPlan $plan)
    {
        return view('admin.advertisements.plans.edit', compact('plan'));
    }

    /**
     * Update an ad plan
     */
    public function update(Request $request, AdvertisementPlan $plan)
    {
        $validated = $request->validate([
            'placement_key' => 'required|string|max:50|unique:advertisement_plans,placement_key,' . $plan->id . '|regex:/^[a-z0-9_]+$/',
            'name' => 'required|string|max:100',
            'badge' => 'nullable|string|max:30',
            'badge_bg_color' => 'nullable|string|max:20',
            'badge_text_color' => 'nullable|string|max:20',
            'description' => 'required|string|max:500',
            'price_basic' => 'required|numeric|min:0',
            'days_basic' => 'required|integer|min:1',
            'price_pro' => 'required|numeric|min:0',
            'days_pro' => 'required|integer|min:1',
            'price_business' => 'required|numeric|min:0',
            'days_business' => 'required|integer|min:1',
            'price_per_day' => 'nullable|numeric|min:0',
            'min_days_custom' => 'nullable|integer|min:1',
            'max_days_custom' => 'nullable|integer|min:1',
            'canvas_title' => 'nullable|string|max:150',
            'canvas_guideline' => 'nullable|string|max:1000',
            'canvas_ratio' => 'nullable|string|max:20',
            'canvas_orientation' => 'nullable|string|max:30',
            'sort_order' => 'nullable|integer',
        ]);

        if ($plan->placement_key === 'home_carousel') {
            $validated['is_active'] = true;
        } else {
            $validated['is_active'] = $request->has('is_active');
        }

        $plan->update($validated);

        return redirect()->route('admin.ad-plans.index')
            ->with('success', "Opsi iklan '{$plan->name}' berhasil diperbarui.");
    }

    /**
     * Toggle active status
     */
    public function toggle(AdvertisementPlan $plan)
    {
        if ($plan->placement_key === 'home_carousel') {
            return back()->with('info', "Papan Utama (Besar) khusus Admin PestaGo selalu aktif dan tidak dapat dinonaktifkan.");
        }

        $plan->update(['is_active' => !$plan->is_active]);

        $statusText = $plan->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Opsi iklan '{$plan->name}' berhasil {$statusText}.");
    }

    /**
     * Delete an ad plan
     */
    public function destroy(AdvertisementPlan $plan)
    {
        if ($plan->placement_key === 'home_carousel') {
            return back()->with('error', "Papan Utama (Besar) adalah papan iklan utama platform dan tidak dapat dihapus.");
        }

        $usageCount = \App\Models\Advertisement::where('placement', $plan->placement_key)->count();
        if ($usageCount > 0) {
            return back()->with('error', "Opsi iklan '{$plan->name}' tidak dapat dihapus karena sudah memiliki {$usageCount} riwayat iklan terdaftar. Anda dapat menonaktifkannya saja.");
        }

        $name = $plan->name;
        $plan->delete();

        return redirect()->route('admin.ad-plans.index')
            ->with('success', "Opsi iklan '{$name}' telah dihapus.");
    }
}

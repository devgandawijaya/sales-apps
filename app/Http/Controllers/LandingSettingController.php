<?php

namespace App\Http\Controllers;

use App\Models\LandingSetting;
use App\Models\LandingSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandingSettingController extends Controller
{
    /**
     * Display a listing of landing page slides & content settings.
     */
    public function index()
    {
        $slides = LandingSlide::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        $settings = LandingSetting::getAllSettings();

        return view('landing_settings.index', compact('slides', 'settings'));
    }

    /**
     * Store a newly created slide.
     */
    public function storeSlide(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'subtitle' => 'nullable|string|max:500',
            'badge_text' => 'nullable|string|max:50',
            'image_url' => 'nullable|string|max:1000',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'cta_text' => 'nullable|string|max:50',
            'cta_link' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $imageUrl = $validated['image_url'] ?? null;

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('slides', 'public');
            $imageUrl = '/storage/' . $path;
        }

        if (empty($imageUrl)) {
            $imageUrl = 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=1600&q=80';
        }

        $highestOrder = LandingSlide::max('sort_order') ?? 0;

        LandingSlide::create([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'badge_text' => $validated['badge_text'] ?? null,
            'image_url' => $imageUrl,
            'cta_text' => $validated['cta_text'] ?? null,
            'cta_link' => $validated['cta_link'] ?? '#fitur',
            'sort_order' => isset($validated['sort_order']) ? (int) $validated['sort_order'] : ($highestOrder + 1),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('landing-settings.index')->with('success', 'Slide gambar berhasil ditambahkan ke halaman landing.');
    }

    /**
     * Update an existing slide.
     */
    public function updateSlide(Request $request, int $id)
    {
        $slide = LandingSlide::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'subtitle' => 'nullable|string|max:500',
            'badge_text' => 'nullable|string|max:50',
            'image_url' => 'nullable|string|max:1000',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'cta_text' => 'nullable|string|max:50',
            'cta_link' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $imageUrl = $validated['image_url'] ?? $slide->image_url;

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('slides', 'public');
            $imageUrl = '/storage/' . $path;
        }

        $slide->update([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'badge_text' => $validated['badge_text'] ?? null,
            'image_url' => $imageUrl,
            'cta_text' => $validated['cta_text'] ?? null,
            'cta_link' => $validated['cta_link'] ?? '#fitur',
            'sort_order' => isset($validated['sort_order']) ? (int) $validated['sort_order'] : $slide->sort_order,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('landing-settings.index')->with('success', "Slide '{$slide->title}' berhasil diperbarui.");
    }

    /**
     * Toggle slide active status.
     */
    public function toggleSlide(int $id)
    {
        $slide = LandingSlide::findOrFail($id);
        $slide->update(['is_active' => !$slide->is_active]);

        $statusText = $slide->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('landing-settings.index')->with('success', "Slide '{$slide->title}' berhasil {$statusText}.");
    }

    /**
     * Remove the specified slide.
     */
    public function destroySlide(int $id)
    {
        $slide = LandingSlide::findOrFail($id);
        $title = $slide->title;
        $slide->delete();

        return redirect()->route('landing-settings.index')->with('success', "Slide '{$title}' berhasil dihapus.");
    }

    /**
     * Update landing page hero content and stats.
     */
    public function updateContent(Request $request)
    {
        $validated = $request->validate([
            'hero_badge' => 'nullable|string|max:100',
            'hero_title' => 'required|string|max:250',
            'hero_description' => 'required|string|max:1500',
            'hero_cta_primary' => 'nullable|string|max:50',
            'hero_cta_secondary' => 'nullable|string|max:50',
            'stats_label_1' => 'nullable|string|max:50',
            'stats_value_1' => 'nullable|string|max:50',
            'stats_desc_1' => 'nullable|string|max:100',
            'stats_label_2' => 'nullable|string|max:50',
            'stats_value_2' => 'nullable|string|max:50',
            'stats_desc_2' => 'nullable|string|max:100',
            'stats_label_3' => 'nullable|string|max:50',
            'stats_value_3' => 'nullable|string|max:50',
            'stats_desc_3' => 'nullable|string|max:100',
        ]);

        foreach ($validated as $key => $value) {
            LandingSetting::set($key, $value);
        }

        return redirect()->route('landing-settings.index')->with('success', 'Konten teks halaman beranda berhasil disimpan.');
    }
}


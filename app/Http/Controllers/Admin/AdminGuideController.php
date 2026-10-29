<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RecruitmentGuideType;
use App\Http\Controllers\Controller;
use App\Models\RecruitmentGuide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminGuideController extends Controller
{
    /**
     * Menampilkan daftar seluruh konten panduan rekrutmen.
     */
    public function index(): View
    {
        $guides = RecruitmentGuide::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.guides.index', [
            'guides' => $guides,
        ]);
    }

    /**
     * Menampilkan formulir editor panduan rekrutmen.
     */
    public function edit(RecruitmentGuide $guide): View
    {
        return view('admin.guides.edit', [
            'guide' => $guide,
            'types' => RecruitmentGuideType::cases(),
        ]);
    }

    /**
     * Memperbarui data dan konten item panduan rekrutmen.
     */
    public function update(Request $request, RecruitmentGuide $guide): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:flow,requirements'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'items' => ['nullable', 'array'],
            'content' => ['nullable'],
        ]);

        $content = [];

        if ($request->has('items') && is_array($request->input('items'))) {
            $rawItems = $request->input('items');
            $index = 1;
            foreach ($rawItems as $item) {
                if (is_array($item)) {
                    $itemTitle = trim((string) ($item['title'] ?? ''));
                    $itemDesc = trim((string) ($item['description'] ?? ''));

                    if ($itemTitle !== '' || $itemDesc !== '') {
                        $content[] = [
                            'step' => isset($item['step']) && is_numeric($item['step']) ? (int) $item['step'] : $index,
                            'title' => $itemTitle,
                            'description' => $itemDesc,
                        ];
                        $index++;
                    }
                }
            }
        } elseif ($request->has('content')) {
            $rawContent = $request->input('content');
            if (is_array($rawContent)) {
                $content = $rawContent;
            } elseif (is_string($rawContent)) {
                $decoded = json_decode($rawContent, true);
                $content = is_array($decoded) ? $decoded : [['step' => 1, 'title' => $guide->title, 'description' => $rawContent]];
            }
        } else {
            $content = $guide->content ?? [];
        }

        $isActive = $request->boolean('is_active', true);
        if (! $request->has('is_active') && $request->isMethod('put') && ! $request->isJson()) {
            $isActive = $request->has('is_active_checkbox') ? $request->boolean('is_active_checkbox') : $guide->is_active;
        }

        $guide->update([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'subtitle' => $validated['subtitle'] ?? null,
            'sort_order' => isset($validated['sort_order']) ? (int) $validated['sort_order'] : $guide->sort_order,
            'is_active' => $isActive,
            'content' => $content,
        ]);

        return redirect()
            ->route('admin.guides.index')
            ->with('success', "Konten panduan '{$guide->title}' berhasil diperbarui.");
    }

    /**
     * Menyimpan panduan rekrutmen baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:flow,requirements'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'items' => ['nullable', 'array'],
            'content' => ['nullable'],
        ]);

        $content = [];

        if ($request->has('items') && is_array($request->input('items'))) {
            $rawItems = $request->input('items');
            $index = 1;
            foreach ($rawItems as $item) {
                if (is_array($item)) {
                    $itemTitle = trim((string) ($item['title'] ?? ''));
                    $itemDesc = trim((string) ($item['description'] ?? ''));

                    if ($itemTitle !== '' || $itemDesc !== '') {
                        $content[] = [
                            'step' => isset($item['step']) && is_numeric($item['step']) ? (int) $item['step'] : $index,
                            'title' => $itemTitle,
                            'description' => $itemDesc,
                        ];
                        $index++;
                    }
                }
            }
        } elseif ($request->has('content')) {
            $rawContent = $request->input('content');
            if (is_array($rawContent)) {
                $content = $rawContent;
            } elseif (is_string($rawContent)) {
                $decoded = json_decode($rawContent, true);
                $content = is_array($decoded) ? $decoded : [['step' => 1, 'title' => $validated['title'], 'description' => $rawContent]];
            }
        }

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $counter = 1;
        while (RecruitmentGuide::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $guide = RecruitmentGuide::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'type' => $validated['type'],
            'subtitle' => $validated['subtitle'] ?? null,
            'sort_order' => isset($validated['sort_order']) ? (int) $validated['sort_order'] : 1,
            'is_active' => $request->boolean('is_active', true),
            'content' => $content,
        ]);

        return redirect()
            ->route('admin.guides.index')
            ->with('success', "Panduan '{$guide->title}' berhasil ditambahkan.");
    }

    /**
     * Mengubah status aktif panduan rekrutmen.
     */
    public function toggleActive(RecruitmentGuide $guide): RedirectResponse
    {
        $guide->update([
            'is_active' => ! $guide->is_active,
        ]);

        $statusLabel = $guide->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->back()
            ->with('success', "Status panduan '{$guide->title}' berhasil {$statusLabel}.");
    }
}

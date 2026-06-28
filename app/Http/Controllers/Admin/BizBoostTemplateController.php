<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostTemplateModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BizBoostTemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = BizBoostTemplateModel::query()
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
            )
            ->when($request->category, fn($q, $cat) => $q->where('category', $cat))
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $categories = BizBoostTemplateModel::distinct()->pluck('category');

        return Inertia::render('Admin/BizBoost/Templates/Index', [
            'templates' => $templates,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function create()
    {
        $categories = BizBoostTemplateModel::distinct()->pluck('category');
        $industries = config('modules.bizboost.industry_kits', []);

        return Inertia::render('Admin/BizBoost/Templates/Create', [
            'categories' => $categories,
            'industries' => array_keys($industries),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:100',
            'industry' => 'nullable|string|max:100',
            'template_data' => 'required|array',
            'template_data.caption' => 'nullable|string',
            'template_data.hashtags' => 'nullable|array',
            'template_data.cta' => 'nullable|string',
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
            'preview' => 'nullable|image|max:2048',
        ]);

        $template = new BizBoostTemplateModel();
        $template->name = $validated['name'];
        $template->slug = \Illuminate\Support\Str::slug($validated['name']) . '-' . uniqid();
        $template->description = $validated['description'] ?? null;
        $template->category = $validated['category'];
        $template->industry = $validated['industry'] ?? null;
        $template->template_data = $validated['template_data'];
        $template->is_premium = $validated['is_premium'] ?? false;
        $template->is_active = $validated['is_active'] ?? true;
        $template->sort_order = $validated['sort_order'] ?? 0;

        if ($request->hasFile('preview')) {
            $path = $request->file('preview')->store('bizboost/templates', 'public');
            $template->preview_path = $path;
        }

        $template->save();

        return redirect()->route('admin.bizboost.templates.index')
            ->with('success', 'Template created successfully.');
    }

    public function edit(int $id)
    {
        $template = BizBoostTemplateModel::findOrFail($id);
        $categories = BizBoostTemplateModel::distinct()->pluck('category');
        $industries = config('modules.bizboost.industry_kits', []);

        return Inertia::render('Admin/BizBoost/Templates/Edit', [
            'template' => $template,
            'categories' => $categories,
            'industries' => array_keys($industries),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $template = BizBoostTemplateModel::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:100',
            'industry' => 'nullable|string|max:100',
            'template_data' => 'required|array',
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
            'preview' => 'nullable|image|max:2048',
        ]);

        $template->name = $validated['name'];
        $template->description = $validated['description'] ?? null;
        $template->category = $validated['category'];
        $template->industry = $validated['industry'] ?? null;
        $template->template_data = $validated['template_data'];
        $template->is_premium = $validated['is_premium'] ?? false;
        $template->is_active = $validated['is_active'] ?? true;
        $template->sort_order = $validated['sort_order'] ?? 0;

        if ($request->hasFile('preview')) {
            // Delete old preview
            if ($template->preview_path) {
                Storage::disk('public')->delete($template->preview_path);
            }
            $path = $request->file('preview')->store('bizboost/templates', 'public');
            $template->preview_path = $path;
        }

        $template->save();

        return redirect()->route('admin.bizboost.templates.index')
            ->with('success', 'Template updated successfully.');
    }

    public function destroy(int $id)
    {
        $template = BizBoostTemplateModel::findOrFail($id);

        if ($template->preview_path) {
            Storage::disk('public')->delete($template->preview_path);
        }

        $template->delete();

        return back()->with('success', 'Template deleted successfully.');
    }

    public function toggleActive(int $id)
    {
        $template = BizBoostTemplateModel::findOrFail($id);
        $template->is_active = !$template->is_active;
        $template->save();

        return back()->with('success', 'Template status updated.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MessageTemplateController extends Controller
{
    public function index()
    {
        $templates = MessageTemplate::with('creator:id,name')
            ->latest()
            ->get();

        return Inertia::render('Admin/Messages/Templates/Index', [
            'templates' => $templates,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Messages/Templates/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'category' => 'required|string|in:general,announcement,reminder,welcome,update',
        ]);

        MessageTemplate::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.messages.templates.index')
            ->with('success', 'Template created successfully');
    }

    public function edit(MessageTemplate $template)
    {
        return Inertia::render('Admin/Messages/Templates/Edit', [
            'template' => $template,
        ]);
    }

    public function update(Request $request, MessageTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'category' => 'required|string|in:general,announcement,reminder,welcome,update',
            'is_active' => 'boolean',
        ]);

        $template->update($validated);

        return redirect()->route('admin.messages.templates.index')
            ->with('success', 'Template updated successfully');
    }

    public function destroy(MessageTemplate $template)
    {
        $template->delete();

        return back()->with('success', 'Template deleted successfully');
    }

    public function show(MessageTemplate $template)
    {
        return response()->json([
            'template' => $template,
        ]);
    }
}

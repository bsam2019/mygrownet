<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopModel;
use App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopRegistrationModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkshopController extends Controller
{
    public function index()
    {
        $workshops = WorkshopModel::withCount('registrations')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($workshop) {
                return [
                    'id' => $workshop->id,
                    'title' => $workshop->title,
                    'slug' => $workshop->slug,
                    'category' => $workshop->category,
                    'delivery_format' => $workshop->delivery_format,
                    'price' => number_format($workshop->price, 2),
                    'price_raw' => $workshop->price,
                    'status' => $workshop->status,
                    'start_date' => $workshop->start_date->format('M j, Y g:i A'),
                    'end_date' => $workshop->end_date->format('M j, Y g:i A'),
                    'max_participants' => $workshop->max_participants,
                    'registrations_count' => $workshop->registrations_count,
                    'lp_reward' => $workshop->lp_reward,
                    'bp_reward' => $workshop->bp_reward,
                ];
            });

        return Inertia::render('Admin/Workshops/Index', [
            'workshops' => $workshops
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Workshops/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:financial_literacy,business_skills,leadership,marketing,technology,personal_development',
            'delivery_format' => 'required|in:online,physical,hybrid',
            'price' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url',
            'max_participants' => 'nullable|integer|min:1',
            'requirements' => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
            'instructor_name' => 'nullable|string|max:255',
            'instructor_bio' => 'nullable|string',
            'lp_reward' => 'required|integer|min:0',
            'bp_reward' => 'required|integer|min:0',
            'status' => 'required|in:draft,published',
        ]);

        $validated['slug'] = \Str::slug($validated['title']);
        $validated['created_by'] = auth()->id();
        
        WorkshopModel::create($validated);

        return redirect()->route('admin.workshops.index')
            ->with('success', 'Workshop created successfully.');
    }

    public function edit(int $id)
    {
        $workshop = WorkshopModel::findOrFail($id);

        return Inertia::render('Admin/Workshops/Edit', [
            'workshop' => [
                'id' => $workshop->id,
                'title' => $workshop->title,
                'description' => $workshop->description,
                'category' => $workshop->category,
                'delivery_format' => $workshop->delivery_format,
                'price' => $workshop->price,
                'start_date' => $workshop->start_date->format('Y-m-d\TH:i'),
                'end_date' => $workshop->end_date->format('Y-m-d\TH:i'),
                'location' => $workshop->location,
                'meeting_link' => $workshop->meeting_link,
                'max_participants' => $workshop->max_participants,
                'requirements' => $workshop->requirements,
                'learning_outcomes' => $workshop->learning_outcomes,
                'instructor_name' => $workshop->instructor_name,
                'instructor_bio' => $workshop->instructor_bio,
                'lp_reward' => $workshop->lp_reward,
                'bp_reward' => $workshop->bp_reward,
                'status' => $workshop->status,
            ]
        ]);
    }

    public function update(Request $request, int $id)
    {
        $workshop = WorkshopModel::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:financial_literacy,business_skills,leadership,marketing,technology,personal_development',
            'delivery_format' => 'required|in:online,physical,hybrid',
            'price' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url',
            'max_participants' => 'nullable|integer|min:1',
            'requirements' => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
            'instructor_name' => 'nullable|string|max:255',
            'instructor_bio' => 'nullable|string',
            'lp_reward' => 'required|integer|min:0',
            'bp_reward' => 'required|integer|min:0',
            'status' => 'required|in:draft,published,cancelled,completed',
        ]);

        $validated['slug'] = \Str::slug($validated['title']);
        
        $workshop->update($validated);

        return redirect()->route('admin.workshops.index')
            ->with('success', 'Workshop updated successfully.');
    }

    public function registrations()
    {
        $registrations = WorkshopRegistrationModel::with(['workshop', 'user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($registration) {
                return [
                    'id' => $registration->id,
                    'workshop_title' => $registration->workshop->title,
                    'user_name' => $registration->user->name,
                    'user_email' => $registration->user->email,
                    'status' => $registration->status,
                    'registered_at' => $registration->created_at->format('M j, Y g:i A'),
                    'notes' => $registration->notes,
                ];
            });

        return Inertia::render('Admin/Workshops/Registrations', [
            'registrations' => $registrations
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:draft,published,cancelled,completed',
        ]);

        $workshop = WorkshopModel::findOrFail($id);
        $workshop->status = $request->status;
        $workshop->save();

        return back()->with('success', 'Workshop status updated successfully.');
    }

    public function destroy(int $id)
    {
        $workshop = WorkshopModel::findOrFail($id);
        
        // Check if there are registrations
        if ($workshop->registrations()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete workshop with existing registrations.']);
        }

        $workshop->delete();

        return redirect()->route('admin.workshops.index')
            ->with('success', 'Workshop deleted successfully.');
    }
}

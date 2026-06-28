<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderForm;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderFormSubmission;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FormSubmissionController extends Controller
{
    /**
     * List all form submissions for a site
     */
    public function index(Request $request, int $siteId)
    {
        $site = GrowBuilderSite::where('id', $siteId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $query = GrowBuilderFormSubmission::where('site_id', $siteId)
            ->with('form')
            ->notSpam();

        // Filter by form
        if ($request->filled('form_id')) {
            $query->where('form_id', $request->form_id);
        }

        // Filter by read status
        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->unread();
            } elseif ($request->status === 'read') {
                $query->where('is_read', true);
            }
        }

        $submissions = $query->latest()->paginate(20);
        $forms = GrowBuilderForm::where('site_id', $siteId)->get(['id', 'name']);
        $unreadCount = GrowBuilderFormSubmission::where('site_id', $siteId)->unread()->notSpam()->count();

        return Inertia::render('GrowBuilder/FormSubmissions/Index', [
            'site' => [
                'id' => $site->id,
                'name' => $site->name,
                'subdomain' => $site->subdomain,
            ],
            'submissions' => $submissions,
            'forms' => $forms,
            'unreadCount' => $unreadCount,
            'filters' => $request->only(['form_id', 'status']),
        ]);
    }

    /**
     * Show a single submission
     */
    public function show(Request $request, int $siteId, int $submissionId)
    {
        $site = GrowBuilderSite::where('id', $siteId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $submission = GrowBuilderFormSubmission::where('site_id', $siteId)
            ->with('form')
            ->findOrFail($submissionId);

        // Mark as read
        if (!$submission->is_read) {
            $submission->markAsRead();
        }

        return Inertia::render('GrowBuilder/FormSubmissions/Show', [
            'site' => [
                'id' => $site->id,
                'name' => $site->name,
                'subdomain' => $site->subdomain,
            ],
            'submission' => $submission,
        ]);
    }

    /**
     * Mark submission as read/unread
     */
    public function toggleRead(Request $request, int $siteId, int $submissionId)
    {
        $site = GrowBuilderSite::where('id', $siteId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $submission = GrowBuilderFormSubmission::where('site_id', $siteId)
            ->findOrFail($submissionId);

        $submission->update(['is_read' => !$submission->is_read]);

        return back()->with('success', $submission->is_read ? 'Marked as read' : 'Marked as unread');
    }

    /**
     * Mark submission as spam
     */
    public function markSpam(Request $request, int $siteId, int $submissionId)
    {
        $site = GrowBuilderSite::where('id', $siteId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $submission = GrowBuilderFormSubmission::where('site_id', $siteId)
            ->findOrFail($submissionId);

        $submission->markAsSpam();

        return back()->with('success', 'Marked as spam');
    }

    /**
     * Delete a submission
     */
    public function destroy(Request $request, int $siteId, int $submissionId)
    {
        $site = GrowBuilderSite::where('id', $siteId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $submission = GrowBuilderFormSubmission::where('site_id', $siteId)
            ->findOrFail($submissionId);

        $submission->delete();

        return redirect()
            ->route('growbuilder.form-submissions.index', $siteId)
            ->with('success', 'Submission deleted');
    }

    /**
     * Export submissions as CSV
     */
    public function export(Request $request, int $siteId)
    {
        $site = GrowBuilderSite::where('id', $siteId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $submissions = GrowBuilderFormSubmission::where('site_id', $siteId)
            ->with('form')
            ->notSpam()
            ->latest()
            ->get();

        $filename = "{$site->subdomain}-form-submissions-" . now()->format('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($submissions) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Date', 'Form', 'Data', 'IP Address', 'Read', 'Spam']);
            
            foreach ($submissions as $submission) {
                fputcsv($file, [
                    $submission->created_at->format('Y-m-d H:i:s'),
                    $submission->form?->name ?? 'Unknown',
                    json_encode($submission->data),
                    $submission->ip_address,
                    $submission->is_read ? 'Yes' : 'No',
                    $submission->is_spam ? 'Yes' : 'No',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobApplicationRequest;
use App\Infrastructure\Persistence\Eloquent\JobPostingModel;
use App\Infrastructure\Persistence\Eloquent\JobApplicationModel;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CareersController extends Controller
{
    /**
     * Display active job postings
     */
    public function index(): Response
    {
        $jobPostings = JobPostingModel::with(['position', 'department'])
            ->active()
            ->orderBy('posted_at', 'desc')
            ->get();

        return Inertia::render('Careers/Index', [
            'jobPostings' => $jobPostings,
        ]);
    }

    /**
     * Show a specific job posting
     */
    public function show(JobPostingModel $jobPosting): Response
    {
        if (!$jobPosting->is_active || $jobPosting->isExpired()) {
            abort(404, 'Job posting not found or expired');
        }

        $jobPosting->load(['position', 'department']);

        return Inertia::render('Careers/Show', [
            'jobPosting' => $jobPosting,
        ]);
    }

    /**
     * Show application form for a job posting
     */
    public function apply(JobPostingModel $jobPosting): Response
    {
        if (!$jobPosting->is_active || $jobPosting->isExpired()) {
            abort(404, 'Job posting not found or expired');
        }

        $jobPosting->load(['position', 'department']);

        return Inertia::render('Careers/Apply', [
            'jobPosting' => $jobPosting,
        ]);
    }

    /**
     * Store a job application
     */
    public function storeApplication(StoreJobApplicationRequest $request, JobPostingModel $jobPosting): RedirectResponse
    {
        if (!$jobPosting->is_active || $jobPosting->isExpired()) {
            return back()->withErrors(['job' => 'This job posting is no longer available.']);
        }

        // Check for duplicate applications
        $existingApplication = JobApplicationModel::where('job_posting_id', $jobPosting->id)
            ->where('email', $request->email)
            ->first();

        if ($existingApplication) {
            return back()->withErrors(['email' => 'You have already applied for this position.']);
        }

        $applicationData = $request->validated();
        $applicationData['job_posting_id'] = $jobPosting->id;
        $applicationData['applied_at'] = now();

        // Handle resume upload
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('resumes', 'private');
            $applicationData['resume_path'] = $resumePath;
        }

        JobApplicationModel::create($applicationData);

        return redirect()->route('careers.success')
            ->with('success', 'Your application has been submitted successfully!');
    }

    /**
     * Show application success page
     */
    public function success(): Response
    {
        return Inertia::render('Careers/Success');
    }
}

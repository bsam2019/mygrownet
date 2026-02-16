<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\RecruitmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecruitmentController extends Controller
{
    public function __construct(
        private RecruitmentService $recruitmentService
    ) {}

    public function jobPostingsIndex(Request $request)
    {
        $companyId = $request->user()->company_id;
        $status = $request->query('status');
        
        $jobPostings = $this->recruitmentService->getJobPostings($companyId, $status);

        return Inertia::render('CMS/Recruitment/JobPostings', [
            'jobPostings' => $jobPostings,
        ]);
    }

    public function jobPostingsCreate()
    {
        return Inertia::render('CMS/Recruitment/CreateJobPosting');
    }

    public function jobPostingsStore(Request $request)
    {
        $validated = $request->validate([
            'job_title' => 'required|string|max:255',
            'department_id' => 'nullable|exists:cms_departments,id',
            'job_description' => 'required|string',
            'requirements' => 'nullable|string',
            'salary_range_min' => 'nullable|numeric|min:0',
            'salary_range_max' => 'nullable|numeric|min:0',
            'positions_available' => 'nullable|integer|min:1',
            'application_deadline' => 'nullable|date',
            'status' => 'required|in:draft,published,closed',
        ]);

        $validated['created_by'] = $request->user()->id;
        
        $jobPosting = $this->recruitmentService->createJobPosting(
            $request->user()->company_id,
            $validated
        );

        return redirect()->route('cms.recruitment.job-postings.index')
            ->with('success', 'Job posting created successfully');
    }

    public function applicationsIndex(Request $request, int $jobPostingId)
    {
        $applications = $this->recruitmentService->getApplications($jobPostingId);

        return Inertia::render('CMS/Recruitment/Applications', [
            'applications' => $applications,
            'jobPostingId' => $jobPostingId,
        ]);
    }

    public function applicationsUpdateStatus(Request $request, int $applicationId)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,screening,interview,offer,rejected,hired',
        ]);

        $this->recruitmentService->updateApplicationStatus($applicationId, $validated['status']);

        return back()->with('success', 'Application status updated');
    }

    public function interviewsIndex(Request $request)
    {
        $companyId = $request->user()->company_id;
        $interviews = $this->recruitmentService->getUpcomingInterviews($companyId);

        return Inertia::render('CMS/Recruitment/Interviews', [
            'interviews' => $interviews,
        ]);
    }

    public function interviewsStore(Request $request)
    {
        $validated = $request->validate([
            'application_id' => 'required|exists:cms_job_applications,id',
            'interview_type' => 'required|in:phone,video,in_person,technical,final',
            'scheduled_date' => 'required|date',
            'location' => 'nullable|string',
            'meeting_link' => 'nullable|url',
            'interviewer_ids' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $this->recruitmentService->scheduleInterview($validated['application_id'], $validated);

        return back()->with('success', 'Interview scheduled successfully');
    }

    public function evaluationsStore(Request $request, int $interviewId)
    {
        $validated = $request->validate([
            'technical_skills_rating' => 'nullable|integer|min:1|max:5',
            'communication_rating' => 'nullable|integer|min:1|max:5',
            'cultural_fit_rating' => 'nullable|integer|min:1|max:5',
            'overall_rating' => 'nullable|integer|min:1|max:5',
            'comments' => 'nullable|string',
            'recommendation' => 'nullable|in:strong_yes,yes,maybe,no,strong_no',
        ]);

        $this->recruitmentService->submitEvaluation(
            $interviewId,
            $request->user()->id,
            $validated
        );

        return back()->with('success', 'Evaluation submitted successfully');
    }
}

<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\JobPostingModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobApplicationModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InterviewModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InterviewEvaluationModel;
use App\Infrastructure\Persistence\Eloquent\CMS\OfferLetterModel;
use Illuminate\Support\Facades\Storage;

class RecruitmentService
{
    public function createJobPosting(int $companyId, array $data): JobPostingModel
    {
        return JobPostingModel::create([
            'company_id' => $companyId,
            'job_title' => $data['job_title'],
            'department_id' => $data['department_id'] ?? null,
            'job_description' => $data['job_description'],
            'requirements' => $data['requirements'] ?? null,
            'salary_range_min' => $data['salary_range_min'] ?? null,
            'salary_range_max' => $data['salary_range_max'] ?? null,
            'positions_available' => $data['positions_available'] ?? 1,
            'application_deadline' => $data['application_deadline'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'created_by' => $data['created_by'] ?? null,
        ]);
    }

    public function submitApplication(int $jobPostingId, array $data): JobApplicationModel
    {
        $cvPath = null;
        if (isset($data['cv'])) {
            $cvPath = $data['cv']->store('cms/applications/cvs', 'public');
        }

        return JobApplicationModel::create([
            'job_posting_id' => $jobPostingId,
            'applicant_name' => $data['applicant_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'cv_path' => $cvPath,
            'cover_letter' => $data['cover_letter'] ?? null,
            'status' => 'new',
        ]);
    }

    public function updateApplicationStatus(int $applicationId, string $status): JobApplicationModel
    {
        $application = JobApplicationModel::findOrFail($applicationId);
        $application->update(['status' => $status]);
        return $application;
    }

    public function scheduleInterview(int $applicationId, array $data): InterviewModel
    {
        return InterviewModel::create([
            'application_id' => $applicationId,
            'interview_type' => $data['interview_type'],
            'scheduled_date' => $data['scheduled_date'],
            'location' => $data['location'] ?? null,
            'meeting_link' => $data['meeting_link'] ?? null,
            'interviewer_ids' => $data['interviewer_ids'] ?? null,
            'status' => 'scheduled',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function submitEvaluation(int $interviewId, int $evaluatorId, array $data): InterviewEvaluationModel
    {
        return InterviewEvaluationModel::create([
            'interview_id' => $interviewId,
            'evaluator_id' => $evaluatorId,
            'technical_skills_rating' => $data['technical_skills_rating'] ?? null,
            'communication_rating' => $data['communication_rating'] ?? null,
            'cultural_fit_rating' => $data['cultural_fit_rating'] ?? null,
            'overall_rating' => $data['overall_rating'] ?? null,
            'comments' => $data['comments'] ?? null,
            'recommendation' => $data['recommendation'] ?? null,
        ]);
    }

    public function createOfferLetter(int $applicationId, array $data): OfferLetterModel
    {
        return OfferLetterModel::create([
            'application_id' => $applicationId,
            'job_title' => $data['job_title'],
            'salary' => $data['salary'],
            'start_date' => $data['start_date'],
            'offer_letter_path' => $data['offer_letter_path'] ?? null,
            'sent_date' => $data['sent_date'] ?? null,
            'response_deadline' => $data['response_deadline'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'terms' => $data['terms'] ?? null,
        ]);
    }

    public function getJobPostings(int $companyId, ?string $status = null)
    {
        $query = JobPostingModel::where('company_id', $companyId)
            ->with(['department', 'createdBy', 'applications']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getApplications(int $jobPostingId)
    {
        return JobApplicationModel::where('job_posting_id', $jobPostingId)
            ->with(['interviews.evaluations'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getUpcomingInterviews(int $companyId)
    {
        return InterviewModel::whereHas('application.jobPosting', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with(['application.jobPosting'])
            ->upcoming()
            ->get();
    }
}

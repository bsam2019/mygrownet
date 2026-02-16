<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\TrainingProgramModel;
use App\Infrastructure\Persistence\Eloquent\CMS\TrainingSessionModel;
use App\Infrastructure\Persistence\Eloquent\CMS\TrainingEnrollmentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\SkillModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerSkillModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CertificationModel;
use Illuminate\Support\Facades\DB;

class TrainingService
{
    public function createProgram(int $companyId, array $data): TrainingProgramModel
    {
        return TrainingProgramModel::create([
            'company_id' => $companyId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'category' => $data['category'],
            'level' => $data['level'] ?? 'beginner',
            'duration_hours' => $data['duration_hours'] ?? null,
            'cost' => $data['cost'] ?? 0,
            'provider' => $data['provider'] ?? null,
            'location' => $data['location'] ?? null,
            'max_participants' => $data['max_participants'] ?? null,
            'prerequisites' => $data['prerequisites'] ?? null,
            'learning_objectives' => $data['learning_objectives'] ?? null,
            'materials' => $data['materials'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'created_by' => $data['created_by'] ?? null,
        ]);
    }

    public function createSession(int $programId, array $data): TrainingSessionModel
    {
        $program = TrainingProgramModel::findOrFail($programId);
        
        return TrainingSessionModel::create([
            'program_id' => $programId,
            'session_name' => $data['session_name'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'venue' => $data['venue'] ?? null,
            'trainer_id' => $data['trainer_id'] ?? null,
            'trainer_name' => $data['trainer_name'] ?? null,
            'available_seats' => $data['available_seats'] ?? $program->max_participants,
            'status' => $data['status'] ?? 'scheduled',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function enrollWorker(int $sessionId, int $workerId, ?int $enrolledBy = null): TrainingEnrollmentModel
    {
        $session = TrainingSessionModel::with('enrollments')->findOrFail($sessionId);
        
        if ($session->available_seats && $session->enrollments()->count() >= $session->available_seats) {
            throw new \Exception('No available seats for this session');
        }

        return TrainingEnrollmentModel::create([
            'session_id' => $sessionId,
            'worker_id' => $workerId,
            'enrolled_date' => now(),
            'status' => 'enrolled',
            'enrolled_by' => $enrolledBy,
        ]);
    }

    public function completeEnrollment(int $enrollmentId, array $data): TrainingEnrollmentModel
    {
        $enrollment = TrainingEnrollmentModel::findOrFail($enrollmentId);
        
        $enrollment->update([
            'status' => 'completed',
            'completion_date' => $data['completion_date'] ?? now(),
            'assessment_score' => $data['assessment_score'] ?? null,
            'pass_status' => $data['pass_status'] ?? 'pending',
            'feedback' => $data['feedback'] ?? null,
        ]);

        if (isset($data['certificate_issued']) && $data['certificate_issued']) {
            $enrollment->update([
                'certificate_issued' => true,
                'certificate_number' => $data['certificate_number'] ?? $this->generateCertificateNumber($enrollment),
            ]);
        }

        return $enrollment->fresh();
    }

    public function addSkillToWorker(int $workerId, int $skillId, array $data): WorkerSkillModel
    {
        return WorkerSkillModel::updateOrCreate(
            ['worker_id' => $workerId, 'skill_id' => $skillId],
            [
                'proficiency_level' => $data['proficiency_level'] ?? 'basic',
                'acquired_date' => $data['acquired_date'] ?? now(),
                'last_assessed_date' => $data['last_assessed_date'] ?? now(),
                'verified_by' => $data['verified_by'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]
        );
    }

    public function addCertification(int $workerId, array $data): CertificationModel
    {
        return CertificationModel::create([
            'worker_id' => $workerId,
            'certification_name' => $data['certification_name'],
            'issuing_organization' => $data['issuing_organization'],
            'certificate_number' => $data['certificate_number'] ?? null,
            'issue_date' => $data['issue_date'],
            'expiry_date' => $data['expiry_date'] ?? null,
            'requires_renewal' => $data['requires_renewal'] ?? false,
            'document_path' => $data['document_path'] ?? null,
            'status' => 'active',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function getWorkerTrainingHistory(int $workerId)
    {
        return TrainingEnrollmentModel::with(['session.program'])
            ->where('worker_id', $workerId)
            ->orderBy('enrolled_date', 'desc')
            ->get();
    }

    public function getWorkerSkills(int $workerId)
    {
        return WorkerSkillModel::with('skill')
            ->where('worker_id', $workerId)
            ->get();
    }

    public function getExpiringCertifications(int $companyId, int $days = 30)
    {
        return CertificationModel::with('worker')
            ->whereHas('worker', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->expiringSoon($days)
            ->get();
    }

    private function generateCertificateNumber(TrainingEnrollmentModel $enrollment): string
    {
        $session = $enrollment->session;
        $program = $session->program;
        $year = now()->year;
        
        return sprintf(
            'CERT-%s-%d-%d',
            strtoupper(substr($program->title, 0, 3)),
            $year,
            $enrollment->id
        );
    }

    public function getTrainingStatistics(int $companyId)
    {
        $programs = TrainingProgramModel::where('company_id', $companyId)->count();
        $activeSessions = TrainingSessionModel::whereHas('program', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->where('status', 'scheduled')->count();
        
        $enrollments = TrainingEnrollmentModel::whereHas('session.program', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->count();
        
        $completions = TrainingEnrollmentModel::whereHas('session.program', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->where('status', 'completed')->count();

        return [
            'total_programs' => $programs,
            'active_sessions' => $activeSessions,
            'total_enrollments' => $enrollments,
            'completed_trainings' => $completions,
            'completion_rate' => $enrollments > 0 ? round(($completions / $enrollments) * 100, 2) : 0,
        ];
    }
}

<?php

namespace App\Domain\CMS\Installation\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\InstallationScheduleModel;
use App\Infrastructure\Persistence\Eloquent\CMS\SiteVisitModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InstallationPhotoModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerSignoffModel;
use App\Infrastructure\Persistence\Eloquent\CMS\DefectModel;
use Illuminate\Support\Facades\DB;

class InstallationService
{
    public function createSchedule(int $companyId, array $data): InstallationScheduleModel
    {
        return DB::transaction(function () use ($companyId, $data) {
            $schedule = InstallationScheduleModel::create([
                'company_id' => $companyId,
                'job_id' => $data['job_id'],
                'scheduled_date' => $data['scheduled_date'],
                'scheduled_time' => $data['scheduled_time'] ?? null,
                'team_leader_id' => $data['team_leader_id'],
                'site_contact_name' => $data['site_contact_name'] ?? null,
                'site_contact_phone' => $data['site_contact_phone'] ?? null,
                'equipment_required' => $data['equipment_required'] ?? null,
                'materials_required' => $data['materials_required'] ?? null,
                'estimated_hours' => $data['estimated_hours'] ?? null,
                'special_instructions' => $data['special_instructions'] ?? null,
                'status' => 'scheduled',
            ]);

            // Add team members if provided
            if (!empty($data['team_members'])) {
                foreach ($data['team_members'] as $member) {
                    $schedule->teamMembers()->create([
                        'user_id' => $member['user_id'],
                        'role' => $member['role'],
                    ]);
                }
            }

            return $schedule->load('job', 'teamLeader', 'teamMembers.user');
        });
    }

    public function updateSchedule(int $scheduleId, array $data): InstallationScheduleModel
    {
        $schedule = InstallationScheduleModel::findOrFail($scheduleId);
        $schedule->update($data);
        return $schedule->load('job', 'teamLeader', 'teamMembers.user');
    }

    public function recordSiteVisit(int $scheduleId, array $data): SiteVisitModel
    {
        return DB::transaction(function () use ($scheduleId, $data) {
            $visit = SiteVisitModel::create([
                'installation_schedule_id' => $scheduleId,
                'visit_date' => $data['visit_date'],
                'visit_type' => $data['visit_type'],
                'arrival_time' => $data['arrival_time'] ?? null,
                'departure_time' => $data['departure_time'] ?? null,
                'work_performed' => $data['work_performed'] ?? null,
                'issues_encountered' => $data['issues_encountered'] ?? null,
                'next_steps' => $data['next_steps'] ?? null,
            ]);

            // Add photos if provided
            if (!empty($data['photos'])) {
                foreach ($data['photos'] as $photo) {
                    $visit->photos()->create([
                        'installation_schedule_id' => $scheduleId,
                        'photo_type' => $photo['photo_type'],
                        'file_path' => $photo['file_path'],
                        'caption' => $photo['caption'] ?? null,
                        'sort_order' => $photo['sort_order'] ?? 0,
                    ]);
                }
            }

            return $visit->load('photos');
        });
    }

    public function recordCustomerSignoff(int $scheduleId, array $data): CustomerSignoffModel
    {
        $signoff = CustomerSignoffModel::create([
            'installation_schedule_id' => $scheduleId,
            'signoff_date' => $data['signoff_date'],
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'signature_data' => $data['signature_data'],
            'satisfaction_rating' => $data['satisfaction_rating'] ?? null,
            'feedback' => $data['feedback'] ?? null,
        ]);

        // Update schedule status
        $schedule = InstallationScheduleModel::findOrFail($scheduleId);
        $schedule->update(['status' => 'completed']);

        return $signoff;
    }

    public function createDefect(int $companyId, array $data): DefectModel
    {
        return DB::transaction(function () use ($companyId, $data) {
            $defect = DefectModel::create([
                'company_id' => $companyId,
                'installation_schedule_id' => $data['installation_schedule_id'] ?? null,
                'job_id' => $data['job_id'] ?? null,
                'defect_number' => $this->generateDefectNumber($companyId),
                'title' => $data['title'],
                'description' => $data['description'],
                'severity' => $data['severity'],
                'location' => $data['location'] ?? null,
                'reported_by' => $data['reported_by'],
                'reported_date' => $data['reported_date'],
                'assigned_to' => $data['assigned_to'] ?? null,
                'target_resolution_date' => $data['target_resolution_date'] ?? null,
                'status' => 'open',
            ]);

            // Add photos if provided
            if (!empty($data['photos'])) {
                foreach ($data['photos'] as $photo) {
                    $defect->photos()->create([
                        'file_path' => $photo['file_path'],
                        'caption' => $photo['caption'] ?? null,
                        'sort_order' => $photo['sort_order'] ?? 0,
                    ]);
                }
            }

            return $defect->load('photos', 'reportedBy', 'assignedTo');
        });
    }

    public function resolveDefect(int $defectId, array $data): DefectModel
    {
        $defect = DefectModel::findOrFail($defectId);
        $defect->update([
            'status' => 'resolved',
            'resolved_by' => $data['resolved_by'],
            'resolved_date' => $data['resolved_date'],
            'resolution_notes' => $data['resolution_notes'] ?? null,
        ]);

        return $defect->load('photos', 'reportedBy', 'assignedTo', 'resolvedBy');
    }

    private function generateDefectNumber(int $companyId): string
    {
        $year = date('Y');
        $lastDefect = DefectModel::where('company_id', $companyId)
            ->where('defect_number', 'like', "DEF-{$year}-%")
            ->orderBy('defect_number', 'desc')
            ->first();

        if ($lastDefect) {
            $lastNumber = (int) substr($lastDefect->defect_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('DEF-%s-%04d', $year, $newNumber);
    }

    public function getScheduleStatistics(int $companyId): array
    {
        return [
            'total_scheduled' => InstallationScheduleModel::where('company_id', $companyId)
                ->where('status', 'scheduled')->count(),
            'in_progress' => InstallationScheduleModel::where('company_id', $companyId)
                ->where('status', 'in_progress')->count(),
            'completed' => InstallationScheduleModel::where('company_id', $companyId)
                ->where('status', 'completed')->count(),
            'open_defects' => DefectModel::where('company_id', $companyId)
                ->where('status', 'open')->count(),
        ];
    }
}

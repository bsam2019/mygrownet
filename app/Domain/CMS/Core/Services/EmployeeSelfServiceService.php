<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\EmployeePortalAccessModel;
use App\Infrastructure\Persistence\Eloquent\CMS\AnnouncementModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LeaveRequestModel;
use App\Infrastructure\Persistence\Eloquent\CMS\AttendanceRecordModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeSelfServiceService
{
    public function createPortalAccess(int $workerId, string $email, string $password): EmployeePortalAccessModel
    {
        return EmployeePortalAccessModel::create([
            'worker_id' => $workerId,
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ]);
    }

    public function authenticateEmployee(string $email, string $password): ?EmployeePortalAccessModel
    {
        $access = EmployeePortalAccessModel::where('email', $email)
            ->where('is_active', true)
            ->first();

        if ($access && Hash::check($password, $access->password)) {
            $access->update(['last_login_at' => now()]);
            return $access;
        }

        return null;
    }

    public function getEmployeeDashboard(int $workerId): array
    {
        $worker = WorkerModel::with(['department', 'leaveBalances', 'skills'])->findOrFail($workerId);
        
        // Get pending leave requests
        $pendingLeave = LeaveRequestModel::where('worker_id', $workerId)
            ->where('status', 'pending')
            ->count();

        // Get recent attendance
        $recentAttendance = AttendanceRecordModel::where('worker_id', $workerId)
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        // Get announcements
        $announcements = $this->getEmployeeAnnouncements($worker->company_id, $workerId);

        return [
            'worker' => $worker,
            'pending_leave_requests' => $pendingLeave,
            'recent_attendance' => $recentAttendance,
            'announcements' => $announcements,
        ];
    }

    public function getEmployeeAnnouncements(int $companyId, int $workerId): array
    {
        $worker = WorkerModel::findOrFail($workerId);
        
        return AnnouncementModel::where('company_id', $companyId)
            ->published()
            ->where(function ($query) use ($worker) {
                $query->where('target_audience', 'all')
                    ->orWhere(function ($q) use ($worker) {
                        $q->where('target_audience', 'department')
                            ->where('department_id', $worker->department_id);
                    })
                    ->orWhereHas('recipients', function ($q) use ($worker) {
                        $q->where('worker_id', $worker->id);
                    });
            })
            ->orderBy('priority', 'desc')
            ->orderBy('publish_date', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function requestDocument(int $workerId, array $data): mixed
    {
        return DB::table('cms_document_requests')->insert([
            'worker_id' => $workerId,
            'document_type' => $data['document_type'],
            'description' => $data['description'] ?? null,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function submitFeedback(int $workerId, array $data): mixed
    {
        return DB::table('cms_employee_feedback')->insert([
            'worker_id' => $workerId,
            'type' => $data['type'],
            'category' => $data['category'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'status' => 'submitted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function requestProfileUpdate(int $workerId, array $currentData, array $requestedData, string $reason): mixed
    {
        return DB::table('cms_profile_update_requests')->insert([
            'worker_id' => $workerId,
            'current_data' => json_encode($currentData),
            'requested_data' => json_encode($requestedData),
            'reason' => $reason,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function generatePasswordResetToken(string $email): ?string
    {
        $access = EmployeePortalAccessModel::where('email', $email)->first();
        
        if (!$access) {
            return null;
        }

        $token = Str::random(64);
        
        $access->update([
            'reset_token' => $token,
            'reset_token_expires_at' => now()->addHours(2),
        ]);

        return $token;
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $access = EmployeePortalAccessModel::where('reset_token', $token)
            ->where('reset_token_expires_at', '>', now())
            ->first();

        if (!$access) {
            return false;
        }

        $access->update([
            'password' => $newPassword,
            'reset_token' => null,
            'reset_token_expires_at' => null,
        ]);

        return true;
    }
}

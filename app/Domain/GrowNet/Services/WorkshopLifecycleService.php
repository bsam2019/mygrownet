<?php

namespace App\Domain\GrowNet\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class WorkshopLifecycleService
{
    /**
     * Step 1: Member registers for a workshop.
     */
    public function registerMember(User $user, int $workshopId, string $delivery = 'online', string $language = 'English'): array
    {
        $workshop = DB::table('workshops')->where('id', $workshopId)->first();
        if (!$workshop) {
            return ['success' => false, 'message' => 'Workshop not found.'];
        }

        DB::table('workshop_registrations')->updateOrInsert(
            ['workshop_id' => $workshopId, 'user_id' => $user->id],
            [
                'preferred_delivery' => $delivery,
                'preferred_language' => $language,
                'lifecycle_state' => 'registered',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return [
            'success' => true,
            'message' => 'Successfully registered for workshop!',
            'workshop' => $workshop,
        ];
    }

    /**
     * Step 2: Confirm Attendance (Check-in).
     */
    public function confirmAttendance(int $registrationId, ?int $facilitatorUserId = null): bool
    {
        return DB::table('workshop_registrations')
            ->where('id', $registrationId)
            ->update([
                'lifecycle_state' => 'attended',
                'attended_at' => now(),
                'facilitator_user_id' => $facilitatorUserId,
                'updated_at' => now(),
            ]) > 0;
    }

    /**
     * Step 3: Verify Participation & Assess Practical Exercise.
     */
    public function verifyParticipationAndAssess(int $registrationId, bool $passed, ?string $notes = null): bool
    {
        $updated = DB::table('workshop_registrations')
            ->where('id', $registrationId)
            ->update([
                'lifecycle_state' => 'assessed',
                'participated_at' => now(),
                'assessed_at' => now(),
                'practical_passed' => $passed,
                'facilitator_notes' => $notes,
                'updated_at' => now(),
            ]);

        if ($updated && $passed) {
            $this->completeAndIssueCertificate($registrationId);
        }

        return $updated > 0;
    }

    /**
     * Step 4: Complete Workshop & Issue Certificate + Award Points (PB/MP).
     */
    public function completeAndIssueCertificate(int $registrationId): bool
    {
        $reg = DB::table('workshop_registrations')->where('id', $registrationId)->first();
        if (!$reg) return false;

        $workshop = DB::table('workshops')->where('id', $reg->workshop_id)->first();
        $user = User::find($reg->user_id);

        if (!$user || !$workshop) return false;

        // Issue Certificate
        $certId = DB::table('certificates')->insertGetId([
            'user_id' => $user->id,
            'title' => 'GrowNet Workshop Certificate: ' . ($workshop->topic ?? 'Practical Workshop'),
            'type' => 'workshop',
            'issued_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Award PB Points (150 PB) and MP Points (50 MP)
        DB::table('user_points')->where('user_id', $user->id)->increment('lifetime_points', $reg->pb_points_awarded ?? 150);
        DB::table('user_points')->where('user_id', $user->id)->increment('monthly_points', $reg->mp_points_awarded ?? 50);

        // Record also in workshop_attendance for backward compatibility
        DB::table('workshop_attendance')->updateOrInsert(
            ['workshop_id' => $reg->workshop_id, 'user_id' => $user->id],
            ['attended_at' => now(), 'created_at' => now(), 'updated_at' => now()]
        );

        return DB::table('workshop_registrations')
            ->where('id', $registrationId)
            ->update([
                'lifecycle_state' => 'completed',
                'certificate_id' => $certId,
                'updated_at' => now(),
            ]) > 0;
    }
}

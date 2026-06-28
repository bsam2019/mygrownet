<?php

namespace App\Domain\CMS\Subcontractors\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\SubcontractorModel;
use App\Infrastructure\Persistence\Eloquent\CMS\SubcontractorAssignmentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\SubcontractorPaymentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\SubcontractorRatingModel;
use Illuminate\Support\Facades\DB;

class SubcontractorService
{
    public function generateSubcontractorCode(int $companyId): string
    {
        $lastSubcontractor = SubcontractorModel::where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastSubcontractor) {
            $lastNumber = (int) substr($lastSubcontractor->code, 4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "SUB-{$newNumber}";
    }

    public function createSubcontractor(array $data): SubcontractorModel
    {
        return SubcontractorModel::create($data);
    }

    public function assignToProject(SubcontractorModel $subcontractor, array $data): SubcontractorAssignmentModel
    {
        return SubcontractorAssignmentModel::create([
            'subcontractor_id' => $subcontractor->id,
            'project_id' => $data['project_id'] ?? null,
            'job_id' => $data['job_id'] ?? null,
            'work_description' => $data['work_description'],
            'quoted_amount' => $data['quoted_amount'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function recordPayment(SubcontractorModel $subcontractor, array $data): SubcontractorPaymentModel
    {
        return DB::transaction(function () use ($subcontractor, $data) {
            $payment = SubcontractorPaymentModel::create([
                'subcontractor_id' => $subcontractor->id,
                'assignment_id' => $data['assignment_id'] ?? null,
                'payment_reference' => $this->generatePaymentReference($subcontractor->company_id),
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'],
                'description' => $data['description'] ?? null,
                'receipt_path' => $data['receipt_path'] ?? null,
            ]);

            return $payment;
        });
    }

    public function rateSubcontractor(SubcontractorAssignmentModel $assignment, array $data): SubcontractorRatingModel
    {
        return DB::transaction(function () use ($assignment, $data) {
            $rating = SubcontractorRatingModel::create([
                'subcontractor_id' => $assignment->subcontractor_id,
                'assignment_id' => $assignment->id,
                'rated_by' => $data['rated_by'],
                'quality_rating' => $data['quality_rating'],
                'timeliness_rating' => $data['timeliness_rating'],
                'communication_rating' => $data['communication_rating'],
                'professionalism_rating' => $data['professionalism_rating'],
                'comments' => $data['comments'] ?? null,
            ]);

            // Update subcontractor's overall rating
            $assignment->subcontractor->updateRating();

            // Update completed jobs count
            if ($assignment->status === 'completed') {
                $assignment->subcontractor->increment('completed_jobs');
            }

            return $rating;
        });
    }

    private function generatePaymentReference(int $companyId): string
    {
        $year = date('Y');
        $lastPayment = SubcontractorPaymentModel::whereHas('subcontractor', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
            ->where('payment_reference', 'like', "SUBPAY-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->payment_reference, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "SUBPAY-{$year}-{$newNumber}";
    }

    public function getSubcontractorPerformance(SubcontractorModel $subcontractor): array
    {
        $assignments = $subcontractor->assignments;
        $totalAssignments = $assignments->count();
        $completedAssignments = $assignments->where('status', 'completed')->count();
        $delayedAssignments = $assignments->filter->isDelayed()->count();

        $totalQuoted = $assignments->sum('quoted_amount');
        $totalActual = $assignments->sum('actual_amount');

        return [
            'total_assignments' => $totalAssignments,
            'completed_assignments' => $completedAssignments,
            'completion_rate' => $totalAssignments > 0 ? ($completedAssignments / $totalAssignments) * 100 : 0,
            'delayed_assignments' => $delayedAssignments,
            'average_rating' => $subcontractor->rating,
            'total_paid' => $subcontractor->payments()->sum('amount'),
            'cost_variance' => $totalActual - $totalQuoted,
        ];
    }
}

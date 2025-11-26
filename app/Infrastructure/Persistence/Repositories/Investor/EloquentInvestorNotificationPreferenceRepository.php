<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\InvestorNotificationPreference;
use App\Domain\Investor\Repositories\InvestorNotificationPreferenceRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Investor\InvestorNotificationPreferenceModel;

class EloquentInvestorNotificationPreferenceRepository implements InvestorNotificationPreferenceRepositoryInterface
{
    public function findById(int $id): ?InvestorNotificationPreference
    {
        $model = InvestorNotificationPreferenceModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByInvestorAccountId(int $investorAccountId): ?InvestorNotificationPreference
    {
        $model = InvestorNotificationPreferenceModel::where('investor_account_id', $investorAccountId)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findOrCreateForInvestor(int $investorAccountId): InvestorNotificationPreference
    {
        $existing = $this->findByInvestorAccountId($investorAccountId);
        
        if ($existing) {
            return $existing;
        }

        // Create default preferences
        $preference = InvestorNotificationPreference::createDefault($investorAccountId);
        return $this->save($preference);
    }

    public function save(InvestorNotificationPreference $preference): InvestorNotificationPreference
    {
        $data = [
            'investor_account_id' => $preference->getInvestorAccountId(),
            'email_announcements' => $preference->getEmailAnnouncements(),
            'email_financial_reports' => $preference->getEmailFinancialReports(),
            'email_dividends' => $preference->getEmailDividends(),
            'email_meetings' => $preference->getEmailMeetings(),
            'email_messages' => $preference->getEmailMessages(),
            'email_urgent_only' => $preference->getEmailUrgentOnly(),
            'digest_frequency' => $preference->getDigestFrequency(),
        ];

        if ($preference->getId()) {
            $model = InvestorNotificationPreferenceModel::find($preference->getId());
            $model->update($data);
        } else {
            $model = InvestorNotificationPreferenceModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findInvestorsForEmailType(string $emailType, bool $isUrgent = false): array
    {
        $query = InvestorNotificationPreferenceModel::query();

        // Map email type to column
        $column = match ($emailType) {
            'announcement' => 'email_announcements',
            'financial_report' => 'email_financial_reports',
            'dividend' => 'email_dividends',
            'meeting' => 'email_meetings',
            'message' => 'email_messages',
            default => null,
        };

        if ($column) {
            $query->where($column, true);
        }

        // If not urgent, exclude urgent-only users
        if (!$isUrgent) {
            $query->where('email_urgent_only', false);
        }

        return $query->get()->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findByDigestFrequency(string $frequency): array
    {
        return InvestorNotificationPreferenceModel::where('digest_frequency', $frequency)
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    private function toDomainEntity(InvestorNotificationPreferenceModel $model): InvestorNotificationPreference
    {
        return new InvestorNotificationPreference(
            id: $model->id,
            investorAccountId: $model->investor_account_id,
            emailAnnouncements: $model->email_announcements,
            emailFinancialReports: $model->email_financial_reports,
            emailDividends: $model->email_dividends,
            emailMeetings: $model->email_meetings,
            emailMessages: $model->email_messages,
            emailUrgentOnly: $model->email_urgent_only,
            digestFrequency: $model->digest_frequency,
            createdAt: $model->created_at ? new \DateTimeImmutable($model->created_at) : null,
            updatedAt: $model->updated_at ? new \DateTimeImmutable($model->updated_at) : null
        );
    }
}

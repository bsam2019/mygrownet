<?php

namespace App\Application\Services;

use App\Infrastructure\Persistence\Eloquent\EmailMarketing\EmailTemplateModel;
use App\Models\User;

class EmailTemplateService
{
    public function renderTemplate(EmailTemplateModel $template, User $user): string
    {
        $html = $template->html_content;
        $variables = $this->getUserVariables($user);

        foreach ($variables as $key => $value) {
            $html = str_replace("{{" . $key . "}}", $value, $html);
        }

        return $html;
    }

    public function renderSubject(string $subject, User $user): string
    {
        $variables = $this->getUserVariables($user);

        foreach ($variables as $key => $value) {
            $subject = str_replace("{{" . $key . "}}", $value, $subject);
        }

        return $subject;
    }

    private function getUserVariables(User $user): array
    {
        return [
            'user.name' => $user->name,
            'user.first_name' => explode(' ', $user->name)[0],
            'user.email' => $user->email,
            'user.phone' => $user->phone ?? '',
            'user.referral_code' => $user->referral_code ?? '',
            'user.referral_link' => url('/register?ref=' . ($user->referral_code ?? $user->id)),
            'user.network_size' => $user->referral_count ?? 0,
            'user.total_earnings' => number_format($user->calculateTotalEarnings(), 2),
            'user.starter_kit_tier' => ucfirst($user->starter_kit_tier ?? 'none'),
            'platform.name' => config('app.name', 'MyGrowNet'),
            'platform.url' => config('app.url'),
            'platform.support_email' => config('mail.from.address'),
            'unsubscribe_url' => route('email.unsubscribe', ['token' => $this->generateUnsubscribeToken($user)]),
        ];
    }

    private function generateUnsubscribeToken(User $user): string
    {
        return base64_encode($user->id . '|' . hash('sha256', $user->email . config('app.key')));
    }

    public function createTemplate(array $data): EmailTemplateModel
    {
        return EmailTemplateModel::create($data);
    }

    public function updateTemplate(int $templateId, array $data): EmailTemplateModel
    {
        $template = EmailTemplateModel::findOrFail($templateId);
        $template->update($data);
        return $template;
    }

    public function duplicateTemplate(int $templateId): EmailTemplateModel
    {
        $original = EmailTemplateModel::findOrFail($templateId);
        
        $duplicate = $original->replicate();
        $duplicate->name = $original->name . ' (Copy)';
        $duplicate->is_system = false;
        $duplicate->save();

        return $duplicate;
    }
}

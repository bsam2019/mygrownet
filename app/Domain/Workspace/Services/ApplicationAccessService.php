<?php

namespace App\Domain\Workspace\Services;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\Organization;
use App\Models\User;
use App\Domain\Workspace\ValueObjects\WorkspaceContext;
use Illuminate\Database\Eloquent\Collection;

class ApplicationAccessService
{
    public function getAvailableApps(User $user, WorkspaceContext $context): Collection
    {
        if ($context->isPersonal() || $context->isGuest()) {
            return $this->getPersonalApps($user);
        }

        return $this->getOrganizationApps($user, $context);
    }

    protected function getPersonalApps(User $user): Collection
    {
        return Application::where('is_active', true)
            ->whereIn('context_support', ['personal', 'both'])
            ->where(function ($q) use ($user) {
                $q->where('access_model', 'customer')
                  ->orWhereHas('userSubscriptions', fn($q) => $q->where('user_id', $user->id));
            })
            ->where('lifecycle', '!=', 'retired')
            ->where('operational_status', 'online')
            ->get()
            ->groupBy('category');
    }

    protected function getOrganizationApps(User $user, WorkspaceContext $context): Collection
    {
        $orgId = $context->organizationId;

        return Application::where('is_active', true)
            ->whereIn('context_support', ['organization', 'both'])
            ->whereHas('installations', fn($q) => $q->where('organization_id', $orgId)->where('status', 'active'))
            ->where('lifecycle', '!=', 'retired')
            ->where('operational_status', 'online')
            ->get()
            ->groupBy('category');
    }

    public function canAccess(User $user, Application $app, WorkspaceContext $context): bool
    {
        if ($app->operational_status !== 'online') {
            return false;
        }

        if ($app->lifecycle === 'retired') {
            return false;
        }

        if ($app->requires_organization_context && $context->isPersonal()) {
            return false;
        }

        if ($context->isOrganization()) {
            $orgId = $context->organizationId;

            $isMember = OrganizationMember::where('user_id', $user->id)
                ->where('organization_id', $orgId)
                ->where('status', 'active')
                ->exists();

            if (!$isMember) {
                return false;
            }

            $hasInstallation = Organization::whereHas('installations', fn($q) => $q
                ->where('application_id', $app->id)
                ->where('organization_id', $orgId)
                ->where('status', 'active')
            )->exists();

            if (!$hasInstallation) {
                return false;
            }

            $member = OrganizationMember::where('user_id', $user->id)
                ->where('organization_id', $orgId)
                ->first();

            if ($member && $member->status !== 'active') {
                return false;
            }

            return true;
        }

        if ($context->isPersonal()) {
            if ($app->access_model === 'customer' && !$app->subscription_required) {
                return true;
            }

            return $user->applicationSubscriptions()
                ->where('application_id', $app->id)
                ->where('status', 'active')
                ->exists();
        }

        return true;
    }
}

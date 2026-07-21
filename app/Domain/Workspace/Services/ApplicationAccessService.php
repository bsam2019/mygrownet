<?php

namespace App\Domain\Workspace\Services;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\Organization;
use App\Domain\Core\Models\OrganizationMember;
use App\Models\User;
use App\Domain\Workspace\ValueObjects\WorkspaceContext;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class ApplicationAccessService
{
    public function getAvailableApps(User $user, WorkspaceContext $context): EloquentCollection
    {
        if ($context->isPersonal() || $context->isGuest()) {
            return $this->getPersonalApps($user);
        }

        return $this->getOrganizationApps($user, $context);
    }

    protected function getPersonalApps(User $user): EloquentCollection
    {
        return Application::where('is_active', true)
            ->whereIn('context_support', ['personal', 'both'])
            ->where(function ($q) use ($user) {
                $q->where(function ($sub) {
                    $sub->whereIn('access_model', ['customer', 'both'])
                        ->where('subscription_required', false);
                })->orWhereHas('userSubscriptions', fn($q) => $q->where('user_id', $user->id)->where('status', 'active'));
            })
            ->where('lifecycle', '!=', 'retired')
            ->where('operational_status', 'online')
            ->get()
            ->groupBy('category');
    }

    protected function getOrganizationApps(User $user, WorkspaceContext $context): EloquentCollection
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

    public function getAllVisibleApps(User $user, WorkspaceContext $context): Collection
    {
        return Application::where('is_active', true)
            ->where('is_visible', true)
            ->whereIn('lifecycle', ['active', 'legacy'])
            ->where('operational_status', 'online')
            ->get()
            ->map(fn($app) => [
                'id' => $app->id,
                'name' => $app->name,
                'slug' => $app->slug,
                'category' => $app->category,
                'url' => $app->url,
                'available' => $this->canAccess($user, $app, $context),
                'reason' => $this->getUnavailabilityReason($user, $app, $context),
            ])
            ->groupBy('category');
    }

    public function getUnavailabilityReason(User $user, Application $app, WorkspaceContext $context): ?string
    {
        if ($app->operational_status !== 'online') {
            return 'Under Maintenance';
        }

        if ($app->lifecycle === 'retired') {
            return 'Discontinued';
        }

        if ($context->isPersonal()) {
            if ($app->requires_organization_context) {
                return 'Requires Organization';
            }

            if ($app->subscription_required) {
                $hasSub = $user->applicationSubscriptions()
                    ->where('application_id', $app->id)
                    ->where('status', 'active')
                    ->exists();
                if (!$hasSub) {
                    return 'Requires Subscription';
                }
            }

            if (!in_array($app->access_model, ['customer', 'both'])) {
                return 'Requires Organization';
            }

            return null;
        }

        if ($context->isOrganization()) {
            $orgId = $context->organizationId;

            $isMember = OrganizationMember::where('user_id', $user->id)
                ->where('organization_id', $orgId)
                ->where('status', 'active')
                ->exists();

            if (!$isMember) {
                return 'Not an Organization Member';
            }

            if ($app->requires_organization_context) {
                $hasInstallation = Organization::whereHas('installations', fn($q) => $q
                    ->where('application_id', $app->id)
                    ->where('organization_id', $orgId)
                    ->where('status', 'active')
                )->exists();

                if (!$hasInstallation) {
                    return 'Not Installed for Organization';
                }

                $member = OrganizationMember::where('user_id', $user->id)
                    ->where('organization_id', $orgId)
                    ->first();

                if ($member && $member->status !== 'active') {
                    return 'Membership Not Active';
                }
            }

            return null;
        }

        return null;
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
            if (in_array($app->access_model, ['customer', 'both']) && !$app->subscription_required) {
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

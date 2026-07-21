<?php

namespace App\Domain\Workspace\Services;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\OrganizationMember;
use App\Models\User;
use App\Domain\Workspace\ValueObjects\WorkspaceContext;
use Illuminate\Http\RedirectResponse;

class AppLaunchService
{
    public function __construct(
        private ApplicationAccessService $appAccess,
    ) {}

    public function buildPayload(Application $app, WorkspaceContext $context, User $user): array
    {
        $installation = null;
        if ($context->organizationId) {
            $installation = $app->installations()
                ->where('organization_id', $context->organizationId)
                ->first();
        }

        return [
            'application' => $app->slug,
            'context_type' => $context->type,
            'organization_id' => $context->organizationId,
            'organization_slug' => $context->organizationSlug,
            'user_id' => $user->id,
            'permissions' => $this->getPermissions($user, $app, $context),
            'installation_id' => $installation?->id,
            'installation_settings' => $installation?->settings,
        ];
    }

    public function launch(Application $app, WorkspaceContext $context, User $user): RedirectResponse
    {
        $payload = $this->buildPayload($app, $context, $user);

        session(['app_launch_payload' => $payload]);

        $url = $app->url;
        if (!$url) {
            $url = '/' . $app->slug;
        }
        if (str_starts_with($url, '/')) {
            $url = url($url);
        }

        return redirect()->away($url);
    }

    protected function getPermissions(User $user, Application $app, WorkspaceContext $context): array
    {
        if ($context->isOrganization()) {
            $member = OrganizationMember::where('user_id', $user->id)
                ->where('organization_id', $context->organizationId)
                ->first();

            return [
                'role' => $member?->role,
                'permissions' => $member?->permissions ?? [],
            ];
        }

        return [
            'role' => 'customer',
            'permissions' => [],
        ];
    }
}

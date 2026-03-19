<?php

namespace App\Services\GrowBuilder;

use App\Models\AgencyActivityLog;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Models\AgencyClient;
use App\Models\AgencyClientInvoice;

class ActivityLogger
{
    /**
     * Log an activity
     */
    public function log(
        int $agencyId,
        string $actionType,
        string $entityType,
        ?int $entityId,
        string $description,
        ?array $metadata = null
    ): void {
        AgencyActivityLog::create([
            'agency_id' => $agencyId,
            'user_id' => auth()->id(),
            'action_type' => $actionType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log site created
     */
    public function logSiteCreated(GrowBuilderSite $site): void
    {
        $this->log(
            $site->agency_id,
            'created',
            'site',
            $site->id,
            "Created site '{$site->site_name}' for client '{$site->client->client_name}'",
            ['site_type' => $site->site_type]
        );
    }

    /**
     * Log site published
     */
    public function logSitePublished(GrowBuilderSite $site): void
    {
        $this->log(
            $site->agency_id,
            'published',
            'site',
            $site->id,
            "Published site '{$site->site_name}'",
            ['domain' => $site->custom_domain ?? $site->internal_subdomain]
        );
    }

    /**
     * Log site suspended
     */
    public function logSiteSuspended(GrowBuilderSite $site, ?string $reason = null): void
    {
        $this->log(
            $site->agency_id,
            'suspended',
            'site',
            $site->id,
            "Suspended site '{$site->site_name}'" . ($reason ? ": {$reason}" : ''),
            ['reason' => $reason]
        );
    }

    /**
     * Log site deleted
     */
    public function logSiteDeleted(GrowBuilderSite $site): void
    {
        $this->log(
            $site->agency_id,
            'deleted',
            'site',
            $site->id,
            "Deleted site '{$site->site_name}'",
            ['site_type' => $site->site_type]
        );
    }

    /**
     * Log client created
     */
    public function logClientCreated(AgencyClient $client): void
    {
        $this->log(
            $client->agency_id,
            'created',
            'client',
            $client->id,
            "Added new client '{$client->client_name}'",
            ['client_type' => $client->client_type]
        );
    }

    /**
     * Log client updated
     */
    public function logClientUpdated(AgencyClient $client): void
    {
        $this->log(
            $client->agency_id,
            'updated',
            'client',
            $client->id,
            "Updated client '{$client->client_name}'",
            []
        );
    }

    /**
     * Log invoice created
     */
    public function logInvoiceCreated(AgencyClientInvoice $invoice): void
    {
        $this->log(
            $invoice->agency_id,
            'created',
            'invoice',
            $invoice->id,
            "Created invoice {$invoice->invoice_number} for {$invoice->client->client_name}",
            ['amount' => $invoice->total, 'due_date' => $invoice->due_date]
        );
    }

    /**
     * Log invoice paid
     */
    public function logInvoicePaid(AgencyClientInvoice $invoice): void
    {
        $this->log(
            $invoice->agency_id,
            'paid',
            'invoice',
            $invoice->id,
            "Invoice {$invoice->invoice_number} marked as paid",
            ['amount' => $invoice->total]
        );
    }

    /**
     * Log team member invited
     */
    public function logTeamMemberInvited(int $agencyId, string $email, string $roleName): void
    {
        $this->log(
            $agencyId,
            'invited',
            'team_member',
            null,
            "Invited {$email} as {$roleName}",
            ['email' => $email, 'role' => $roleName]
        );
    }

    /**
     * Log team member joined
     */
    public function logTeamMemberJoined(int $agencyId, int $userId, string $userName): void
    {
        $this->log(
            $agencyId,
            'joined',
            'team_member',
            $userId,
            "{$userName} joined the agency",
            []
        );
    }

    /**
     * Log team member removed
     */
    public function logTeamMemberRemoved(int $agencyId, int $userId, string $userName): void
    {
        $this->log(
            $agencyId,
            'removed',
            'team_member',
            $userId,
            "Removed {$userName} from agency",
            []
        );
    }
}

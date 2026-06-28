<?php

namespace App\Domain\Investor\Services;

use App\Models\InvestorRelationsDocument;
use App\Models\InvestorRelationsUpdate;
use App\Models\InvestorDocumentAccessLog;
use App\Models\QuarterlyReport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class InvestorRelationsService
{
    /**
     * Get all published documents
     */
    public function getPublishedDocuments(?string $type = null): Collection
    {
        $query = InvestorRelationsDocument::where('status', 'published')
            ->where('is_public', true)
            ->orderBy('document_date', 'desc');
            
        if ($type) {
            $query->where('document_type', $type);
        }
        
        return $query->get()->map(fn($doc) => [
            'id' => $doc->id,
            'type' => $doc->document_type,
            'type_label' => $this->getDocumentTypeLabel($doc->document_type),
            'title' => $doc->title,
            'description' => $doc->description,
            'period' => $doc->period,
            'document_date' => $doc->document_date->format('Y-m-d'),
            'document_date_formatted' => $doc->document_date->format('M j, Y'),
            'file_type' => $doc->file_type,
            'file_size' => $this->formatFileSize($doc->file_size),
            'requires_acknowledgment' => $doc->requires_acknowledgment,
        ]);
    }

    /**
     * Get documents by category
     */
    public function getDocumentsByCategory(): array
    {
        $categories = [
            'reports' => ['quarterly_report', 'annual_report', 'audit_report'],
            'governance' => ['agm_notice', 'board_update', 'ceo_letter'],
            'legal' => ['shareholder_agreement', 'articles_of_association', 'compliance_certificate'],
            'tax' => ['tax_statement'],
        ];
        
        $result = [];
        
        foreach ($categories as $category => $types) {
            $docs = InvestorRelationsDocument::where('status', 'published')
                ->where('is_public', true)
                ->whereIn('document_type', $types)
                ->orderBy('document_date', 'desc')
                ->limit(10)
                ->get();
                
            $result[$category] = [
                'label' => ucfirst($category),
                'count' => $docs->count(),
                'documents' => $docs->map(fn($doc) => [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'type' => $doc->document_type,
                    'date' => $doc->document_date->format('M j, Y'),
                    'file_type' => $doc->file_type,
                ]),
            ];
        }
        
        return $result;
    }

    /**
     * Get recent updates
     */
    public function getRecentUpdates(int $limit = 10): Collection
    {
        return InvestorRelationsUpdate::where('is_published', true)
            ->where('publish_date', '<=', now())
            ->orderBy('publish_date', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($update) => [
                'id' => $update->id,
                'title' => $update->title,
                'content' => $update->content,
                'type' => $update->update_type,
                'type_label' => $this->getUpdateTypeLabel($update->update_type),
                'priority' => $update->priority,
                'publish_date' => $update->publish_date->format('Y-m-d'),
                'publish_date_formatted' => $update->publish_date->format('M j, Y'),
                'publish_date_human' => $update->publish_date->diffForHumans(),
            ]);
    }

    /**
     * Log document access
     */
    public function logDocumentAccess(int $investorId, int $documentId, bool $downloaded = false): void
    {
        InvestorDocumentAccessLog::create([
            'investor_account_id' => $investorId,
            'document_id' => $documentId,
            'accessed_at' => now(),
            'downloaded' => $downloaded,
        ]);
    }

    /**
     * Download document
     */
    public function downloadDocument(int $documentId, int $investorId): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $document = InvestorRelationsDocument::findOrFail($documentId);
        
        if (!Storage::disk('private')->exists($document->file_path)) {
            throw new \Exception('Document file not found');
        }
        
        // Log access
        $this->logDocumentAccess($investorId, $documentId, true);
        
        $filename = "{$document->title}.{$document->file_type}";
        
        return Storage::disk('private')->download($document->file_path, $filename);
    }

    /**
     * Check if investor has acknowledged document
     */
    public function hasAcknowledged(int $investorId, int $documentId): bool
    {
        return InvestorDocumentAccessLog::where('investor_account_id', $investorId)
            ->where('document_id', $documentId)
            ->where('acknowledged', true)
            ->exists();
    }

    /**
     * Mark document as acknowledged
     */
    public function acknowledgeDocument(int $investorId, int $documentId): bool
    {
        return InvestorDocumentAccessLog::updateOrCreate(
            [
                'investor_account_id' => $investorId,
                'document_id' => $documentId,
            ],
            [
                'accessed_at' => now(),
                'acknowledged' => true,
            ]
        );
    }

    /**
     * Get document type label
     */
    private function getDocumentTypeLabel(string $type): string
    {
        return match($type) {
            'quarterly_report' => 'Quarterly Report',
            'annual_report' => 'Annual Report',
            'agm_notice' => 'AGM Notice',
            'board_update' => 'Board Update',
            'ceo_letter' => 'CEO Letter',
            'shareholder_agreement' => 'Shareholder Agreement',
            'articles_of_association' => 'Articles of Association',
            'compliance_certificate' => 'Compliance Certificate',
            'audit_report' => 'Audit Report',
            'tax_statement' => 'Tax Statement',
            default => ucwords(str_replace('_', ' ', $type)),
        };
    }

    /**
     * Get update type label
     */
    private function getUpdateTypeLabel(string $type): string
    {
        return match($type) {
            'news' => 'Company News',
            'milestone' => 'Milestone',
            'financial' => 'Financial Update',
            'governance' => 'Governance',
            'general' => 'General Update',
            default => ucfirst($type),
        };
    }

    /**
     * Get latest quarterly report
     */
    public function getLatestQuarterlyReport()
    {
        return QuarterlyReport::orderBy('year', 'desc')
            ->orderBy('quarter', 'desc')
            ->first();
    }

    /**
     * Get upcoming meetings
     */
    public function getUpcomingMeetings(int $limit = 5): array
    {
        // For now, return empty array since we don't have a meetings table yet
        // This can be implemented when the meetings feature is added
        return [];
    }

    /**
     * Format file size
     */
    private function formatFileSize(?int $bytes): string
    {
        if (!$bytes) return 'Unknown';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

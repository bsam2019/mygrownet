<?php

namespace App\Services\GrowBuilder;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsExportService
{
    /**
     * Export analytics data as PDF
     */
    public function exportToPdf(int $siteId, string $siteName, string $subdomain, array $analyticsData, string $period): \Barryvdh\DomPDF\PDF
    {
        $data = [
            'site' => [
                'id' => $siteId,
                'name' => $siteName,
                'subdomain' => $subdomain,
                'url' => "https://{$subdomain}.mygrownet.com",
            ],
            'period' => $this->formatPeriod($period),
            'generatedAt' => now()->format('F j, Y \a\t g:i A'),
            'analytics' => $analyticsData,
            'summary' => $this->generateSummary($analyticsData),
        ];

        $pdf = Pdf::loadView('pdf.growbuilder.analytics-report', $data);
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf;
    }

    /**
     * Export analytics data as CSV
     */
    public function exportToCsv(int $siteId, string $period): string
    {
        $days = match ($period) {
            '7d' => 7,
            '90d' => 90,
            default => 30,
        };

        $startDate = now()->subDays($days);

        // Get detailed page view data
        $pageViews = GrowBuilderPageView::where('site_id', $siteId)
            ->where('created_at', '>=', $startDate)
            ->select([
                'created_at',
                'path',
                'referrer',
                'ip_address',
                'country',
                'device_type',
                'user_agent'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $csvData = [];
        $csvData[] = [
            'Date',
            'Time',
            'Page Path',
            'Referrer',
            'Country',
            'Device Type',
            'IP Address',
            'User Agent'
        ];

        foreach ($pageViews as $view) {
            $csvData[] = [
                $view->created_at->format('Y-m-d'),
                $view->created_at->format('H:i:s'),
                $view->path ?? '/',
                $view->referrer ?? 'Direct',
                $view->country ?? 'Unknown',
                $view->device_type ?? 'Unknown',
                $view->ip_address,
                $view->user_agent ?? 'Unknown'
            ];
        }

        return $this->arrayToCsv($csvData);
    }

    /**
     * Export analytics data as Excel (CSV format)
     */
    public function exportToExcel(int $siteId, string $siteName, array $analyticsData, string $period): string
    {
        $csvData = [];
        
        // Header information
        $csvData[] = ['Site Analytics Report'];
        $csvData[] = ['Site Name:', $siteName];
        $csvData[] = ['Period:', $this->formatPeriod($period)];
        $csvData[] = ['Generated:', now()->format('F j, Y \a\t g:i A')];
        $csvData[] = []; // Empty row

        // Summary metrics
        $csvData[] = ['SUMMARY METRICS'];
        $csvData[] = ['Metric', 'Value'];
        $csvData[] = ['Total Page Views', number_format($analyticsData['totalViews'])];
        $csvData[] = ['Unique Visitors', number_format($analyticsData['totalVisitors'])];
        $csvData[] = ['Average Session Duration', $this->formatDuration($analyticsData['avgSessionDuration'])];
        $csvData[] = ['New Visitors', number_format($analyticsData['newVisitors'])];
        $csvData[] = ['Returning Visitors', number_format($analyticsData['returningVisitors'])];
        $csvData[] = []; // Empty row

        // Top pages
        if (!empty($analyticsData['topPages'])) {
            $csvData[] = ['TOP PAGES'];
            $csvData[] = ['Page Path', 'Views', 'Avg Time', 'Bounce Rate'];
            foreach ($analyticsData['topPages'] as $page) {
                $csvData[] = [
                    $page['path'],
                    $page['views'],
                    $this->formatDuration($page['avgTime']),
                    round($page['bounceRate'], 1) . '%'
                ];
            }
            $csvData[] = []; // Empty row
        }

        // Geographic data
        if (!empty($analyticsData['geographicData'])) {
            $csvData[] = ['GEOGRAPHIC DATA'];
            $csvData[] = ['Country', 'Visitors', 'Percentage'];
            foreach ($analyticsData['geographicData'] as $geo) {
                $csvData[] = [
                    $geo['country'],
                    $geo['visitors'],
                    $geo['percentage'] . '%'
                ];
            }
            $csvData[] = []; // Empty row
        }

        // Traffic sources
        if (!empty($analyticsData['trafficSources'])) {
            $csvData[] = ['TRAFFIC SOURCES'];
            $csvData[] = ['Source', 'Visitors', 'Percentage'];
            foreach ($analyticsData['trafficSources'] as $source) {
                $csvData[] = [
                    $source['source'],
                    $source['visitors'],
                    $source['percentage'] . '%'
                ];
            }
            $csvData[] = []; // Empty row
        }

        // Device breakdown
        if (!empty($analyticsData['deviceStats'])) {
            $csvData[] = ['DEVICE BREAKDOWN'];
            $csvData[] = ['Device Type', 'Sessions', 'Percentage'];
            foreach ($analyticsData['deviceStats'] as $device) {
                $csvData[] = [
                    ucfirst($device['device']),
                    $device['count'],
                    $device['percentage'] . '%'
                ];
            }
        }

        return $this->arrayToCsv($csvData);
    }

    /**
     * Convert array to CSV string
     */
    private function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');
        
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
     * Format period for display
     */
    private function formatPeriod(string $period): string
    {
        return match ($period) {
            '7d' => 'Last 7 days',
            '30d' => 'Last 30 days',
            '90d' => 'Last 90 days',
            default => 'Last 30 days',
        };
    }

    /**
     * Format duration in seconds to readable format
     */
    private function formatDuration(int $seconds): string
    {
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        return sprintf('%d:%02d', $minutes, $remainingSeconds);
    }

    /**
     * Generate summary insights
     */
    private function generateSummary(array $data): array
    {
        $summary = [];
        
        // Performance insights
        if ($data['totalVisitors'] > 0) {
            $pagesPerSession = round($data['totalViews'] / $data['totalVisitors'], 1);
            $summary['pagesPerSession'] = $pagesPerSession;
            
            if ($pagesPerSession >= 3) {
                $summary['engagement'] = 'High engagement - visitors are exploring multiple pages';
            } elseif ($pagesPerSession >= 2) {
                $summary['engagement'] = 'Good engagement - visitors view multiple pages';
            } else {
                $summary['engagement'] = 'Low engagement - consider improving content or navigation';
            }
        }

        // Geographic insights
        if (!empty($data['geographicData'])) {
            $topCountry = $data['geographicData'][0];
            $summary['topCountry'] = $topCountry['country'];
            $summary['topCountryPercentage'] = $topCountry['percentage'];
        }

        // Traffic source insights
        if (!empty($data['trafficSources'])) {
            $topSource = $data['trafficSources'][0];
            $summary['topTrafficSource'] = $topSource['source'];
            $summary['topSourcePercentage'] = $topSource['percentage'];
        }

        // Device insights
        if (!empty($data['deviceStats'])) {
            $mobilePercentage = 0;
            foreach ($data['deviceStats'] as $device) {
                if ($device['device'] === 'mobile') {
                    $mobilePercentage = $device['percentage'];
                    break;
                }
            }
            $summary['mobilePercentage'] = $mobilePercentage;
            
            if ($mobilePercentage >= 60) {
                $summary['deviceRecommendation'] = 'Mobile-first audience - ensure excellent mobile experience';
            } elseif ($mobilePercentage >= 40) {
                $summary['deviceRecommendation'] = 'Balanced mobile/desktop usage - optimize for both';
            } else {
                $summary['deviceRecommendation'] = 'Desktop-heavy audience - focus on desktop experience';
            }
        }

        return $summary;
    }
}
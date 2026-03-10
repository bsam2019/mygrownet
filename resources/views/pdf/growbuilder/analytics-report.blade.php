<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Report - {{ $site['name'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }

        .header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .header .subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 4px;
        }

        .header .meta {
            font-size: 11px;
            opacity: 0.8;
        }

        .container {
            padding: 0 30px;
        }

        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1d4ed8;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e5e7eb;
        }

        .metrics-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .metric-row {
            display: table-row;
        }

        .metric-cell {
            display: table-cell;
            width: 33.33%;
            padding: 15px;
            text-align: center;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .metric-value {
            font-size: 20px;
            font-weight: bold;
            color: #1d4ed8;
            display: block;
            margin-bottom: 4px;
        }

        .metric-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th {
            background: #f9fafb;
            color: #374151;
            font-weight: bold;
            padding: 10px;
            text-align: left;
            border: 1px solid #e5e7eb;
            font-size: 11px;
        }

        .data-table td {
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
        }

        .data-table tr:nth-child(even) {
            background: #f9fafb;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 4px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #2563eb, #1d4ed8);
            border-radius: 3px;
        }

        .summary-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .summary-title {
            font-size: 14px;
            font-weight: bold;
            color: #0c4a6e;
            margin-bottom: 10px;
        }

        .summary-item {
            margin-bottom: 8px;
            font-size: 11px;
            line-height: 1.5;
        }

        .summary-item strong {
            color: #0c4a6e;
        }

        .chart-placeholder {
            width: 100%;
            height: 120px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 11px;
            margin-bottom: 15px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }

        .page-break {
            page-break-before: always;
        }

        .two-column {
            display: table;
            width: 100%;
        }

        .column {
            display: table-cell;
            width: 50%;
            padding-right: 15px;
            vertical-align: top;
        }

        .column:last-child {
            padding-right: 0;
            padding-left: 15px;
        }

        .highlight {
            background: #fef3c7;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Website Analytics Report</h1>
        <div class="subtitle">{{ $site['name'] }}</div>
        <div class="subtitle">{{ $site['url'] }}</div>
        <div class="meta">{{ $period }} • Generated {{ $generatedAt }}</div>
    </div>

    <div class="container">
        <!-- Key Metrics -->
        <div class="section">
            <h2 class="section-title">Key Performance Metrics</h2>
            
            <div class="metrics-grid">
                <div class="metric-row">
                    <div class="metric-cell">
                        <span class="metric-value">{{ number_format($analytics['totalViews']) }}</span>
                        <span class="metric-label">Page Views</span>
                    </div>
                    <div class="metric-cell">
                        <span class="metric-value">{{ number_format($analytics['totalVisitors']) }}</span>
                        <span class="metric-label">Unique Visitors</span>
                    </div>
                    <div class="metric-cell">
                        <span class="metric-value">{{ floor($analytics['avgSessionDuration'] / 60) }}:{{ str_pad($analytics['avgSessionDuration'] % 60, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="metric-label">Avg Session Duration</span>
                    </div>
                </div>
            </div>

            <div class="metrics-grid">
                <div class="metric-row">
                    <div class="metric-cell">
                        <span class="metric-value">{{ number_format($analytics['newVisitors']) }}</span>
                        <span class="metric-label">New Visitors</span>
                    </div>
                    <div class="metric-cell">
                        <span class="metric-value">{{ number_format($analytics['returningVisitors']) }}</span>
                        <span class="metric-label">Returning Visitors</span>
                    </div>
                    <div class="metric-cell">
                        <span class="metric-value">{{ $analytics['totalVisitors'] > 0 ? round(($analytics['totalViews'] / $analytics['totalVisitors']), 1) : 0 }}</span>
                        <span class="metric-label">Pages per Session</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Summary -->
        @if(!empty($summary))
        <div class="section">
            <h2 class="section-title">Performance Insights</h2>
            <div class="summary-box">
                <div class="summary-title">Key Insights</div>
                
                @if(isset($summary['engagement']))
                <div class="summary-item">
                    <strong>Engagement:</strong> {{ $summary['engagement'] }}
                </div>
                @endif

                @if(isset($summary['topCountry']))
                <div class="summary-item">
                    <strong>Top Location:</strong> {{ $summary['topCountry'] }} accounts for <span class="highlight">{{ $summary['topCountryPercentage'] }}%</span> of visitors
                </div>
                @endif

                @if(isset($summary['topTrafficSource']))
                <div class="summary-item">
                    <strong>Primary Traffic Source:</strong> {{ $summary['topTrafficSource'] }} drives <span class="highlight">{{ $summary['topSourcePercentage'] }}%</span> of traffic
                </div>
                @endif

                @if(isset($summary['deviceRecommendation']))
                <div class="summary-item">
                    <strong>Device Strategy:</strong> {{ $summary['deviceRecommendation'] }}
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Two Column Layout -->
        <div class="two-column">
            <!-- Top Pages -->
            <div class="column">
                <div class="section">
                    <h2 class="section-title">Top Pages</h2>
                    @if(!empty($analytics['topPages']))
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Page</th>
                                    <th>Views</th>
                                    <th>Avg Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($analytics['topPages'], 0, 8) as $page)
                                <tr>
                                    <td>{{ $page['path'] ?: '/' }}</td>
                                    <td>{{ number_format($page['views']) }}</td>
                                    <td>{{ floor($page['avgTime'] / 60) }}:{{ str_pad($page['avgTime'] % 60, 2, '0', STR_PAD_LEFT) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p style="color: #6b7280; font-style: italic;">No page data available for this period.</p>
                    @endif
                </div>
            </div>

            <!-- Device Breakdown -->
            <div class="column">
                <div class="section">
                    <h2 class="section-title">Device Breakdown</h2>
                    @if(!empty($analytics['deviceStats']))
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Device</th>
                                    <th>Sessions</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($analytics['deviceStats'] as $device)
                                <tr>
                                    <td>{{ ucfirst($device['device']) }}</td>
                                    <td>{{ number_format($device['count']) }}</td>
                                    <td>
                                        {{ $device['percentage'] }}%
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: {{ $device['percentage'] }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p style="color: #6b7280; font-style: italic;">No device data available for this period.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Page Break for Second Page -->
        <div class="page-break"></div>

        <!-- Geographic Data -->
        <div class="section">
            <h2 class="section-title">Geographic Distribution</h2>
            @if(!empty($analytics['geographicData']))
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Country</th>
                            <th>Visitors</th>
                            <th>Percentage</th>
                            <th>Visual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analytics['geographicData'] as $geo)
                        <tr>
                            <td>{{ $geo['country'] }}</td>
                            <td>{{ number_format($geo['visitors']) }}</td>
                            <td>{{ $geo['percentage'] }}%</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $geo['percentage'] }}%"></div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #6b7280; font-style: italic;">No geographic data available for this period.</p>
            @endif
        </div>

        <!-- Traffic Sources -->
        <div class="section">
            <h2 class="section-title">Traffic Sources</h2>
            @if(!empty($analytics['trafficSources']))
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th>Visitors</th>
                            <th>Percentage</th>
                            <th>Visual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analytics['trafficSources'] as $source)
                        <tr>
                            <td>{{ $source['source'] }}</td>
                            <td>{{ number_format($source['visitors']) }}</td>
                            <td>{{ $source['percentage'] }}%</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $source['percentage'] }}%"></div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #6b7280; font-style: italic;">No traffic source data available for this period.</p>
            @endif
        </div>

        <!-- Conversion Goals -->
        @if(!empty($analytics['conversionGoals']))
        <div class="section">
            <h2 class="section-title">Conversion Goals</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Goal</th>
                        <th>Completions</th>
                        <th>Conversion Rate</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analytics['conversionGoals'] as $goal)
                    <tr>
                        <td>{{ $goal['name'] }}</td>
                        <td>{{ number_format($goal['completions']) }}</td>
                        <td>{{ $goal['conversionRate'] }}%</td>
                        <td>{{ isset($goal['value']) ? 'K' . number_format($goal['value'] / 100, 2) : 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Generated by MyGrowNet GrowBuilder Analytics • {{ $generatedAt }}</p>
        <p>This report contains data for {{ $period }} ending {{ now()->format('F j, Y') }}</p>
    </div>
</body>
</html>
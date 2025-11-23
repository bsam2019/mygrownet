<?php

namespace App\Services\BusinessPlan;

use App\Models\BusinessPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ExportService
{
    /**
     * Export business plan in specified format
     */
    public function export(BusinessPlan $plan, string $type): string
    {
        return match($type) {
            'template' => $this->exportTemplate($plan),
            'pdf' => $this->exportPDF($plan),
            'word' => $this->exportWord($plan),
            'pitch_deck' => $this->exportPitchDeck($plan),
            default => throw new \Exception("Unsupported export type: {$type}"),
        };
    }

    /**
     * Export as editable template (HTML/Markdown)
     */
    private function exportTemplate(BusinessPlan $plan): string
    {
        $content = $this->generateHTMLContent($plan);
        
        $filename = "template_{$plan->id}_" . time() . ".html";
        $directory = storage_path("app/business-plans/{$plan->user_id}");
        
        // Ensure directory exists
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $fullPath = $directory . '/' . $filename;
        file_put_contents($fullPath, $content);
        
        // Return relative path for storage
        return "business-plans/{$plan->user_id}/{$filename}";
    }

    /**
     * Export as PDF
     */
    private function exportPDF(BusinessPlan $plan): string
    {
        $html = $this->generateHTMLContent($plan);
        
        // Generate PDF using DomPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');
        
        $filename = "plan_{$plan->id}_" . time() . ".pdf";
        $directory = storage_path("app/business-plans/{$plan->user_id}");
        
        // Ensure directory exists
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $fullPath = $directory . '/' . $filename;
        $pdf->save($fullPath);
        
        // Return relative path for storage
        return "business-plans/{$plan->user_id}/{$filename}";
    }

    /**
     * Export as Word document (RTF format - compatible with Word)
     */
    private function exportWord(BusinessPlan $plan): string
    {
        $content = $this->generateRTFContent($plan);
        
        $filename = "plan_{$plan->id}_" . time() . ".rtf";
        $directory = storage_path("app/business-plans/{$plan->user_id}");
        
        // Ensure directory exists
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $fullPath = $directory . '/' . $filename;
        file_put_contents($fullPath, $content);
        
        // Return relative path for storage
        return "business-plans/{$plan->user_id}/{$filename}";
    }

    /**
     * Export as pitch deck
     */
    private function exportPitchDeck(BusinessPlan $plan): string
    {
        $content = $this->generatePitchDeckHTML($plan);
        
        $filename = "pitch_deck_{$plan->id}_" . time() . ".html";
        $directory = storage_path("app/business-plans/{$plan->user_id}");
        
        // Ensure directory exists
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $fullPath = $directory . '/' . $filename;
        file_put_contents($fullPath, $content);
        
        // Return relative path for storage
        return "business-plans/{$plan->user_id}/{$filename}";
    }

    /**
     * Generate HTML content for business plan
     */
    private function generateHTMLContent(BusinessPlan $plan): string
    {
        $monthlyProfit = ($plan->expected_monthly_revenue ?? 0) - ($plan->monthly_operating_costs ?? 0);
        $profitMargin = ($plan->expected_monthly_revenue ?? 0) > 0 
            ? round(($monthlyProfit / $plan->expected_monthly_revenue) * 100, 1) 
            : 0;
        $breakEven = $monthlyProfit > 0 
            ? ceil(($plan->startup_costs ?? 0) / $monthlyProfit) 
            : '∞';
        $breakEvenText = $breakEven !== '∞' ? $breakEven . ' months' : $breakEven;

        $businessName = htmlspecialchars($plan->business_name ?? 'Business Plan');
        $industry = htmlspecialchars($plan->industry ?? 'N/A');
        $city = htmlspecialchars($plan->city ?? 'N/A');
        $country = htmlspecialchars($plan->country ?? 'N/A');
        $legalStructure = htmlspecialchars($plan->legal_structure ?? 'N/A');
        $generatedDate = $plan->created_at ? $plan->created_at->format('F d, Y') : date('F d, Y');
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$businessName} - Business Plan</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #2563eb; border-bottom: 3px solid #2563eb; padding-bottom: 10px; }
        h2 { color: #1d4ed8; margin-top: 30px; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; }
        h3 { color: #374151; margin-top: 20px; }
        .section { margin-bottom: 30px; }
        .financial-box { background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .highlight { background: #dbeafe; padding: 10px; border-left: 4px solid #2563eb; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{$businessName}</h1>
    <p><strong>Industry:</strong> {$industry} | <strong>Location:</strong> {$city}, {$country}</p>
    <p><strong>Legal Structure:</strong> {$legalStructure}</p>
    
    <div class="section">
        <h2>1. Executive Summary</h2>
        <h3>Mission Statement</h3>
        <p>{$this->formatText($plan->mission_statement)}</p>
        <h3>Vision Statement</h3>
        <p>{$this->formatText($plan->vision_statement)}</p>
        <h3>Background</h3>
        <p>{$this->formatText($plan->background)}</p>
    </div>
    
    <div class="section">
        <h2>2. Problem & Solution</h2>
        <h3>Problem Statement</h3>
        <p>{$this->formatText($plan->problem_statement)}</p>
        <h3>Our Solution</h3>
        <p>{$this->formatText($plan->solution_description)}</p>
        <h3>Competitive Advantage</h3>
        <p>{$this->formatText($plan->competitive_advantage)}</p>
    </div>
    
    <div class="section">
        <h2>3. Products & Services</h2>
        <p>{$this->formatText($plan->product_description)}</p>
        <h3>Key Features</h3>
        <p>{$this->formatText($plan->product_features)}</p>
        <h3>Pricing Strategy</h3>
        <p>{$this->formatText($plan->pricing_strategy)}</p>
        <h3>Unique Selling Points</h3>
        <p>{$this->formatText($plan->unique_selling_points)}</p>
    </div>
    
    <div class="section">
        <h2>4. Market Analysis</h2>
        <h3>Target Market</h3>
        <p>{$this->formatText($plan->target_market)}</p>
        <h3>Customer Demographics</h3>
        <p>{$this->formatText($plan->customer_demographics)}</p>
        <h3>Market Size</h3>
        <p>{$this->formatText($plan->market_size)}</p>
        <h3>Competitive Analysis</h3>
        <p>{$this->formatText($plan->competitive_analysis)}</p>
    </div>
    
    <div class="section">
        <h2>5. Marketing & Sales Strategy</h2>
        <h3>Marketing Channels</h3>
        <p>{$this->formatText($plan->marketing_channels)}</p>
        <h3>Branding Approach</h3>
        <p>{$this->formatText($plan->branding_approach)}</p>
        <h3>Sales Channels</h3>
        <p>{$this->formatText($plan->sales_channels)}</p>
        <h3>Customer Retention</h3>
        <p>{$this->formatText($plan->customer_retention)}</p>
    </div>
    
    <div class="section">
        <h2>6. Operations Plan</h2>
        <h3>Daily Operations</h3>
        <p>{$this->formatText($plan->daily_operations)}</p>
        <h3>Staff & Roles</h3>
        <p>{$this->formatText($plan->staff_roles)}</p>
        <h3>Equipment & Tools</h3>
        <p>{$this->formatText($plan->equipment_tools)}</p>
    </div>
    
    <div class="section">
        <h2>7. Financial Plan</h2>
        <div class="financial-box">
            <table>
                <tr><th>Item</th><th>Amount (K)</th></tr>
                <tr><td>Startup Costs</td><td>{$this->formatNumber($plan->startup_costs)}</td></tr>
                <tr><td>Monthly Operating Costs</td><td>{$this->formatNumber($plan->monthly_operating_costs)}</td></tr>
                <tr><td>Expected Monthly Revenue</td><td>{$this->formatNumber($plan->expected_monthly_revenue)}</td></tr>
                <tr><td><strong>Monthly Profit</strong></td><td><strong>{$this->formatNumber($monthlyProfit)}</strong></td></tr>
                <tr><td>Profit Margin</td><td>{$profitMargin}%</td></tr>
                <tr><td>Break-Even Point</td><td>{$breakEvenText}</td></tr>
            </table>
        </div>
    </div>
    
    <div class="section">
        <h2>8. Risk Analysis</h2>
        <h3>Key Risks</h3>
        <p>{$this->formatText($plan->key_risks)}</p>
        <h3>Mitigation Strategies</h3>
        <p>{$this->formatText($plan->mitigation_strategies)}</p>
    </div>
    
    <div class="section">
        <h2>9. Implementation Roadmap</h2>
        <h3>Timeline</h3>
        <p>{$this->formatText($plan->timeline)}</p>
        <h3>Key Milestones</h3>
        <p>{$this->formatText($plan->milestones)}</p>
        <h3>Responsibilities</h3>
        <p>{$this->formatText($plan->responsibilities)}</p>
    </div>
    
    <div class="highlight">
        <p><strong>Document Generated:</strong> {$generatedDate}</p>
        <p><strong>Powered by:</strong> MyGrowNet Business Plan Generator</p>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Format text for HTML output
     */
    private function formatText(?string $text): string
    {
        if (empty($text)) {
            return '<em>Not provided</em>';
        }
        return nl2br(htmlspecialchars($text));
    }

    /**
     * Format number with thousand separators
     */
    private function formatNumber($number): string
    {
        if ($number === null) {
            return '0';
        }
        return number_format((float)$number, 0, '.', ',');
    }

    /**
     * Generate pitch deck HTML
     */
    private function generatePitchDeckHTML(BusinessPlan $plan): string
    {
        $monthlyProfit = ($plan->expected_monthly_revenue ?? 0) - ($plan->monthly_operating_costs ?? 0);
        $businessName = htmlspecialchars($plan->business_name ?? 'Business Plan');
        $industry = htmlspecialchars($plan->industry ?? 'N/A');
        $city = htmlspecialchars($plan->city ?? 'N/A');
        $country = htmlspecialchars($plan->country ?? 'N/A');
        
        // Simplified pitch deck with key slides
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$businessName} - Pitch Deck</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .slide { min-height: 100vh; padding: 60px; display: flex; flex-direction: column; justify-content: center; page-break-after: always; }
        .slide h1 { font-size: 48px; color: #2563eb; margin-bottom: 20px; }
        .slide h2 { font-size: 36px; color: #1d4ed8; margin-bottom: 15px; }
        .slide p { font-size: 20px; line-height: 1.6; }
        .cover { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; text-align: center; }
        .problem { background: #fef3c7; }
        .solution { background: #d1fae5; }
        .market { background: #dbeafe; }
        .financials { background: #f3e8ff; }
    </style>
</head>
<body>
    <div class="slide cover">
        <h1>{$businessName}</h1>
        <p style="font-size: 28px;">{$industry}</p>
        <p>{$city}, {$country}</p>
    </div>
    
    <div class="slide problem">
        <h2>The Problem</h2>
        <p>{$this->formatText($plan->problem_statement)}</p>
    </div>
    
    <div class="slide solution">
        <h2>Our Solution</h2>
        <p>{$this->formatText($plan->solution_description)}</p>
    </div>
    
    <div class="slide">
        <h2>Product/Service</h2>
        <p>{$this->formatText($plan->product_description)}</p>
    </div>
    
    <div class="slide market">
        <h2>Market Opportunity</h2>
        <p><strong>Target Market:</strong> {$this->formatText($plan->target_market)}</p>
        <p><strong>Market Size:</strong> {$this->formatText($plan->market_size)}</p>
    </div>
    
    <div class="slide">
        <h2>Business Model</h2>
        <p><strong>Pricing:</strong> {$this->formatText($plan->pricing_strategy)}</p>
        <p><strong>Sales Channels:</strong> {$this->formatText($plan->sales_channels)}</p>
    </div>
    
    <div class="slide financials">
        <h2>Financials</h2>
        <p><strong>Startup Costs:</strong> K{$this->formatNumber($plan->startup_costs)}</p>
        <p><strong>Monthly Revenue:</strong> K{$this->formatNumber($plan->expected_monthly_revenue)}</p>
        <p><strong>Monthly Profit:</strong> K{$this->formatNumber($monthlyProfit)}</p>
    </div>
    
    <div class="slide">
        <h2>Team & Execution</h2>
        <p>{$this->formatText($plan->responsibilities)}</p>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Generate RTF content for Word compatibility
     */
    private function generateRTFContent(BusinessPlan $plan): string
    {
        $monthlyProfit = ($plan->expected_monthly_revenue ?? 0) - ($plan->monthly_operating_costs ?? 0);
        $profitMargin = ($plan->expected_monthly_revenue ?? 0) > 0 
            ? round(($monthlyProfit / $plan->expected_monthly_revenue) * 100, 1) 
            : 0;
        $breakEven = $monthlyProfit > 0 
            ? ceil(($plan->startup_costs ?? 0) / $monthlyProfit) 
            : '∞';

        $businessName = $this->rtfEscape($plan->business_name ?? 'Business Plan');
        $industry = $this->rtfEscape($plan->industry ?? 'N/A');
        $city = $this->rtfEscape($plan->city ?? 'N/A');
        $country = $this->rtfEscape($plan->country ?? 'N/A');
        $legalStructure = $this->rtfEscape($plan->legal_structure ?? 'N/A');

        // RTF document structure
        $rtf = "{\\rtf1\\ansi\\deff0\n";
        $rtf .= "{\\fonttbl{\\f0 Arial;}{\\f1 Times New Roman;}}\n";
        $rtf .= "{\\colortbl;\\red37\\green99\\blue235;\\red29\\green78\\blue216;}\n";
        $rtf .= "\\viewkind4\\uc1\\pard\\sa200\\sl276\\slmult1\\lang9\\f0\\fs28\n\n";

        // Title
        $rtf .= "{\\b\\fs48\\cf1 {$businessName}}\\par\n";
        $rtf .= "\\fs22 {\\b Industry:} {$industry} | {\\b Location:} {$city}, {$country}\\par\n";
        $rtf .= "{\\b Legal Structure:} {$legalStructure}\\par\\par\n\n";

        // Section 1: Executive Summary
        $rtf .= "{\\b\\fs32\\cf2 1. Executive Summary}\\par\n";
        $rtf .= "{\\b\\fs24 Mission Statement}\\par\n";
        $rtf .= $this->rtfEscape($plan->mission_statement ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Vision Statement}\\par\n";
        $rtf .= $this->rtfEscape($plan->vision_statement ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Background}\\par\n";
        $rtf .= $this->rtfEscape($plan->background ?? 'Not provided') . "\\par\\par\n\n";

        // Section 2: Problem & Solution
        $rtf .= "{\\b\\fs32\\cf2 2. Problem & Solution}\\par\n";
        $rtf .= "{\\b\\fs24 Problem Statement}\\par\n";
        $rtf .= $this->rtfEscape($plan->problem_statement ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Our Solution}\\par\n";
        $rtf .= $this->rtfEscape($plan->solution_description ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Competitive Advantage}\\par\n";
        $rtf .= $this->rtfEscape($plan->competitive_advantage ?? 'Not provided') . "\\par\\par\n\n";

        // Section 3: Products & Services
        $rtf .= "{\\b\\fs32\\cf2 3. Products & Services}\\par\n";
        $rtf .= $this->rtfEscape($plan->product_description ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Key Features}\\par\n";
        $rtf .= $this->rtfEscape($plan->product_features ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Pricing Strategy}\\par\n";
        $rtf .= $this->rtfEscape($plan->pricing_strategy ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Unique Selling Points}\\par\n";
        $rtf .= $this->rtfEscape($plan->unique_selling_points ?? 'Not provided') . "\\par\\par\n\n";

        // Section 4: Market Analysis
        $rtf .= "{\\b\\fs32\\cf2 4. Market Analysis}\\par\n";
        $rtf .= "{\\b\\fs24 Target Market}\\par\n";
        $rtf .= $this->rtfEscape($plan->target_market ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Customer Demographics}\\par\n";
        $rtf .= $this->rtfEscape($plan->customer_demographics ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Market Size}\\par\n";
        $rtf .= $this->rtfEscape($plan->market_size ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Competitive Analysis}\\par\n";
        $rtf .= $this->rtfEscape($plan->competitive_analysis ?? 'Not provided') . "\\par\\par\n\n";

        // Section 5: Marketing & Sales
        $rtf .= "{\\b\\fs32\\cf2 5. Marketing & Sales Strategy}\\par\n";
        $rtf .= "{\\b\\fs24 Marketing Channels}\\par\n";
        $rtf .= $this->rtfEscape($plan->marketing_channels ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Branding Approach}\\par\n";
        $rtf .= $this->rtfEscape($plan->branding_approach ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Sales Channels}\\par\n";
        $rtf .= $this->rtfEscape($plan->sales_channels ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Customer Retention}\\par\n";
        $rtf .= $this->rtfEscape($plan->customer_retention ?? 'Not provided') . "\\par\\par\n\n";

        // Section 6: Operations
        $rtf .= "{\\b\\fs32\\cf2 6. Operations Plan}\\par\n";
        $rtf .= "{\\b\\fs24 Daily Operations}\\par\n";
        $rtf .= $this->rtfEscape($plan->daily_operations ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Staff & Roles}\\par\n";
        $rtf .= $this->rtfEscape($plan->staff_roles ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Equipment & Tools}\\par\n";
        $rtf .= $this->rtfEscape($plan->equipment_tools ?? 'Not provided') . "\\par\\par\n\n";

        // Section 7: Financials
        $rtf .= "{\\b\\fs32\\cf2 7. Financial Plan}\\par\n";
        $rtf .= "{\\b Startup Costs:} K" . $this->formatNumber($plan->startup_costs) . "\\par\n";
        $rtf .= "{\\b Monthly Operating Costs:} K" . $this->formatNumber($plan->monthly_operating_costs) . "\\par\n";
        $rtf .= "{\\b Expected Monthly Revenue:} K" . $this->formatNumber($plan->expected_monthly_revenue) . "\\par\n";
        $rtf .= "{\\b Monthly Profit:} K" . $this->formatNumber($monthlyProfit) . "\\par\n";
        $rtf .= "{\\b Profit Margin:} {$profitMargin}%\\par\n";
        $rtf .= "{\\b Break-Even Point:} {$breakEven} " . ($breakEven !== '∞' ? 'months' : '') . "\\par\\par\n\n";

        // Section 8: Risk Analysis
        $rtf .= "{\\b\\fs32\\cf2 8. Risk Analysis}\\par\n";
        $rtf .= "{\\b\\fs24 Key Risks}\\par\n";
        $rtf .= $this->rtfEscape($plan->key_risks ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Mitigation Strategies}\\par\n";
        $rtf .= $this->rtfEscape($plan->mitigation_strategies ?? 'Not provided') . "\\par\\par\n\n";

        // Section 9: Implementation
        $rtf .= "{\\b\\fs32\\cf2 9. Implementation Roadmap}\\par\n";
        $rtf .= "{\\b\\fs24 Timeline}\\par\n";
        $rtf .= $this->rtfEscape($plan->timeline ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Key Milestones}\\par\n";
        $rtf .= $this->rtfEscape($plan->milestones ?? 'Not provided') . "\\par\\par\n";
        $rtf .= "{\\b\\fs24 Responsibilities}\\par\n";
        $rtf .= $this->rtfEscape($plan->responsibilities ?? 'Not provided') . "\\par\\par\n\n";

        // Footer
        $rtf .= "\\par\\par\n";
        $rtf .= "{\\i Document Generated: " . ($plan->created_at ? $plan->created_at->format('F d, Y') : date('F d, Y')) . "}\\par\n";
        $rtf .= "{\\i Powered by: MyGrowNet Business Plan Generator}\\par\n";

        $rtf .= "}";

        return $rtf;
    }

    /**
     * Escape special characters for RTF format
     */
    private function rtfEscape(?string $text): string
    {
        if (empty($text)) {
            return 'Not provided';
        }
        
        // Replace special RTF characters
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('{', '\\{', $text);
        $text = str_replace('}', '\\}', $text);
        $text = str_replace("\n", "\\par\n", $text);
        
        return $text;
    }
}

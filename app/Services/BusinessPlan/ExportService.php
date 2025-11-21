<?php

namespace App\Services\BusinessPlan;

use App\Models\BusinessPlan;
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
        
        $filename = "business-plans/{$plan->user_id}/template_{$plan->id}_" . time() . ".html";
        Storage::put($filename, $content);
        
        return $filename;
    }

    /**
     * Export as PDF
     */
    private function exportPDF(BusinessPlan $plan): string
    {
        // TODO: Implement PDF generation using DomPDF or similar
        // For now, generate HTML and save as PDF placeholder
        
        $content = $this->generateHTMLContent($plan);
        
        $filename = "business-plans/{$plan->user_id}/plan_{$plan->id}_" . time() . ".pdf";
        
        // Placeholder: Save as HTML until PDF library is integrated
        Storage::put($filename, $content);
        
        return $filename;
    }

    /**
     * Export as Word document
     */
    private function exportWord(BusinessPlan $plan): string
    {
        // TODO: Implement Word generation using PHPWord
        // For now, generate HTML
        
        $content = $this->generateHTMLContent($plan);
        
        $filename = "business-plans/{$plan->user_id}/plan_{$plan->id}_" . time() . ".docx";
        
        // Placeholder: Save as HTML until Word library is integrated
        Storage::put($filename, $content);
        
        return $filename;
    }

    /**
     * Export as pitch deck
     */
    private function exportPitchDeck(BusinessPlan $plan): string
    {
        $content = $this->generatePitchDeckHTML($plan);
        
        $filename = "business-plans/{$plan->user_id}/pitch_deck_{$plan->id}_" . time() . ".html";
        Storage::put($filename, $content);
        
        return $filename;
    }

    /**
     * Generate HTML content for business plan
     */
    private function generateHTMLContent(BusinessPlan $plan): string
    {
        $monthlyProfit = $plan->expected_monthly_revenue - $plan->monthly_operating_costs;
        $profitMargin = $plan->expected_monthly_revenue > 0 
            ? round(($monthlyProfit / $plan->expected_monthly_revenue) * 100, 1) 
            : 0;
        $breakEven = $monthlyProfit > 0 
            ? ceil($plan->startup_costs / $monthlyProfit) 
            : 0;

        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$plan->business_name} - Business Plan</title>
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
    <h1>{$plan->business_name}</h1>
    <p><strong>Industry:</strong> {$plan->industry} | <strong>Location:</strong> {$plan->city}, {$plan->country}</p>
    <p><strong>Legal Structure:</strong> {$plan->legal_structure}</p>
    
    <div class="section">
        <h2>1. Executive Summary</h2>
        <h3>Mission Statement</h3>
        <p>{$plan->mission_statement}</p>
        <h3>Vision Statement</h3>
        <p>{$plan->vision_statement}</p>
        <h3>Background</h3>
        <p>{$plan->background}</p>
    </div>
    
    <div class="section">
        <h2>2. Problem & Solution</h2>
        <h3>Problem Statement</h3>
        <p>{$plan->problem_statement}</p>
        <h3>Our Solution</h3>
        <p>{$plan->solution_description}</p>
        <h3>Competitive Advantage</h3>
        <p>{$plan->competitive_advantage}</p>
    </div>
    
    <div class="section">
        <h2>3. Products & Services</h2>
        <p>{$plan->product_description}</p>
        <h3>Key Features</h3>
        <p>{$plan->product_features}</p>
        <h3>Pricing Strategy</h3>
        <p>{$plan->pricing_strategy}</p>
        <h3>Unique Selling Points</h3>
        <p>{$plan->unique_selling_points}</p>
    </div>
    
    <div class="section">
        <h2>4. Market Analysis</h2>
        <h3>Target Market</h3>
        <p>{$plan->target_market}</p>
        <h3>Customer Demographics</h3>
        <p>{$plan->customer_demographics}</p>
        <h3>Market Size</h3>
        <p>{$plan->market_size}</p>
        <h3>Competitive Analysis</h3>
        <p>{$plan->competitive_analysis}</p>
    </div>
    
    <div class="section">
        <h2>5. Marketing & Sales Strategy</h2>
        <h3>Marketing Channels</h3>
        <p>{$plan->marketing_channels}</p>
        <h3>Branding Approach</h3>
        <p>{$plan->branding_approach}</p>
        <h3>Sales Channels</h3>
        <p>{$plan->sales_channels}</p>
        <h3>Customer Retention</h3>
        <p>{$plan->customer_retention}</p>
    </div>
    
    <div class="section">
        <h2>6. Operations Plan</h2>
        <h3>Daily Operations</h3>
        <p>{$plan->daily_operations}</p>
        <h3>Staff & Roles</h3>
        <p>{$plan->staff_roles}</p>
        <h3>Equipment & Tools</h3>
        <p>{$plan->equipment_tools}</p>
    </div>
    
    <div class="section">
        <h2>7. Financial Plan</h2>
        <div class="financial-box">
            <table>
                <tr><th>Item</th><th>Amount (K)</th></tr>
                <tr><td>Startup Costs</td><td>{$plan->startup_costs}</td></tr>
                <tr><td>Monthly Operating Costs</td><td>{$plan->monthly_operating_costs}</td></tr>
                <tr><td>Expected Monthly Revenue</td><td>{$plan->expected_monthly_revenue}</td></tr>
                <tr><td><strong>Monthly Profit</strong></td><td><strong>{$monthlyProfit}</strong></td></tr>
                <tr><td>Profit Margin</td><td>{$profitMargin}%</td></tr>
                <tr><td>Break-Even Point</td><td>{$breakEven} months</td></tr>
            </table>
        </div>
    </div>
    
    <div class="section">
        <h2>8. Risk Analysis</h2>
        <h3>Key Risks</h3>
        <p>{$plan->key_risks}</p>
        <h3>Mitigation Strategies</h3>
        <p>{$plan->mitigation_strategies}</p>
    </div>
    
    <div class="section">
        <h2>9. Implementation Roadmap</h2>
        <h3>Timeline</h3>
        <p>{$plan->timeline}</p>
        <h3>Key Milestones</h3>
        <p>{$plan->milestones}</p>
        <h3>Responsibilities</h3>
        <p>{$plan->responsibilities}</p>
    </div>
    
    <div class="highlight">
        <p><strong>Document Generated:</strong> {$plan->created_at->format('F d, Y')}</p>
        <p><strong>Powered by:</strong> MyGrowNet Business Plan Generator</p>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Generate pitch deck HTML
     */
    private function generatePitchDeckHTML(BusinessPlan $plan): string
    {
        $monthlyProfit = $plan->expected_monthly_revenue - $plan->monthly_operating_costs;
        
        // Simplified pitch deck with key slides
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$plan->business_name} - Pitch Deck</title>
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
        <h1>{$plan->business_name}</h1>
        <p style="font-size: 28px;">{$plan->industry}</p>
        <p>{$plan->city}, {$plan->country}</p>
    </div>
    
    <div class="slide problem">
        <h2>The Problem</h2>
        <p>{$plan->problem_statement}</p>
    </div>
    
    <div class="slide solution">
        <h2>Our Solution</h2>
        <p>{$plan->solution_description}</p>
    </div>
    
    <div class="slide">
        <h2>Product/Service</h2>
        <p>{$plan->product_description}</p>
    </div>
    
    <div class="slide market">
        <h2>Market Opportunity</h2>
        <p><strong>Target Market:</strong> {$plan->target_market}</p>
        <p><strong>Market Size:</strong> {$plan->market_size}</p>
    </div>
    
    <div class="slide">
        <h2>Business Model</h2>
        <p><strong>Pricing:</strong> {$plan->pricing_strategy}</p>
        <p><strong>Sales Channels:</strong> {$plan->sales_channels}</p>
    </div>
    
    <div class="slide financials">
        <h2>Financials</h2>
        <p><strong>Startup Costs:</strong> K{$plan->startup_costs}</p>
        <p><strong>Monthly Revenue:</strong> K{$plan->expected_monthly_revenue}</p>
        <p><strong>Monthly Profit:</strong> K{$plan->expected_monthly_revenue - $plan->monthly_operating_costs}</p>
    </div>
    
    <div class="slide">
        <h2>Team & Execution</h2>
        <p>{$plan->responsibilities}</p>
    </div>
</body>
</html>
HTML;
    }
}

<?php

namespace App\Services\BusinessPlan;

use App\Services\GrowBuilder\AIContentService;

class AIGenerationService
{
    private AIContentService $ai;

    public function __construct(AIContentService $ai)
    {
        $this->ai = $ai;
    }

    public function generate(string $field, array $context): string
    {
        $prompts = $this->getPrompts();

        $fieldLabel = $prompts[$field] ?? $field;
        $businessName = $context['business_name'] ?? 'the business';
        $industry = $context['industry'] ?? '';
        $country = $context['country'] ?? 'Zambia';
        $userPrompt = $context['user_ai_instructions'] ?? '';

        $systemPrompt = "You are a professional business plan writer and strategic advisor for entrepreneurs in {$country}. "
            . "You write clear, specific, and actionable business content. "
            . "Keep responses concise (3-5 sentences unless asked for more detail). "
            . "Be specific to the {$industry} industry. "
            . "Write in professional but accessible language.";

        $userMessage = "For the business \"{$businessName}\" in the {$industry} industry:\n\n"
            . "Generate content for: {$fieldLabel}\n\n"
            . ($userPrompt ? "Additional instructions from the user: {$userPrompt}\n\n" : "")
            . "Provide specific, detailed content that a business in {$country} can use directly.";

        $attempts = 0;
        $maxAttempts = 3;
        $lastException = null;
        while ($attempts < $maxAttempts) {
            try {
                return $this->ai->smartChatWithPrompt($systemPrompt, $userMessage);
            } catch (\Exception $e) {
                $lastException = $e;
                $attempts++;
                if ($attempts < $maxAttempts) {
                    usleep($attempts * 1000000);
                }
            }
        }
        \Illuminate\Support\Facades\Log::warning('AI generation failed after retries', [
            'field' => $field,
            'error' => $lastException?->getMessage(),
        ]);
        return $this->generateFallbackContent($field, $context);
    }

    public function chat(string $message, array $context): array
    {
        $prompts = $this->getPrompts();
        $businessName = $context['business_name'] ?? 'the business';
        $industry = $context['industry'] ?? '';
        $country = $context['country'] ?? 'Zambia';
        $currentStep = $context['current_step'] ?? 'unknown';
        $stepLabel = $context['current_step_label'] ?? '';

        $fieldsList = '';
        $filledFields = [];
        $emptyFields = [];
        foreach ($prompts as $key => $label) {
            $fieldsList .= "- {$key}: {$label}\n";
            $val = $context[$key] ?? '';
            if (!empty($val)) {
                $filledFields[] = $key;
            } else {
                $emptyFields[] = $key;
            }
        }

        $systemPrompt = "You are a business plan writing assistant embedded in the form itself. "
            . "You help entrepreneurs in {$country} create a business plan for \"{$businessName}\" in the {$industry} industry.\n\n"
            . "CURRENT PROGRESS: Step {$currentStep} of 20 — {$stepLabel}\n"
            . "FILLED FIELDS: " . (count($filledFields) > 0 ? implode(', ', $filledFields) : 'none yet') . "\n"
            . "EMPTY FIELDS: " . (count($emptyFields) > 0 ? implode(', ', $emptyFields) : 'none') . "\n\n"
            . "AVAILABLE FIELDS (field_key: description):\n{$fieldsList}\n"
            . "RULES:\n"
            . "1. When the user asks to generate, write, create, improve, rewrite, or fix content for a section — respond with a field value.\n"
            . "2. When the user is just chatting or asking advice — respond conversationally.\n"
            . "3. Always write content specific to {$country} and the {$industry} industry.\n"
            . "4. Keep generated content concise (3-5 sentences for text fields, JSON array/object for structured fields).\n"
            . "5. If the user says something generic like 'write my mission statement', fill the mission_statement field.\n"
            . "6. Prioritize fields in the current step ({$stepLabel}) when the user is vague.\n\n"
            . "RESPONSE FORMAT (output ONLY valid JSON, no markdown, no backticks):\n"
            . "- For generating content: {\"type\":\"field\",\"field\":\"field_key\",\"content\":\"the generated content\"}\n"
            . "- For chatting: {\"type\":\"chat\",\"content\":\"your helpful response\"}\n"
            . "- If the user's request is ambiguous, ask for clarification via the chat type.";

        $userMessage = "User says: {$message}";

        $attempts = 0;
        $maxAttempts = 3;
        $lastException = null;
        while ($attempts < $maxAttempts) {
            try {
                $raw = $this->ai->smartChatWithPrompt($systemPrompt, $userMessage);
                $parsed = json_decode($raw, true);
                if (is_array($parsed) && isset($parsed['type'])) {
                    return $parsed;
                }
                return ['type' => 'chat', 'content' => $raw];
            } catch (\Exception $e) {
                $lastException = $e;
                $attempts++;
                if ($attempts < $maxAttempts) {
                    usleep($attempts * 1000000);
                }
            }
        }
        \Illuminate\Support\Facades\Log::warning('AI chat failed after retries', [
            'message' => $message,
            'error' => $lastException?->getMessage(),
        ]);
        return ['type' => 'chat', 'content' => 'Sorry, I had trouble processing that. Could you try again?'];
    }

    private function getPrompts(): array
    {
        return [
            'tagline' => 'A short, memorable tagline or slogan',
            'mission_statement' => 'A mission statement',
            'vision_statement' => 'A vision statement',
            'core_values' => 'Core values as a JSON array of strings',
            'business_objectives' => 'Business objectives',
            'company_history' => 'Company history and background story',
            'long_term_goals' => 'Long-term business goals (5-10 years)',
            'background' => 'Business background and founding story',
            'success_factors' => 'Key success factors',
            'problem_statement' => 'A problem statement describing the customer pain point',
            'existing_alternatives' => 'Existing alternatives customers currently use',
            'why_existing_fail' => 'Why existing alternatives fail to solve the problem',
            'solution_description' => 'A solution description',
            'product_description' => 'A product or service description',
            'delivery_method' => 'Delivery method description',
            'product_lifecycle' => 'Product lifecycle analysis',
            'future_improvements' => 'Future improvements and roadmap',
            'product_features' => 'Key features as a JSON array of strings',
            'production_process' => 'Production process description',
            'pricing_strategy' => 'A pricing strategy description',
            'revenue_streams' => 'Revenue streams',
            'cost_structure' => 'Cost structure analysis',
            'customer_relationships' => 'Customer relationship strategy',
            'channels' => 'Sales and distribution channels',
            'key_activities' => 'Key business activities',
            'key_resources' => 'Key business resources',
            'key_partners' => 'Key business partners',
            'target_market' => 'Target market description',
            'industry_trends' => 'Industry trends analysis',
            'regulations' => 'Regulatory and compliance requirements',
            'technology_changes' => 'Technology changes affecting the industry',
            'surveys_data' => 'Survey data and findings summary',
            'interviews_data' => 'Interview insights summary',
            'competitor_pricing_data' => 'Competitor pricing analysis',
            'customer_feedback_information' => 'Customer feedback summary',
            'swot_from_research' => 'SWOT analysis based on research',
            'competitive_analysis' => 'Competitive analysis summary',
            'swot_strengths' => 'SWOT strengths',
            'swot_weaknesses' => 'SWOT weaknesses',
            'swot_opportunities' => 'SWOT opportunities',
            'swot_threats' => 'SWOT threats',
            'unique_selling_points' => 'Unique selling points as a JSON array',
            'competitive_advantage' => 'Competitive advantage description',
            'branding_approach' => 'Branding approach and strategy',
            'sales_funnel' => 'Sales funnel description',
            'customer_retention' => 'Customer retention strategy',
            'sales_process' => 'Sales process description',
            'sales_targets' => 'Sales targets',
            'crm_process' => 'CRM process description',
            'daily_operations' => 'Daily operations description',
            'facilities' => 'Facilities and location requirements',
            'technology_stack' => 'Technology stack and tools',
            'quality_control' => 'Quality control processes',
            'operational_workflow' => 'Operational workflow description',
            'hiring_plan' => 'Hiring plan',
            'recruitment_strategy' => 'Recruitment strategy',
            'employee_benefits' => 'Employee benefits and perks',
            'training_plan' => 'Training and development plan',
            'performance_management' => 'Performance management approach',
            'risks' => 'Key risks as a JSON array',
            'mitigation_strategies' => 'Mitigation strategies as a JSON object',
            'timeline' => 'Implementation timeline as a JSON array',
            'milestones' => 'Key milestones as a JSON array',
            'financial_projections' => 'Financial projections summary',
            'break_even_analysis' => 'Break-even analysis',
            'exit_strategy_details' => 'Exit strategy details',
        ];
    }

    private function generateFallbackContent(string $field, array $context): string
    {
        $industry = $context['industry'] ?? 'business';
        $businessName = $context['business_name'] ?? 'our company';
        $country = $context['country'] ?? 'Zambia';

        $templates = [
            'mission_statement' => "To provide high-quality {$industry} products and services that meet the needs of our customers while contributing to the economic development of {$country}.",
            'vision_statement' => "To become the leading {$industry} provider in {$country}, known for excellence, innovation, and customer satisfaction.",
            'problem_statement' => "Many customers in the {$industry} sector face challenges with accessibility, affordability, and quality of products/services.",
            'solution_description' => "{$businessName} addresses these challenges by offering accessible, affordable, and high-quality {$industry} solutions.",
            'competitive_advantage' => "Our competitive advantages include deep understanding of local market needs, competitive pricing, strong customer service, and an innovative approach.",
            'product_description' => "We offer a comprehensive range of {$industry} products and services designed to meet diverse customer needs.",
            'pricing_strategy' => "Our pricing strategy is value-based, ensuring affordability while maintaining quality with flexible payment options.",
            'target_market' => "Our target market includes individuals and businesses in {$country} seeking reliable {$industry} solutions.",
            'competitive_analysis' => "The {$industry} sector has several established players, but there is room for differentiation through superior service and innovation.",
            'branding_approach' => "Our brand emphasizes trust, quality, and local expertise, positioning us as the reliable choice for {$industry} needs.",
            'customer_retention' => "We implement loyalty programs, regular engagement, quality assurance, and responsive service to ensure high retention rates.",
            'risks' => '["Market competition","Economic fluctuations","Supply chain disruptions","Regulatory changes","Technology changes"]',
            'mitigation_strategies' => '{"competition":"Diversify revenue streams","supply_chain":"Build strong supplier relationships","economic":"Maintain cash reserves","regulatory":"Stay informed on regulatory changes","technology":"Continuous innovation"}',
        ];

        return $templates[$field] ?? "Content for {$field} will be generated based on your business context.";
    }
}

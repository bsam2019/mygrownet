<?php

namespace App\Domain\GrowBuilder\Contracts;

/**
 * AiGeneratorEngineInterface — Strategy Pattern contract for AI site/content generation.
 *
 * Decouples GrowBuilder from any specific AI provider (Gemini, OpenAI, Claude, DeepSeek, etc.).
 * The active provider is resolved from config('services.ai.provider') at runtime.
 *
 * To add a new provider: implement this interface and bind it in GrowBuilderServiceProvider.
 */
interface AiGeneratorEngineInterface
{
    /**
     * Generate a full multi-page site layout JSON from a business description and Business Profile data.
     *
     * @param  string $prompt          The owner's natural-language business description
     * @param  array  $businessProfile Structured Business Profile fields (name, industry, services, location, etc.)
     * @param  string $templateSlug    The template/design style the owner selected
     * @param  array  $options         Additional options: language, tone, industry blueprint, etc.
     * @return array                   Multi-page layout JSON array compatible with site pages JSON schema
     */
    public function generateSiteLayout(
        string $prompt,
        array $businessProfile,
        string $templateSlug,
        array $options = []
    ): array;

    /**
     * Rewrite or improve a specific section's content using AI.
     *
     * @param  string $sectionType     Section component type (e.g. 'hero', 'services', 'about', 'contact')
     * @param  array  $currentContent  Existing section content fields
     * @param  array  $businessProfile Business Profile context to ground the rewrite
     * @param  string $instruction     Owner's instruction (e.g. 'make it more professional', 'add our PACRA number')
     * @return array                   Updated section content fields
     */
    public function reWriteSectionContent(
        string $sectionType,
        array $currentContent,
        array $businessProfile,
        string $instruction
    ): array;

    /**
     * Suggest three industry-appropriate template styles based on the business description.
     *
     * @param  string $businessDescription Natural-language description of the business
     * @param  string $industry            Detected or selected industry slug (e.g. 'pharmacy', 'restaurant')
     * @return array                       Array of up to 3 template recommendations: [{slug, name, reasoning}]
     */
    public function suggestTemplates(string $businessDescription, string $industry): array;

    /**
     * Generate a monthly site performance digest and improvement suggestions.
     *
     * @param  array $analyticsData    Aggregated analytics: visitors, enquiries, orders, top referrers
     * @param  array $businessProfile  Business Profile for context
     * @return array                   Digest payload: {summary_text, suggestions: [{text, priority}]}
     */
    public function generateRetentionDigest(array $analyticsData, array $businessProfile): array;

    /**
     * Translate site section content to a target language.
     *
     * @param  array  $sectionContent  Section content fields to translate
     * @param  string $targetLanguage  ISO language code ('bem' = Bemba, 'nya' = Nyanja, 'fr', 'pt', etc.)
     * @return array                   Translated section content fields
     */
    public function translateSectionContent(array $sectionContent, string $targetLanguage): array;

    /**
     * Return the human-readable name of this AI provider implementation.
     */
    public function getProviderName(): string;

    /**
     * Return whether this provider is available (credentials configured, quota available, etc.).
     */
    public function isAvailable(): bool;
}

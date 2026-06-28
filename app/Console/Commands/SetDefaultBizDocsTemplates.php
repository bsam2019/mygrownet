<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentTemplateModel;
use Illuminate\Console\Command;

class SetDefaultBizDocsTemplates extends Command
{
    protected $signature = 'bizdocs:set-default-templates';
    protected $description = 'Set default BizDocs templates for all companies that don\'t have templates configured';

    public function handle(): int
    {
        $this->info('Setting default BizDocs templates for companies...');

        // Get default templates for each document type
        $defaultTemplates = DocumentTemplateModel::where('is_default', true)
            ->whereIn('document_type', ['invoice', 'quotation', 'receipt'])
            ->get()
            ->keyBy('document_type');

        if ($defaultTemplates->isEmpty()) {
            $this->error('No default templates found. Please run the BizDocsTemplateSeeder first.');
            return self::FAILURE;
        }

        $this->info('Found default templates:');
        foreach ($defaultTemplates as $docType => $template) {
            $this->line("  - {$docType}: {$template->name} (ID: {$template->id}, Layout: {$template->layout_file})");
        }

        // Get all companies
        $companies = CompanyModel::all();
        $updated = 0;
        $skipped = 0;

        foreach ($companies as $company) {
            $settings = $company->settings ?? [];
            $hasTemplates = isset($settings['bizdocs_template_preferences']) 
                && !empty($settings['bizdocs_template_preferences']);

            if ($hasTemplates) {
                $this->line("Skipping {$company->name} - already has templates configured");
                $skipped++;
                continue;
            }

            $settings['bizdocs_template_preferences'] = [];

            foreach (['invoice', 'quotation', 'receipt'] as $docType) {
                if (isset($defaultTemplates[$docType])) {
                    $settings['bizdocs_template_preferences'][$docType] = $defaultTemplates[$docType]->id;
                }
            }

            $company->update(['settings' => $settings]);
            $this->info("✓ Updated {$company->name}");
            $updated++;
        }

        $this->newLine();
        $this->info("Summary:");
        $this->line("  - Updated: {$updated} companies");
        $this->line("  - Skipped: {$skipped} companies");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetImageProcessingPhase extends Command
{
    protected $signature = 'marketplace:image-phase {phase : The phase to set (mvp, phase2, phase3, phase4)}';
    protected $description = 'Set the image processing phase for marketplace products';

    public function handle(): int
    {
        $phase = $this->argument('phase');
        $validPhases = ['mvp', 'phase2', 'phase3', 'phase4'];

        if (!in_array($phase, $validPhases)) {
            $this->error("Invalid phase. Must be one of: " . implode(', ', $validPhases));
            return self::FAILURE;
        }

        // Update .env file
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        if (str_contains($envContent, 'MARKETPLACE_IMAGE_PHASE=')) {
            $envContent = preg_replace(
                '/MARKETPLACE_IMAGE_PHASE=.*/',
                "MARKETPLACE_IMAGE_PHASE={$phase}",
                $envContent
            );
        } else {
            $envContent .= "\nMARKETPLACE_IMAGE_PHASE={$phase}\n";
        }

        file_put_contents($envPath, $envContent);

        $this->info("✅ Image processing phase set to: {$phase}");
        $this->newLine();

        // Show phase details
        $this->displayPhaseInfo($phase);

        return self::SUCCESS;
    }

    private function displayPhaseInfo(string $phase): void
    {
        $info = match ($phase) {
            'mvp' => [
                'name' => 'MVP - Basic Upload',
                'features' => [
                    '✓ Raw image upload',
                    '✓ Seller-managed image editing',
                    '✓ Image guidelines provided',
                    '✗ No automatic processing',
                ],
            ],
            'phase2' => [
                'name' => 'Phase 2 - Basic Optimization',
                'features' => [
                    '✓ Automatic resize (1200px, 800px, 300px)',
                    '✓ Image compression (85% quality)',
                    '✓ Multiple size generation',
                    '✓ Optimized storage',
                ],
            ],
            'phase3' => [
                'name' => 'Phase 3 - Background Removal',
                'features' => [
                    '✓ All Phase 2 features',
                    '✓ Background removal for featured products',
                    '✓ Trust level-based processing',
                    '✓ Optional API integration (remove.bg)',
                ],
            ],
            'phase4' => [
                'name' => 'Phase 4 - Advanced Processing',
                'features' => [
                    '✓ All Phase 3 features',
                    '✓ Watermark support',
                    '✓ Image enhancement',
                    '✓ Premium seller features',
                    '✓ Full editing suite',
                ],
            ],
        };

        $this->info("📦 {$info['name']}");
        $this->newLine();
        $this->info("Features:");
        foreach ($info['features'] as $feature) {
            $this->line("  {$feature}");
        }
        $this->newLine();

        if ($phase === 'phase3' || $phase === 'phase4') {
            $this->warn("⚠️  Additional configuration may be required:");
            $this->line("  - Set REMOVEBG_API_KEY in .env for API-based background removal");
            $this->line("  - Set MARKETPLACE_BG_REMOVAL=true to enable");
        }

        if ($phase === 'phase4') {
            $this->line("  - Set MARKETPLACE_WATERMARK=true to enable watermarks");
            $this->line("  - Set MARKETPLACE_IMAGE_ENHANCE=true for auto-enhancement");
        }
    }
}

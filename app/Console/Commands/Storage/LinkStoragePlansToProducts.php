<?php

namespace App\Console\Commands\Storage;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LinkStoragePlansToProducts extends Command
{
    protected $signature = 'storage:link-products';
    protected $description = 'Link storage plans to their corresponding products';

    public function handle(): int
    {
        $this->info('Linking storage plans to products...');
        
        $mappings = [
            'starter' => 'growbackup-starter',
            'basic' => 'growbackup-basic',
            'growth' => 'growbackup-growth',
            'pro' => 'growbackup-pro',
        ];
        
        foreach ($mappings as $planSlug => $productSlug) {
            $plan = DB::table('storage_plans')->where('slug', $planSlug)->first();
            $product = DB::table('products')->where('slug', $productSlug)->first();
            
            if ($plan && $product) {
                DB::table('storage_plans')
                    ->where('id', $plan->id)
                    ->update(['product_id' => $product->id]);
                    
                $this->info("✓ Linked {$planSlug} plan to {$productSlug} product");
            } else {
                $this->warn("✗ Could not link {$planSlug} - plan or product not found");
            }
        }
        
        $this->newLine();
        $this->info('Done!');
        
        return 0;
    }
}

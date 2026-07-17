<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sa_recipes')) {
            Schema::create('sa_recipes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
                $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
                $table->string('name');
                $table->decimal('yield_quantity', 12, 2)->default(1)->comment('Portions / servings this recipe produces');
                $table->string('yield_uom', 20)->default('portions');
                $table->string('difficulty', 20)->default('easy');
                $table->integer('prep_time_minutes')->nullable();
                $table->integer('cook_time_minutes')->nullable();
                $table->text('instructions')->nullable();
                $table->json('allergens')->nullable();
                $table->json('dietary_labels')->nullable();
                $table->string('status', 20)->default('active');
                $table->timestamps();
                $table->index(['sa_company_id', 'sa_item_id']);
            });
        }

        if (!Schema::hasTable('sa_recipe_ingredients')) {
            Schema::create('sa_recipe_ingredients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_recipe_id')->constrained('sa_recipes')->cascadeOnDelete();
                $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
                $table->decimal('quantity', 12, 2);
                $table->string('uom', 20)->default('each');
                $table->decimal('waste_factor', 5, 2)->default(0)->comment('Trim/prep loss %');
                $table->boolean('is_substitutable')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sa_menu_costings')) {
            Schema::create('sa_menu_costings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
                $table->foreignId('sa_recipe_id')->constrained('sa_recipes')->cascadeOnDelete();
                $table->decimal('total_ingredient_cost', 12, 2);
                $table->decimal('cost_per_portion', 12, 2);
                $table->decimal('suggested_price', 12, 2)->nullable();
                $table->decimal('target_food_cost_pct', 5, 2)->default(30);
                $table->date('costed_at');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sa_wastage_records')) {
            Schema::create('sa_wastage_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
                $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
                $table->decimal('quantity', 12, 2);
                $table->decimal('unit_cost', 12, 2)->default(0);
                $table->string('reason', 50)->comment('spoilage, trim, overproduction, expiry, other');
                $table->string('reference_type', 30)->nullable()->comment('recipe, work_order, purchase');
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->text('notes')->nullable();
                $table->date('occurred_at');
                $table->timestamps();
                $table->index(['sa_company_id', 'reason']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_wastage_records');
        Schema::dropIfExists('sa_menu_costings');
        Schema::dropIfExists('sa_recipe_ingredients');
        Schema::dropIfExists('sa_recipes');
    }
};

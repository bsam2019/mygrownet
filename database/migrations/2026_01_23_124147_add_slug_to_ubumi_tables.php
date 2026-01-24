<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add slug column without unique constraint first
        Schema::table('ubumi_families', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        Schema::table('ubumi_persons', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        // Populate slugs for existing records
        $this->populateFamilySlugs();
        $this->populatePersonSlugs();

        // Now make slug non-nullable and add unique constraint
        Schema::table('ubumi_families', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
            $table->index('slug');
        });

        Schema::table('ubumi_persons', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->index(['family_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ubumi_families', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        Schema::table('ubumi_persons', function (Blueprint $table) {
            $table->dropIndex(['family_id', 'slug']);
            $table->dropColumn('slug');
        });
    }

    private function populateFamilySlugs(): void
    {
        $families = DB::table('ubumi_families')->get();
        $usedSlugs = [];

        foreach ($families as $family) {
            $baseSlug = Str::slug($family->name);
            $slug = $baseSlug;
            $counter = 1;

            // Ensure uniqueness
            while (in_array($slug, $usedSlugs)) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $usedSlugs[] = $slug;

            DB::table('ubumi_families')
                ->where('id', $family->id)
                ->update(['slug' => $slug]);
        }
    }

    private function populatePersonSlugs(): void
    {
        $persons = DB::table('ubumi_persons')->get();
        $usedSlugs = []; // Track slugs per family

        foreach ($persons as $person) {
            $baseSlug = Str::slug($person->name);
            $slug = $baseSlug;
            $counter = 1;

            // Ensure uniqueness within family
            $familyKey = $person->family_id;
            if (!isset($usedSlugs[$familyKey])) {
                $usedSlugs[$familyKey] = [];
            }

            while (in_array($slug, $usedSlugs[$familyKey])) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $usedSlugs[$familyKey][] = $slug;

            DB::table('ubumi_persons')
                ->where('id', $person->id)
                ->update(['slug' => $slug]);
        }
    }
};

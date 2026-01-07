<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create wedding_templates table if it doesn't exist
        if (!Schema::hasTable('wedding_templates')) {
            Schema::create('wedding_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('slug', 100)->unique();
                $table->text('description')->nullable();
                $table->string('preview_image')->nullable();
                $table->json('settings'); // colors, fonts, layout config
                $table->boolean('is_active')->default(true);
                $table->boolean('is_premium')->default(false);
                $table->timestamps();
            });
        }

        // Add template_id and other fields to wedding_events (only if they don't exist)
        Schema::table('wedding_events', function (Blueprint $table) {
            if (!Schema::hasColumn('wedding_events', 'template_id')) {
                $table->foreignId('template_id')->nullable()->after('id')->constrained('wedding_templates')->nullOnDelete();
            }
            if (!Schema::hasColumn('wedding_events', 'custom_settings')) {
                $table->json('custom_settings')->nullable()->after('preferences');
            }
            if (!Schema::hasColumn('wedding_events', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('status');
            }
            if (!Schema::hasColumn('wedding_events', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('is_published');
            }
            if (!Schema::hasColumn('wedding_events', 'groom_name')) {
                $table->string('groom_name')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('wedding_events', 'bride_name')) {
                $table->string('bride_name')->nullable()->after('groom_name');
            }
            if (!Schema::hasColumn('wedding_events', 'hero_image')) {
                $table->string('hero_image')->nullable()->after('custom_settings');
            }
            if (!Schema::hasColumn('wedding_events', 'story_image')) {
                $table->string('story_image')->nullable()->after('hero_image');
            }
            if (!Schema::hasColumn('wedding_events', 'how_we_met')) {
                $table->text('how_we_met')->nullable()->after('story_image');
            }
            if (!Schema::hasColumn('wedding_events', 'proposal_story')) {
                $table->text('proposal_story')->nullable()->after('how_we_met');
            }
            if (!Schema::hasColumn('wedding_events', 'ceremony_time')) {
                $table->string('ceremony_time')->nullable()->after('venue_location');
            }
            if (!Schema::hasColumn('wedding_events', 'reception_time')) {
                $table->string('reception_time')->nullable()->after('ceremony_time');
            }
            if (!Schema::hasColumn('wedding_events', 'reception_venue')) {
                $table->string('reception_venue')->nullable()->after('reception_time');
            }
            if (!Schema::hasColumn('wedding_events', 'reception_address')) {
                $table->string('reception_address')->nullable()->after('reception_venue');
            }
            if (!Schema::hasColumn('wedding_events', 'dress_code')) {
                $table->string('dress_code')->nullable()->after('reception_address');
            }
            if (!Schema::hasColumn('wedding_events', 'rsvp_deadline')) {
                $table->date('rsvp_deadline')->nullable()->after('dress_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wedding_events', function (Blueprint $table) {
            $columns = [
                'template_id', 'custom_settings', 'is_published', 'published_at',
                'groom_name', 'bride_name', 'hero_image', 'story_image', 'how_we_met',
                'proposal_story', 'ceremony_time', 'reception_time', 'reception_venue',
                'reception_address', 'dress_code', 'rsvp_deadline'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('wedding_events', $column)) {
                    if ($column === 'template_id') {
                        $table->dropForeign(['template_id']);
                    }
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('wedding_templates');
    }
};

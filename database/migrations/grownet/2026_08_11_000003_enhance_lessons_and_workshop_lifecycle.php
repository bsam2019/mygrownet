<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Extend Education Curricula / Lessons Table
        if (Schema::hasTable('education_curricula')) {
            Schema::table('education_curricula', function (Blueprint $table) {
                if (!Schema::hasColumn('education_curricula', 'learning_objectives')) {
                    $table->json('learning_objectives')->nullable()->after('description');
                }
                if (!Schema::hasColumn('education_curricula', 'transcript')) {
                    $table->longText('transcript')->nullable()->after('pdf_url');
                }
                if (!Schema::hasColumn('education_curricula', 'simplified_notes')) {
                    $table->longText('simplified_notes')->nullable()->after('transcript');
                }
                if (!Schema::hasColumn('education_curricula', 'key_points')) {
                    $table->json('key_points')->nullable()->after('simplified_notes');
                }
                if (!Schema::hasColumn('education_curricula', 'local_languages')) {
                    $table->json('local_languages')->nullable()->after('key_points'); // Audio/video URLs per language
                }
                if (!Schema::hasColumn('education_curricula', 'practical_activity_prompt')) {
                    $table->text('practical_activity_prompt')->nullable()->after('local_languages');
                }
                if (!Schema::hasColumn('education_curricula', 'resource_files')) {
                    $table->json('resource_files')->nullable()->after('practical_activity_prompt');
                }
                if (!Schema::hasColumn('education_curricula', 'is_low_literacy_friendly')) {
                    $table->boolean('is_low_literacy_friendly')->default(true)->after('is_required');
                }
            });
        }

        // 2. Lesson Progress States Table (Learn / Practise / Prove Engine)
        if (!Schema::hasTable('lesson_progress_states')) {
            Schema::create('lesson_progress_states', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('lesson_id');
                $table->enum('status', ['not_started', 'learning_completed', 'practice_completed', 'proven_completed'])->default('not_started');
                $table->string('active_language')->default('English');
                $table->timestamp('learn_completed_at')->nullable();
                $table->text('practice_submission')->nullable();
                $table->timestamp('practice_completed_at')->nullable();
                $table->foreignId('assessment_attempt_id')->nullable();
                $table->timestamp('proven_completed_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'lesson_id']);
                $table->index(['user_id', 'status']);
            });
        }

        // 3. Extend Workshops Table for Event Lifecycle & Accreditation
        if (Schema::hasTable('workshops')) {
            Schema::table('workshops', function (Blueprint $table) {
                if (!Schema::hasColumn('workshops', 'status')) {
                    $table->enum('status', ['draft', 'published', 'registration_open', 'in_progress', 'completed', 'cancelled'])->default('published')->after('level');
                }
                if (!Schema::hasColumn('workshops', 'delivery_mode')) {
                    $table->enum('delivery_mode', ['physical', 'online', 'hybrid'])->default('online')->after('status');
                }
                if (!Schema::hasColumn('workshops', 'location_address')) {
                    $table->string('location_address')->nullable()->after('delivery_mode');
                }
                if (!Schema::hasColumn('workshops', 'languages')) {
                    $table->json('languages')->nullable()->after('location_address');
                }
                if (!Schema::hasColumn('workshops', 'capacity')) {
                    $table->integer('capacity')->default(50)->after('languages');
                }
                if (!Schema::hasColumn('workshops', 'institution_id')) {
                    $table->unsignedBigInteger('institution_id')->nullable()->after('capacity');
                }
                if (!Schema::hasColumn('workshops', 'is_specialist_skills')) {
                    $table->boolean('is_specialist_skills')->default(false)->after('institution_id');
                }
            });
        }

        // 4. Extend Workshop Registrations for Full Lifecycle State
        if (!Schema::hasTable('workshop_registrations')) {
            Schema::create('workshop_registrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workshop_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->enum('preferred_delivery', ['physical', 'online'])->default('online');
                $table->string('preferred_language')->default('English');
                $table->enum('lifecycle_state', ['registered', 'confirmed', 'attended', 'participated', 'assessed', 'completed'])->default('registered');
                $table->timestamp('attended_at')->nullable();
                $table->timestamp('participated_at')->nullable();
                $table->timestamp('assessed_at')->nullable();
                $table->boolean('practical_passed')->default(false);
                $table->foreignId('facilitator_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->text('facilitator_notes')->nullable();
                $table->foreignId('certificate_id')->nullable();
                $table->integer('pb_points_awarded')->default(150);
                $table->integer('mp_points_awarded')->default(50);
                $table->timestamps();

                $table->unique(['workshop_id', 'user_id']);
                $table->index(['user_id', 'lifecycle_state']);
            });
        }

        // 5. Skills Catalog & Demand Tables
        if (!Schema::hasTable('skills')) {
            Schema::create('skills', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('category')->default('Entrepreneurship');
                $table->text('description')->nullable();
                $table->integer('demand_count')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['category', 'is_active']);
            });
        }

        if (!Schema::hasTable('member_skill_interests')) {
            Schema::create('member_skill_interests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('skill_id')->constrained()->onDelete('cascade');
                $table->enum('status', ['interested', 'registered', 'in_training', 'completed'])->default('interested');
                $table->text('custom_request_notes')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'skill_id']);
                $table->index(['skill_id', 'status']);
            });
        }

        if (!Schema::hasTable('training_opportunities')) {
            Schema::create('training_opportunities', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->foreignId('skill_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('expert_id')->nullable();
                $table->enum('status', ['draft', 'pre_registration', 'scheduled', 'in_progress', 'completed', 'cancelled'])->default('pre_registration');
                $table->integer('min_demand_threshold')->default(30);
                $table->integer('current_registrations')->default(0);
                $table->integer('max_capacity')->default(50);
                $table->timestamp('starts_at')->nullable();
                $table->string('location')->default('Online');
                $table->decimal('fee', 10, 2)->default(0.00);
                $table->timestamps();

                $table->index(['skill_id', 'status']);
            });
        }

        // 6. Expert Network Registry Table
        if (!Schema::hasTable('experts')) {
            Schema::create('experts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('name');
                $table->string('title');
                $table->text('bio')->nullable();
                $table->json('qualifications')->nullable();
                $table->json('areas_of_expertise')->nullable();
                $table->string('institutional_affiliation')->nullable();
                $table->decimal('rating', 3, 2)->default(5.00);
                $table->boolean('is_verified')->default(true);
                $table->timestamps();
            });
        }

        // 7. Institutions & Accreditation Table
        if (!Schema::hasTable('institutions')) {
            Schema::create('institutions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->string('logo_url')->nullable();
                $table->string('website')->nullable();
                $table->text('accreditation_details')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 8. Business Funding Applications Table (Level 5 & Level 6 Leaders)
        if (!Schema::hasTable('business_funding_applications')) {
            Schema::create('business_funding_applications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->integer('level_achieved')->default(5); // 5 or 6
                $table->string('business_name');
                $table->unsignedBigInteger('business_plan_id')->nullable();
                $table->decimal('requested_amount', 12, 2)->default(0.00);
                $table->text('funding_purpose');
                $table->json('financial_summary')->nullable();
                $table->enum('status', ['submitted', 'under_review', 'assessed', 'approved', 'rejected'])->default('submitted');
                $table->foreignId('evaluated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->text('evaluation_notes')->nullable();
                $table->timestamp('evaluated_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('business_funding_applications');
        Schema::dropIfExists('institutions');
        Schema::dropIfExists('experts');
        Schema::dropIfExists('training_opportunities');
        Schema::dropIfExists('member_skill_interests');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('workshop_registrations');
        Schema::dropIfExists('lesson_progress_states');
    }
};

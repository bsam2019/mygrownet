<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Team members for multi-user access
        Schema::create('growfinance_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->default('member'); // owner, admin, accountant, viewer
            $table->json('permissions')->nullable(); // granular permissions
            $table->string('status')->default('pending'); // pending, active, suspended
            $table->string('invitation_token')->nullable();
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'user_id']);
            $table->index(['business_id', 'status']);
            $table->index('invitation_token');
        });

        // Invoice templates
        Schema::create('growfinance_invoice_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('layout')->default('standard'); // standard, modern, minimal, professional
            $table->json('colors')->nullable(); // primary, secondary, accent colors
            $table->json('fonts')->nullable(); // heading, body fonts
            $table->string('logo_position')->default('left'); // left, center, right
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_watermark')->default(false);
            $table->text('header_text')->nullable();
            $table->text('footer_text')->nullable();
            $table->text('terms_text')->nullable();
            $table->json('custom_fields')->nullable(); // additional fields to show
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['business_id', 'is_active']);
        });

        // API tokens for API access
        Schema::create('growfinance_api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->json('abilities')->nullable(); // read, write, delete permissions
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['business_id', 'is_active']);
            $table->index('token');
        });

        // White-label settings
        Schema::create('growfinance_white_label_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->onDelete('cascade');
            $table->string('company_name')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('primary_color')->default('#2563eb');
            $table->string('secondary_color')->default('#1e40af');
            $table->string('accent_color')->default('#059669');
            $table->string('custom_domain')->nullable();
            $table->boolean('hide_powered_by')->default(false);
            $table->text('custom_css')->nullable();
            $table->json('email_branding')->nullable(); // email templates branding
            $table->timestamps();

            $table->unique('business_id');
            $table->index('custom_domain');
        });

        // Add template_id to invoices
        Schema::table('growfinance_invoices', function (Blueprint $table) {
            $table->foreignId('template_id')->nullable()->after('business_id');
        });
    }

    public function down(): void
    {
        Schema::table('growfinance_invoices', function (Blueprint $table) {
            $table->dropColumn('template_id');
        });

        Schema::dropIfExists('growfinance_white_label_settings');
        Schema::dropIfExists('growfinance_api_tokens');
        Schema::dropIfExists('growfinance_invoice_templates');
        Schema::dropIfExists('growfinance_team_members');
    }
};

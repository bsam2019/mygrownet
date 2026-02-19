<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add email configuration to companies
        Schema::table('cms_companies', function (Blueprint $table) {
            $table->enum('email_provider', ['platform', 'custom'])->default('platform')->after('settings');
            $table->string('email_from_address')->nullable()->after('email_provider');
            $table->string('email_from_name')->nullable()->after('email_from_address');
            $table->string('email_reply_to')->nullable()->after('email_from_name');
            $table->string('smtp_host')->nullable()->after('email_reply_to');
            $table->integer('smtp_port')->nullable()->after('smtp_host');
            $table->string('smtp_username')->nullable()->after('smtp_port');
            $table->text('smtp_password')->nullable()->after('smtp_username'); // Encrypted
            $table->enum('smtp_encryption', ['tls', 'ssl', 'none'])->default('tls')->after('smtp_password');
        });

        // Email logs
        if (!Schema::hasTable('cms_email_logs')) {
            Schema::create('cms_email_logs', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->enum('email_type', ['invoice', 'payment', 'reminder', 'overdue', 'receipt', 'quotation', 'other']);
                        $table->string('recipient_email');
                        $table->string('recipient_name')->nullable();
                        $table->string('subject', 500);
                        $table->enum('reference_type', ['invoice', 'payment', 'quotation', 'job'])->nullable();
                        $table->unsignedBigInteger('reference_id')->nullable();
                        $table->enum('status', ['queued', 'sent', 'failed', 'bounced'])->default('queued');
                        $table->enum('provider', ['platform', 'custom']);
                        $table->timestamp('sent_at')->nullable();
                        $table->timestamp('opened_at')->nullable();
                        $table->timestamp('clicked_at')->nullable();
                        $table->timestamp('bounced_at')->nullable();
                        $table->text('error_message')->nullable();
                        $table->json('metadata')->nullable();
                        $table->timestamps();
            
                        $table->index(['company_id', 'email_type']);
                        $table->index(['status', 'created_at']);
                        $table->index(['reference_type', 'reference_id']);
                    });
        }

        // Email templates
        if (!Schema::hasTable('cms_email_templates')) {
            Schema::create('cms_email_templates', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->enum('template_type', ['invoice_sent', 'payment_received', 'payment_reminder', 'overdue_notice', 'receipt', 'quotation_sent']);
                        $table->string('subject', 500);
                        $table->text('body_html');
                        $table->text('body_text')->nullable();
                        $table->json('variables')->nullable();
                        $table->boolean('is_active')->default(true);
                        $table->timestamps();
            
                        $table->unique(['company_id', 'template_type']);
                        $table->index(['company_id', 'is_active']);
                    });
        }

        // Unsubscribes
        if (!Schema::hasTable('cms_email_unsubscribes')) {
            Schema::create('cms_email_unsubscribes', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('email_address');
                        $table->enum('unsubscribe_type', ['all', 'marketing', 'reminders'])->default('all');
                        $table->text('reason')->nullable();
                        $table->timestamp('unsubscribed_at')->useCurrent();
            
                        $table->unique(['company_id', 'email_address']);
                        $table->index('company_id');
                    });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_email_unsubscribes');
        Schema::dropIfExists('cms_email_templates');
        Schema::dropIfExists('cms_email_logs');

        Schema::table('cms_companies', function (Blueprint $table) {
            $table->dropColumn([
                'email_provider',
                'email_from_address',
                'email_from_name',
                'email_reply_to',
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_password',
                'smtp_encryption',
            ]);
        });
    }
};

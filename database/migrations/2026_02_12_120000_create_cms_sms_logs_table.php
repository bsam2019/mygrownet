<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cms_sms_logs')) {
            Schema::create('cms_sms_logs', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('to', 20); // Phone number
                        $table->text('message');
                        $table->string('type', 50)->default('general'); // invoice, payment, reminder, job, general
                        $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
                        $table->string('message_id')->nullable(); // Provider message ID
                        $table->decimal('cost', 10, 4)->nullable(); // SMS cost
                        $table->text('error')->nullable();
                        $table->timestamp('sent_at')->nullable();
                        $table->timestamps();
            
                        $table->index(['company_id', 'created_at']);
                        $table->index(['company_id', 'status']);
                        $table->index(['company_id', 'type']);
                    });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_sms_logs');
    }
};

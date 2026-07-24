<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bizboost_ai_usage_logs', function (Blueprint $table) {
            // Add type column for categorizing AI usage (advisor_chat, content_generation, etc.)
            $table->string('type')->nullable()->after('user_id');
            
            // Add tokens_used as a convenience column (sum of input + output)
            $table->integer('tokens_used')->default(0)->after('output_tokens');
            
            // Add index for type queries
            $table->index(['business_id', 'type', 'created_at']);
        });

        // Update existing records to set type based on content_type
        \Illuminate\Support\Facades\DB::table('bizboost_ai_usage_logs')
            ->whereNull('type')
            ->update(['type' => \Illuminate\Support\Facades\DB::raw('content_type')]);
    }

    public function down(): void
    {
        Schema::table('bizboost_ai_usage_logs', function (Blueprint $table) {
            $table->dropIndex(['business_id', 'type', 'created_at']);
            $table->dropColumn(['type', 'tokens_used']);
        });
    }
};

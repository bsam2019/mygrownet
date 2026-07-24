<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_contract_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('cms_contracts')->cascadeOnDelete();
            $table->string('party'); // 'company' or 'customer'
            $table->string('signer_name')->nullable();
            $table->string('signer_email')->nullable();
            $table->text('signature_data'); // Base64-encoded signature image
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('signing_token')->nullable()->unique(); // for remote signing links
            $table->timestamp('signed_at');
            $table->timestamps();

            $table->index(['contract_id', 'party']);
        });

        if (!Schema::hasColumn('cms_contracts', 'signed_pdf_path')) {
            Schema::table('cms_contracts', function (Blueprint $table) {
                $table->string('signed_pdf_path')->nullable()->after('signed_at');
            });
        }

        if (!Schema::hasColumn('cms_contracts', 'signing_token')) {
            Schema::table('cms_contracts', function (Blueprint $table) {
                $table->string('signing_token')->nullable()->unique()->after('signed_pdf_path');
            });
        }
    }

    public function down(): void
    {
        Schema::table('cms_contracts', function (Blueprint $table) {
            $table->dropColumn(['signed_pdf_path', 'signing_token']);
        });

        Schema::dropIfExists('cms_contract_signatures');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quick_invoice_custom_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('base_template')->default('classic'); // Base template to extend
            
            // Design customizations
            $table->string('primary_color')->default('#2563eb');
            $table->string('secondary_color')->nullable();
            $table->string('accent_color')->nullable();
            $table->string('font_family')->default('Inter, sans-serif');
            $table->string('heading_font')->nullable();
            
            // Layout settings
            $table->string('header_style')->default('standard'); // standard, minimal, bold, split
            $table->string('layout_style')->default('single-column'); // single-column, two-column, sidebar
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_business_details')->default(true);
            $table->string('logo_position')->default('left'); // left, center, right
            $table->integer('logo_size')->default(80); // pixels
            
            // Styling options
            $table->integer('border_radius')->default(8); // pixels
            $table->string('border_style')->default('solid'); // solid, dashed, none
            $table->integer('spacing')->default(16); // pixels
            $table->string('table_style')->default('striped'); // striped, bordered, minimal
            
            // Advanced customizations (JSON)
            $table->json('custom_css')->nullable();
            $table->json('section_visibility')->nullable(); // Which sections to show/hide
            $table->json('field_labels')->nullable(); // Custom field labels
            
            // Metadata
            $table->boolean('is_public')->default(false); // Can other users see/use this template
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quick_invoice_custom_templates');
    }
};

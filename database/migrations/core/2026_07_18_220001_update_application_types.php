<?php

use App\Domain\Core\Models\Application;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Application::where('slug', 'grownet')->update(['type' => 'platform']);
        Application::where('slug', 'bms')->update(['type' => 'system']);
        Application::where('type', 'BUSINESS')->whereNotIn('slug', ['bms'])->update(['type' => 'business']);
        Application::where('type', 'CONSUMER')->update(['type' => 'consumer']);
    }

    public function down(): void
    {
        Application::where('slug', 'grownet')->update(['type' => 'NETWORK']);
        Application::where('slug', 'bms')->update(['type' => 'BUSINESS']);
        Application::where('type', 'business')->update(['type' => 'BUSINESS']);
        Application::where('type', 'consumer')->update(['type' => 'CONSUMER']);
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Migrate main users that have StockFlow company associations
        $mainUsers = DB::table('users')
            ->join('sa_company_users', 'users.id', '=', 'sa_company_users.user_id')
            ->select('users.id as main_id', 'users.name', 'users.email', 'users.password', 'users.remember_token')
            ->distinct()
            ->get();

        $idMap = [];
        foreach ($mainUsers as $user) {
            $saUserId = DB::table('sa_users')->insertGetId([
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->password,
                'remember_token' => $user->remember_token,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $idMap[$user->main_id] = $saUserId;
        }

        // Update sa_company_users to use new sa_users ids
        if (!empty($idMap)) {
            foreach ($idMap as $mainId => $saUserId) {
                DB::table('sa_company_users')
                    ->where('user_id', $mainId)
                    ->update(['user_id' => $saUserId]);
            }
        }

        // Drop old FK and add new FK
        Schema::table('sa_company_users', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('sa_users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Restore FK to users table
        Schema::table('sa_company_users', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::dropIfExists('sa_users');
    }
};

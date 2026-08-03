<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE growstream_videos MODIFY content_type "
            ."ENUM('movie','series','episode','short','comedy','skit','soap','drama','lesson','workshop','webinar') "
            ."NOT NULL DEFAULT 'lesson'"
        );
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE growstream_videos MODIFY content_type "
            ."ENUM('movie','series','episode','lesson','short','workshop','webinar') "
            ."NOT NULL DEFAULT 'lesson'"
        );
    }
};

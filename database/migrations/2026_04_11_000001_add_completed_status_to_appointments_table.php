<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE appointments MODIFY status ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending'"
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('appointments')
            ->where('status', 'completed')
            ->update(['status' => 'confirmed']);

        DB::statement(
            "ALTER TABLE appointments MODIFY status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending'"
        );
    }
};

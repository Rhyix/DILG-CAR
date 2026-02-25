<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement(
            "ALTER TABLE admins MODIFY COLUMN role ENUM('superadmin', 'admin', 'hr_division', 'viewer') NOT NULL DEFAULT 'viewer'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::table('admins')
            ->where('role', 'superadmin')
            ->update(['role' => 'admin']);
        DB::table('admins')
            ->where('role', 'hr_division')
            ->update(['role' => 'admin']);

        DB::statement(
            "ALTER TABLE admins MODIFY COLUMN role ENUM('admin', 'viewer') NOT NULL DEFAULT 'viewer'"
        );
    }
};

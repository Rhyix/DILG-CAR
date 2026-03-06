<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'middle_initial') && !Schema::hasColumn('users', 'middle_name')) {
            DB::statement('ALTER TABLE users CHANGE middle_initial middle_name VARCHAR(255) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'middle_name') && !Schema::hasColumn('users', 'middle_initial')) {
            DB::statement('ALTER TABLE users CHANGE middle_name middle_initial VARCHAR(255) NULL');
        }
    }
};

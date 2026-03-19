<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE job_vacancies MODIFY qualification_eligibility TEXT NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE job_vacancies MODIFY qualification_eligibility VARCHAR(255) NOT NULL");
    }
};


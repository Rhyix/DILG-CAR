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
        DB::statement('ALTER TABLE educational_backgrounds MODIFY elem_from VARCHAR(10) NULL');
        DB::statement('ALTER TABLE educational_backgrounds MODIFY elem_to VARCHAR(10) NULL');
        DB::statement('ALTER TABLE educational_backgrounds MODIFY jhs_from VARCHAR(10) NULL');
        DB::statement('ALTER TABLE educational_backgrounds MODIFY jhs_to VARCHAR(10) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE educational_backgrounds MODIFY elem_from VARCHAR(7) NULL');
        DB::statement('ALTER TABLE educational_backgrounds MODIFY elem_to VARCHAR(7) NULL');
        DB::statement('ALTER TABLE educational_backgrounds MODIFY jhs_from VARCHAR(7) NULL');
        DB::statement('ALTER TABLE educational_backgrounds MODIFY jhs_to VARCHAR(7) NULL');
    }
};

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
        DB::statement('ALTER TABLE educational_backgrounds MODIFY shs_from VARCHAR(10) NULL');
        DB::statement('ALTER TABLE educational_backgrounds MODIFY shs_to VARCHAR(10) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        DB::statement('ALTER TABLE educational_backgrounds MODIFY shs_from VARCHAR(7) NULL');
        DB::statement('ALTER TABLE educational_backgrounds MODIFY shs_to VARCHAR(7) NULL');
    }
};

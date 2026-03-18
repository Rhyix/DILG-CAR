<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('exam_attendance_status', 32)->nullable()->after('read_at');
            $table->text('exam_attendance_remark')->nullable()->after('exam_attendance_status');
            $table->timestamp('exam_attendance_responded_at')->nullable()->after('exam_attendance_remark');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'exam_attendance_status',
                'exam_attendance_remark',
                'exam_attendance_responded_at',
            ]);
        });
    }
};

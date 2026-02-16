<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Applications extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'updated_by_admin_id',
        'vacancy_id',
        'status',
        'result',
        'answers',
        'scores',
        'is_valid',
        'deadline_date',
        'deadline_time',
        'file_original_name',
        'file_stored_name',
        'file_storage_path',
        'file_remarks',
        'file_status',
        'file_size_8b',
        'qs_education',
        'qs_eligibility',
        'qs_experience',
        'qs_training',
        'qs_result',
        'application_remarks',
        'link_sent_at',
        'exam_token',
        'exam_token_expires_at',
        'read_at',
        'exam_started_at',
        'exam_end_time'
    ];

    protected $casts = [
        'answers' => 'array',
        'scores' => 'array',
    ];

    public function vacancy()
    {
        return $this->belongsTo(JobVacancy::class, 'vacancy_id', 'vacancy_id');
    }

    public function personalInformation()
    {
        return $this->belongsTo(PersonalInformation::class, 'user_id', 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function updatedByAdmin()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'updated_by_admin_id');
    }

    

}

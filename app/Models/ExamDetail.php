<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'vacancy_id',
        'is_started',
        'time',
        'date',
        'place',
        'duration',
        'notified_at',
        'details_saved',
        'link_sent',
        'link_sent_at',
    ];

    public function vacancy()
    {

        return $this->belongsTo(JobVacancy::class, 'vacancy_id', 'vacancy_id');
    }
}

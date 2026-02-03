<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExamItems extends Model
{
    use HasFactory;

    protected $fillable = [
        'vacancy_id',
        'question',
        'is_essay',
        'choices',
        'ans',
    ];

    protected $casts = [
        'choices' => 'array',
    ];
}

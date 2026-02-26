<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacancyTitle extends Model
{
    use HasFactory;

    protected $table = 'vacancy_titles';

    protected $fillable = [
        'position_title',
        'salary_grade',
        'monthly_salary',
    ];
}


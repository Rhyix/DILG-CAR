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
        'vacancy_type',
        'pcn_no',
        'plantilla_item_no',
        'closing_date',
        'salary_grade',
        'monthly_salary',
        'place_of_assignment',
        'qualification_education',
        'qualification_training',
        'qualification_experience',
        'qualification_eligibility',
        'competencies',
        'expected_output',
        'scope_of_work',
        'duration_of_work',
        'to_person',
        'to_position',
        'to_office',
        'to_office_address',
        'csc_form_path',
    ];
}

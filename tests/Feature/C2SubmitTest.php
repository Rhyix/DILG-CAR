<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class C2SubmitTest extends TestCase
{
    use RefreshDatabase;

    public function test_c2_submit_saves_work_experience_without_civil_service_and_preserves_simple_flag(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('submit_c2', ['go_to' => 'display_c3']), [
            'simple' => 1,
            'work_exp_id' => [''],
            'work_exp_from' => ['2026-01-01'],
            'work_exp_to' => ['2026-02-01'],
            'work_exp_position' => ['Planning Officer'],
            'work_exp_department' => ['Planning Unit'],
            'work_exp_status' => ['Permanent'],
            'work_exp_govt_service' => ['Y'],
            'cs_eligibility_id' => [''],
            'cs_eligibility_career' => [''],
            'cs_eligibility_rating' => [''],
            'cs_eligibility_date' => [''],
            'cs_eligibility_place' => [''],
            'cs_eligibility_license' => [''],
            'cs_eligibility_validity' => [''],
        ]);

        $response->assertRedirect(route('display_c3', ['simple' => 1]));
        $this->assertDatabaseHas('work_experiences', [
            'user_id' => $user->id,
            'work_exp_position' => 'Planning Officer',
            'work_exp_from' => '2026-01-01',
            'work_exp_to' => '2026-02-01',
        ]);
    }
}

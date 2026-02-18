<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\JobVacancy;
use App\Models\Applications;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendExamNotification;

class ExamNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_and_notify_triggers_job()
    {
        // 1. Setup Data
        Queue::fake();
        Mail::fake();

        // Create Admin User for auth
        $admin = Admin::create([
            'username' => 'test_admin',
            'name' => 'Test Admin',
            'office' => 'IT',
            'designation' => 'Developer',
            'email' => 'admin@dilg.gov.ph',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        
        // Create Vacancy
        $vacancy = JobVacancy::create([
            'vacancy_id' => 'TEST-001',
            'position_title' => 'Test Position',
            'vacancy_type' => 'COS',
            'monthly_salary' => 35000,
            'status' => 'OPEN',
            'closing_date' => now()->addWeek(),
            'qualification_education' => 'Bachelor',
            'qualification_training' => 'None',
            'qualification_experience' => '1 year',
            'qualification_eligibility' => 'None',
            'to_person' => 'HR Officer',
            'to_position' => 'HR',
            'to_office' => 'DILG',
            'to_office_address' => 'Baguio',
            'place_of_assignment' => 'Baguio',
        ]);

        // Create Applicant
        $user = User::factory()->create();
        Applications::create([
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $user->id,
            'status' => 'Pending',
        ]);

        // 2. Simulate Request
        $response = $this->actingAs($admin, 'admin') // Assuming 'admin' guard
            ->postJson("/admin/exam_management/{$vacancy->vacancy_id}/details/save", [
                'place' => 'Test Venue',
                'date' => '2025-12-31',
                'time' => '09:00',
                'time_end' => '10:00',
                'duration' => 60,
                'notify' => 1 // Critical flag
            ]);

        // 3. Assertions
        $response->assertStatus(200)
            ->assertJson(['success' => true, 'notified' => true]);

        // Check Job Pushed
        Queue::assertPushed(SendExamNotification::class, function ($job) use ($user, $vacancy) {
            $ref = new \ReflectionClass($job);
            $vacancyProp = $ref->getProperty('vacancyId');
            $vacancyProp->setAccessible(true);
            $userProp = $ref->getProperty('userId');
            $userProp->setAccessible(true);
            return $userProp->getValue($job) === $user->id && $vacancyProp->getValue($job) === $vacancy->vacancy_id;
        });

        // Check Exam Detail Updated
        $this->assertDatabaseHas('exam_details', [
            'vacancy_id' => $vacancy->vacancy_id,
            'place' => 'Test Venue'
        ]);
    }
}

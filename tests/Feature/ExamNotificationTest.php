<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\JobVacancy;
use App\Models\ExamDetail;
use App\Models\Applications;
use App\Models\EmailLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendExamNotification;
use App\Mail\NotifyApplicantMail;

class ExamNotificationTest extends TestCase
{
    // Use RefreshDatabase to reset DB state between tests if using SQLite/testing DB
    // use RefreshDatabase; 

    public function test_save_and_notify_triggers_job()
    {
        // 1. Setup Data
        Queue::fake();
        Mail::fake();

        // Create Admin User for auth
        $admin = User::factory()->create(['email' => 'admin@dilg.gov.ph']); // or Admin model if separate
        
        // Create Vacancy
        $vacancy = JobVacancy::create([
            'vacancy_id' => 'TEST-001',
            'position_title' => 'Test Position',
            'vacancy_type' => 'Contractual',
            // ... other required fields
        ]);

        // Create Applicant
        $user = User::factory()->create();
        Applications::create([
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $user->id,
            // ... other fields
        ]);

        // 2. Simulate Request
        $response = $this->actingAs($admin, 'admin') // Assuming 'admin' guard
            ->postJson("/admin/exam_management/{$vacancy->vacancy_id}/details/save", [
                'place' => 'Test Venue',
                'date' => '2025-12-31',
                'time' => '09:00',
                'duration' => 60,
                'notify' => 1 // Critical flag
            ]);

        // 3. Assertions
        $response->assertStatus(200)
            ->assertJson(['success' => true, 'notified' => true]);

        // Check Job Pushed
        Queue::assertPushed(SendExamNotification::class, function ($job) use ($user, $vacancy) {
            return $job->user_id === $user->id && $job->vacancy_id === $vacancy->vacancy_id;
        });

        // Check Exam Detail Updated
        $this->assertDatabaseHas('exam_details', [
            'vacancy_id' => $vacancy->vacancy_id,
            'place' => 'Test Venue'
        ]);
    }
}

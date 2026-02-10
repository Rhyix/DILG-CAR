<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\JobVacancy;
use App\Models\ExamDetail;
use App\Models\Applications;
use App\Models\PersonalInformation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendExamNotification;
use ReflectionClass;

class ExamLobbyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_fetch_lobby_data()
    {
        // Setup
        $admin = Admin::create([
            'name' => 'Admin Test',
            'username' => 'admintest',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin', // Correct role
            'is_active' => true,
            'office' => 'ORD',
            'designation' => 'Director'
        ]);

        $vacancy = JobVacancy::factory()->create();
        $applicant = User::factory()->create();

        $personalInfo = PersonalInformation::factory()->create(['user_id' => $applicant->id]);

        $application = Applications::factory()->create([
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $applicant->id,
            'status' => 'qualified'
        ]);

        // Act
        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.exam.lobby_data', ['vacancy_id' => $vacancy->vacancy_id]));

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'participants'])
            ->assertJsonPath('success', true);

        // We might not see the name directly if the factory setup is minimal, 
        // but let's check if we get at least one participant.
        $this->assertCount(1, $response->json('participants'));
    }

    public function test_admin_can_send_notifications_to_selected_applicants()
    {
        Queue::fake();

        // Setup
        $admin = Admin::create([
            'name' => 'Admin Test',
            'username' => 'admintest2', // Unique username
            'email' => 'admin2@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
            'office' => 'ORD',
            'designation' => 'Director'
        ]);

        $vacancy = JobVacancy::factory()->create();
        $detail = ExamDetail::factory()->create([
            'vacancy_id' => $vacancy->vacancy_id,
            'details_saved' => true
        ]);

        $applicant1 = User::factory()->create();
        $app1 = Applications::factory()->create([
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $applicant1->id,
            'status' => 'qualified'
        ]);

        $applicant2 = User::factory()->create();
        $app2 = Applications::factory()->create([
            'vacancy_id' => $vacancy->vacancy_id,
            'user_id' => $applicant2->id,
            'status' => 'qualified'
        ]);

        // Act - Select applicant 1 only
        $response = $this->actingAs($admin, 'admin')
            ->postJson(route('admin.exam.notify_selected', ['vacancy_id' => $vacancy->vacancy_id]), [
                'user_ids' => [$applicant1->id]
            ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check Job Pushed for Applicant 1
        Queue::assertPushed(SendExamNotification::class, function ($job) use ($applicant1) {
            $reflection = new ReflectionClass($job);
            $property = $reflection->getProperty('userId');
            $property->setAccessible(true);
            return $property->getValue($job) === $applicant1->id;
        });

        // Check Job NOT Pushed for Applicant 2
        Queue::assertNotPushed(SendExamNotification::class, function ($job) use ($applicant2) {
            $reflection = new ReflectionClass($job);
            $property = $reflection->getProperty('userId');
            $property->setAccessible(true);
            return $property->getValue($job) === $applicant2->id;
        });

        // Check DB for Token Generation
        $this->assertNotNull($app1->fresh()->exam_token, 'Exam token should be generated for applicant 1');
        $this->assertNull($app2->fresh()->exam_token, 'Exam token should NOT be generated for applicant 2');
    }
}

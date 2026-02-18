<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\Notification;
use Spatie\Activitylog\Models\Activity;

class AdminNotificationFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_logout_events_do_not_create_admin_notifications(): void
    {
        $admin = Admin::create([
            'username' => 'admin1',
            'name' => 'Admin One',
            'email' => 'admin1@example.com',
            'password' => bcrypt('secret'),
            'role' => 'super',
            'is_active' => 1,
        ]);

        activity()
            ->causedBy($admin)
            ->event('logout')
            ->withProperties(['section' => 'Login'])
            ->log('logged out');

        $count = Notification::where('notifiable_type', Admin::class)->count();
        $this->assertSame(0, $count);
    }

    public function test_document_verification_creates_admin_notifications(): void
    {
        $admin = Admin::create([
            'username' => 'admin2',
            'name' => 'Admin Two',
            'email' => 'admin2@example.com',
            'password' => bcrypt('secret'),
            'role' => 'super',
            'is_active' => 1,
        ]);

        $changes = [
            'document_application_letter' => [
                'status' => ['old' => 'Pending', 'new' => 'Verified']
            ]
        ];

        activity()
            ->causedBy($admin)
            ->event('update')
            ->withProperties(['section' => 'Application List', 'user_id' => 10, 'vacancy_id' => 'VAC-001', 'changes' => $changes])
            ->log('Updated applicant status and documents.');

        $exists = Notification::where('notifiable_type', Admin::class)
            ->where('data->category', 'document_verification')
            ->exists();
        $this->assertTrue($exists);
    }

    public function test_exam_lifecycle_events_create_notifications(): void
    {
        $admin = Admin::create([
            'username' => 'admin3',
            'name' => 'Admin Three',
            'email' => 'admin3@example.com',
            'password' => bcrypt('secret'),
            'role' => 'super',
            'is_active' => 1,
        ]);

        activity()
            ->causedBy($admin)
            ->event('start')
            ->withProperties(['section' => 'Exam Management', 'vacancy_id' => 'VAC-002'])
            ->log('Started the exam.');

        $exists = Notification::where('notifiable_type', Admin::class)
            ->where('data->category', 'exam_lifecycle')
            ->exists();
        $this->assertTrue($exists);
    }

    public function test_exam_question_update_creates_notifications(): void
    {
        $admin = Admin::create([
            'username' => 'admin4',
            'name' => 'Admin Four',
            'email' => 'admin4@example.com',
            'password' => bcrypt('secret'),
            'role' => 'super',
            'is_active' => 1,
        ]);

        activity()
            ->causedBy($admin)
            ->event('update')
            ->withProperties(['section' => 'Exam Management', 'vacancy_id' => 'VAC-003', 'questions_count' => 5])
            ->log('updated exam questions.');

        $exists = Notification::where('notifiable_type', Admin::class)
            ->where('data->category', 'exam_questions')
            ->exists();
        $this->assertTrue($exists);
    }
}

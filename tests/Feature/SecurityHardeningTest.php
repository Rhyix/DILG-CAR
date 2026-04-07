<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Config::set('activitylog.enabled', false);

        Schema::dropIfExists('job_vacancies');
        Schema::dropIfExists('users');

        Schema::create('job_vacancies', function (Blueprint $table): void {
            $table->id();
            $table->string('status')->nullable();
            $table->date('closing_date')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function test_login_page_uses_strict_security_headers(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');

        $csp = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertStringContainsString("style-src 'self'", $csp);
        $this->assertStringContainsString("script-src-attr 'none'", $csp);
        $this->assertStringNotContainsString("'unsafe-inline'", $csp);
        $this->assertStringNotContainsString("'unsafe-eval'", $csp);
    }

    public function test_robots_txt_is_served_through_the_app_with_security_headers(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertSeeText('User-agent: *');
    }

    public function test_guest_redirects_have_empty_bodies(): void
    {
        $response = $this->get('/my_applications');

        $response->assertRedirect('/login');
        $this->assertSame('', $response->getContent());
    }

    public function test_login_rejects_tampered_remember_input(): void
    {
        DB::table('users')->insert([
            'name' => 'Applicant User',
            'email' => 'applicant@example.com',
            'password' => Hash::make('password123'),
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'applicant@example.com',
            'password' => 'password123',
            'remember' => 'on OR 1=1 -- ',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}

<?php

namespace Tests\Feature;

use App\Http\Middleware\SecureHeaders;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoginSecurityHardeningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('applicant_code')->nullable()->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->dateTime('closing_date');
            $table->timestamps();
        });
    }

    public function test_login_rejects_malformed_remember_values_before_authentication(): void
    {
        User::factory()->create([
            'email' => 'applicant@example.com',
            'password' => Hash::make('Secret123!'),
        ]);

        $response = $this->from(route('login.form'))->post(route('login'), [
            'email' => 'applicant@example.com',
            'password' => 'Secret123!',
            'remember' => 'on OR 1=1 -- ',
        ]);

        $response->assertRedirect(route('login.form'));
        $response->assertSessionHasErrors('remember');
        $this->assertGuest();
    }

    public function test_home_page_uses_strict_content_security_policy_without_inline_or_eval(): void
    {
        $request = Request::create('/', 'GET');
        $route = (new Route('GET', '/', fn () => response('ok')))->name('home');
        $request->setRouteResolver(static fn () => $route);

        $response = app(SecureHeaders::class)->handle($request, fn () => response('ok'));
        $policy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertNotSame('', $policy);
        $this->assertStringNotContainsString("'unsafe-inline'", $policy);
        $this->assertStringNotContainsString("'unsafe-eval'", $policy);
        $this->assertStringNotContainsString('https://cdn.tailwindcss.com', $policy);
    }
}

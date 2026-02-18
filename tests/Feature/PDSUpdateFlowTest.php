<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PDSUpdateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pds_c1_submit_redirects_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Required fields for C1
        $data = [
            'surname' => 'Doe',
            'first_name' => 'John',
            'middle_name' => 'M',
            'civil_status' => 'single',
            'date_of_birth' => '01-01-1990',
            'place_of_birth' => 'Test City',
            'citizenship' => 'Filipino',
            'sex' => 'male',
            'blood_type' => 'O+',
            'mobile_no' => '09123456789',
            'email_address' => 'john@example.com',
            'height' => 170,
            'weight' => 70,
            'elem_from' => '01-01-2000',
            'elem_to' => '01-01-2006',
            'jhs_from' => '01-01-2006',
            'jhs_to' => '01-01-2010',
            // Add other required fields if any
        ];

        // Route helper should generate correct URL if route is defined correctly
        // We expect it to be /pds/submit_c1/c2_update or similar
        // If it generates ?go_to=c2_update, it might fail if controller expects param
        $url = route('submit_c1', ['go_to' => 'c2_update']);
        dump("Generated URL: " . $url);
        
        $response = $this->post($url, $data);

        // Check for success (redirect)
        // If 500, controller failed (argument count error)
        // If 404, route not found
        // If 302, it worked
        $response->assertStatus(302);
        $response->assertRedirect(route('c2_update'));
    }

    public function test_pds_finalize_route_exists()
    {
        $url = route('finalize_pds', ['go_to' => 'dashboard_user']);
        dump("Finalize URL: " . $url);
        // Expecting /pds/finalize/dashboard_user
        $this->assertStringContainsString('/pds/finalize/dashboard_user', $url);
    }
}

<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PDSValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_number_validation_fails_for_invalid_format(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $payload = [
            'surname' => 'Doe',
            'first_name' => 'John',
            'middle_name' => null,
            'name_extension' => null,
            'civil_status' => 'single',
            'date_of_birth' => '01-01-1990',
            'place_of_birth' => 'Manila',
            'citizenship' => 'Filipino',
            'sex' => 'male',
            'blood_type' => 'O+',
            'telephone_no' => '0281234567',
            'mobile_no' => '0912345678', // invalid (10 digits instead of 11)
            'email_address' => 'john@example.com',
            'height' => 170,
            'weight' => 65,
            'elem_from' => '01-06-2000',
            'elem_to' => '01-03-2006',
            'jhs_from' => '01-06-2006',
            'jhs_to' => '01-03-2010',
        ];

        $res = $this->post('/pds/submit_c1/display_c2', $payload);
        $res->assertSessionHasErrors(['mobile_no']);
    }

    public function test_mobile_number_validation_passes_for_valid_format(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $payload = [
            'surname' => 'Doe',
            'first_name' => 'Jane',
            'middle_name' => null,
            'name_extension' => null,
            'civil_status' => 'single',
            'date_of_birth' => '12-05-1992',
            'place_of_birth' => 'Quezon City',
            'citizenship' => 'Filipino',
            'sex' => 'female',
            'blood_type' => 'A+',
            'telephone_no' => '0281234567',
            'mobile_no' => '09123456789',
            'email_address' => 'jane@example.com',
            'height' => 165,
            'weight' => 55,
            'elem_from' => '01-06-2000',
            'elem_to' => '01-03-2006',
            'jhs_from' => '01-06-2006',
            'jhs_to' => '01-03-2010',
        ];

        $res = $this->post('/pds/submit_c1/display_c2', $payload);
        $res->assertSessionHasNoErrors();
        $res->assertRedirect();
    }

    public function test_date_of_birth_requires_valid_format(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $payload = [
            'surname' => 'Smith',
            'first_name' => 'Alan',
            'middle_name' => null,
            'name_extension' => null,
            'civil_status' => 'single',
            'date_of_birth' => '1990/12/31',
            'place_of_birth' => 'Pasig',
            'citizenship' => 'Filipino',
            'sex' => 'male',
            'blood_type' => 'B+',
            'telephone_no' => '0281234567',
            'mobile_no' => '09123456789',
            'email_address' => 'alan@example.com',
            'height' => 175,
            'weight' => 70,
            'elem_from' => '01-06-2000',
            'elem_to' => '01-03-2006',
            'jhs_from' => '01-06-2006',
            'jhs_to' => '01-03-2010',
        ];

        $res = $this->post('/pds/submit_c1/display_c2', $payload);
        $res->assertSessionHasErrors(['date_of_birth']);
    }
}

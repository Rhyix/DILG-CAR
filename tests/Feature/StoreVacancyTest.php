<?php

namespace Tests\Feature;

use Tests\TestCase;

class StoreVacancyTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}

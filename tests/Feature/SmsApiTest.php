<?php

namespace Tests\Feature;

use App\Models\Gateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test gateway
        Gateway::create([
            'name' => 'Test Gateway',
            'api_url' => 'http://test.gateway.com',
            'api_key' => 'test_key',
            'secret_key' => 'test_secret',
            'status' => 'active',
        ]);
    }

    public function test_get_balance_with_valid_api_key(): void
    {
        $user = User::factory()->create([
            'api_key' => 'test_api_key_123',
            'balance' => 100.50,
            'rate' => 0.30,
        ]);

        $response = $this->get('/api/getBalance?api_key=test_api_key_123');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'balance' => '100.50',
                'rate' => '0.30',
                'name' => $user->name,
            ]);
    }

    public function test_get_balance_with_invalid_api_key(): void
    {
        $response = $this->get('/api/getBalance?api_key=invalid_key');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid API key',
            ]);
    }

    public function test_send_sms_with_insufficient_balance(): void
    {
        $user = User::factory()->create([
            'api_key' => 'test_api_key_123',
            'balance' => 0.10,
            'rate' => 0.30,
            'status' => 'active',
        ]);

        $response = $this->get('/api/smsapi2?api_key=test_api_key_123&type=text&contacts=1234567890&senderid=TEST&msg=Hello');

        $response->assertStatus(402)
            ->assertJson([
                'status' => 'error',
                'message' => 'Insufficient balance',
            ]);
    }

    public function test_send_sms_validation(): void
    {
        $user = User::factory()->create([
            'api_key' => 'test_api_key_123',
            'balance' => 100,
            'status' => 'active',
        ]);

        // Test missing required fields
        $response = $this->get('/api/smsapi2?api_key=test_api_key_123');

        $response->assertStatus(302); // Validation redirect
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_reseller_can_access_dashboard(): void
    {
        $reseller = User::factory()->create([
            'role' => 'reseller',
        ]);

        $response = $this->actingAs($reseller)->get('/reseller/dashboard');

        $response->assertStatus(200);
    }
}

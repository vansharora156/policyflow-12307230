<?php

namespace Tests\Feature;

use App\Models\PolicyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_policy_request(): void
    {
        $response = $this->postJson('/api/policy-requests', [
            'client_request_id' => 'REQ-01',
            'agent_id' => 'AGENT-01',
            'customer_name' => 'John Doe',
            'date_of_birth' => '1995-01-01',
            'destination' => 'DOMESTIC',
            'trip_start' => '2026-11-01',
            'trip_end' => '2026-11-05',
            'plan_code' => 'BASIC',
            'sum_insured' => 100000,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('policy_requests', ['client_request_id' => 'REQ-01']);
    }

    public function test_can_submit_policy(): void
    {
        $policy = PolicyRequest::create([
            'client_request_id' => 'REQ-02',
            'agent_id' => 'AGENT-01',
            'customer_name' => 'Jane Doe',
            'date_of_birth' => '1995-01-01',
            'destination' => 'DOMESTIC',
            'trip_start' => '2026-11-01',
            'trip_end' => '2026-11-05',
            'travel_days' => 5,
            'plan_code' => 'BASIC',
            'sum_insured' => 100000,
            'calculated_premium' => 60.00,
            'status' => 'VALIDATED',
        ]);

        $response = $this->postJson("/api/policy-requests/{$policy->id}/submit");
        $response->assertStatus(200);
    }
}

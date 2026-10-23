<?php

namespace Tests\Feature;

use App\Models\PolicyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Intake creates a request and computes premium correctly.
     */
    public function test_creates_policy_request_and_calculates_premium(): void
    {
        $payload = [
            'client_request_id' => 'AGT-2026-0001',
            'agent_id' => 'AGENT-17',
            'customer_name' => 'Sample Customer',
            'date_of_birth' => '1998-04-10',
            'destination' => 'INTERNATIONAL',
            'trip_start' => '2026-11-10',
            'trip_end' => '2026-11-14',
            'plan_code' => 'PLUS',
            'sum_insured' => 300000,
        ];

        $response = $this->postJson('/api/policy-requests', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'is_repeated' => false,
                'data' => [
                    'client_request_id' => 'AGT-2026-0001',
                    'agent_id' => 'AGENT-17',
                    'travel_days' => 5,
                    'calculated_premium' => 225.00,
                ],
            ]);

        $this->assertDatabaseHas('policy_requests', [
            'client_request_id' => 'AGT-2026-0001',
            'calculated_premium' => 225.00,
        ]);
    }

    /**
     * Test 2: Duplicate request returns 200 without creating a second record.
     */
    public function test_duplicate_submission_returns_200(): void
    {
        $payload = [
            'client_request_id' => 'AGT-DUP-01',
            'agent_id' => 'AGENT-01',
            'customer_name' => 'John Doe',
            'date_of_birth' => '1995-01-01',
            'destination' => 'DOMESTIC',
            'trip_start' => '2026-12-01',
            'trip_end' => '2026-12-05',
            'plan_code' => 'BASIC',
            'sum_insured' => 100000,
        ];

        // First submit -> 201
        $this->postJson('/api/policy-requests', $payload)->assertStatus(201);

        // Second submit -> 200
        $this->postJson('/api/policy-requests', $payload)
            ->assertStatus(200)
            ->assertJson(['is_repeated' => true]);

        $this->assertDatabaseCount('policy_requests', 1);
    }

    /**
     * Test 3: Invalid trip dates are rejected with 422.
     */
    public function test_invalid_dates_rejected(): void
    {
        $payload = [
            'client_request_id' => 'AGT-BAD-DATES',
            'agent_id' => 'AGENT-01',
            'customer_name' => 'Bad Dates',
            'date_of_birth' => '1995-01-01',
            'destination' => 'DOMESTIC',
            'trip_start' => '2026-12-10',
            'trip_end' => '2026-12-05', // trip_end is before trip_start
            'plan_code' => 'BASIC',
            'sum_insured' => 100000,
        ];

        $this->postJson('/api/policy-requests', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['trip_end']);
    }

    /**
     * Test 4: Submitting policy creates policy number and marks it ISSUED.
     */
    public function test_submit_issues_policy(): void
    {
        $policy = PolicyRequest::create([
            'client_request_id' => 'AGT-SUBMIT-01',
            'agent_id' => 'AGENT-01',
            'customer_name' => 'Jane Doe',
            'date_of_birth' => '1995-01-01',
            'destination' => 'DOMESTIC',
            'trip_start' => '2026-12-01',
            'trip_end' => '2026-12-05',
            'travel_days' => 5,
            'plan_code' => 'BASIC',
            'sum_insured' => 100000,
            'calculated_premium' => 60.00,
            'status' => 'VALIDATED',
        ]);

        $response = $this->postJson("/api/policy-requests/{$policy->id}/submit");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'status' => 'ISSUED',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.policy_number'));
    }

    /**
     * Test 5: Temporary failure handling and subsequent retry.
     */
    public function test_temporary_failure_and_retry(): void
    {
        $policy = PolicyRequest::create([
            'client_request_id' => 'AGT-RETRY-01',
            'agent_id' => 'AGENT-01',
            'customer_name' => 'Retry Test',
            'date_of_birth' => '1995-01-01',
            'destination' => 'DOMESTIC',
            'trip_start' => '2026-12-01',
            'trip_end' => '2026-12-05',
            'travel_days' => 5,
            'plan_code' => 'BASIC',
            'sum_insured' => 100000,
            'calculated_premium' => 60.00,
            'status' => 'VALIDATED',
        ]);

        // Attempt 1: Simulate temporary failure
        $this->postJson("/api/policy-requests/{$policy->id}/submit", [
            'scenario' => 'TEMPORARY_FAILURE',
        ])->assertStatus(503);

        $policy->refresh();
        $this->assertEquals('FAILED', $policy->status);

        // Attempt 2: Retry with success
        $this->postJson("/api/policy-requests/{$policy->id}/submit", [
            'scenario' => 'SUCCESS',
        ])->assertStatus(200);

        $policy->refresh();
        $this->assertEquals('ISSUED', $policy->status);
    }
}

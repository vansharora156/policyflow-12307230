<?php

namespace App\Http\Controllers;

use App\Models\PolicyRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    /**
     * POST /api/policy-requests
     * Simple intake endpoint with duplicate prevention and basic pricing logic.
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Basic validation
        $validated = $request->validate([
            'client_request_id' => 'required|string',
            'agent_id' => 'required|string',
            'customer_name' => 'required|string',
            'date_of_birth' => 'required|date',
            'destination' => 'required|in:DOMESTIC,INTERNATIONAL',
            'trip_start' => 'required|date',
            'trip_end' => 'required|date|after_or_equal:trip_start',
            'plan_code' => 'required|in:BASIC,PLUS,PREMIUM',
            'sum_insured' => 'required|in:100000,300000,500000',
        ]);

        // 2. Duplicate Intake Check (Reliability Case 1)
        // If agent_id + client_request_id already exists, return existing request with 200 OK.
        $existing = PolicyRequest::where('agent_id', $validated['agent_id'])
            ->where('client_request_id', $validated['client_request_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'success',
                'is_repeated' => true,
                'message' => 'Existing policy request returned.',
                'data' => $existing,
            ], 200);
        }

        // 3. Simple Premium Calculation
        $start = Carbon::parse($validated['trip_start']);
        $end = Carbon::parse($validated['trip_end']);
        $dob = Carbon::parse($validated['date_of_birth']);

        // Days: inclusive (minimum 1)
        $travelDays = max(1, (int)$start->diffInDays($end) + 1);

        // Age at trip start
        $age = (int)$dob->diffInYears($start);

        // Base rate per day
        $baseRates = ['BASIC' => 12.00, 'PLUS' => 20.00, 'PREMIUM' => 30.00];
        $baseRate = $baseRates[$validated['plan_code']];

        // Age multiplier
        if ($age <= 17) {
            $ageMultiplier = 1.10;
        } elseif ($age <= 60) {
            $ageMultiplier = 1.00;
        } elseif ($age <= 70) {
            $ageMultiplier = 1.50;
        } else {
            $ageMultiplier = 2.00;
        }

        // Destination multiplier
        $destMultiplier = ($validated['destination'] === 'INTERNATIONAL') ? 1.80 : 1.00;

        // Coverage multiplier
        $coverageMultipliers = [
            '100000' => 1.00,
            '300000' => 1.25,
            '500000' => 1.50,
        ];
        $covMultiplier = $coverageMultipliers[(string)(int)$validated['sum_insured']];

        // Formula: premium = base * days * age * destination * coverage
        $premium = round($baseRate * $travelDays * $ageMultiplier * $destMultiplier * $covMultiplier, 2);

        // 4. Save to database
        $policy = PolicyRequest::create([
            'client_request_id' => $validated['client_request_id'],
            'agent_id' => $validated['agent_id'],
            'customer_name' => $validated['customer_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'destination' => $validated['destination'],
            'trip_start' => $validated['trip_start'],
            'trip_end' => $validated['trip_end'],
            'travel_days' => $travelDays,
            'plan_code' => $validated['plan_code'],
            'sum_insured' => $validated['sum_insured'],
            'calculated_premium' => $premium,
            'status' => 'VALIDATED',
        ]);

        return response()->json([
            'status' => 'success',
            'is_repeated' => false,
            'data' => $policy,
        ], 201);
    }

    /**
     * GET /api/policy-requests/{id}
     */
    public function show($id): JsonResponse
    {
        $policy = PolicyRequest::find($id);

        if (!$policy) {
            return response()->json(['message' => 'Policy not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $policy,
        ], 200);
    }

    /**
     * GET /api/policy-requests
     */
    public function index(Request $request): JsonResponse
    {
        $query = PolicyRequest::query();

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->paginate(15),
        ], 200);
    }

    /**
     * POST /api/policy-requests/{id}/submit
     * Simple mock insurer submission.
     */
    public function submit(Request $request, $id): JsonResponse
    {
        $policy = PolicyRequest::find($id);

        if (!$policy) {
            return response()->json(['message' => 'Policy not found'], 404);
        }

        // If already issued, do not issue again:
        if ($policy->status === 'ISSUED') {
            return response()->json([
                'status' => 'success',
                'message' => 'Policy already issued.',
                'data' => $policy,
            ], 200);
        }

        $policy->attempt_count = $policy->attempt_count + 1;
        $scenario = $request->input('scenario', 'SUCCESS');

        // Simple failure simulation:
        if ($scenario === 'TEMPORARY_FAILURE') {
            $policy->status = 'FAILED';
            $policy->last_error = 'Temporary insurer outage.';
            $policy->save();

            return response()->json([
                'status' => 'FAILED',
                'message' => 'Mock insurer temporary failure.',
            ], 503);
        }

        if ($scenario === 'TIMEOUT') {
            $policy->status = 'NEEDS_RECONCILIATION';
            $policy->provider_reference = 'PRV-' . rand(10000, 99999);
            $policy->last_error = 'Network timeout.';
            $policy->save();

            return response()->json([
                'status' => 'NEEDS_RECONCILIATION',
                'message' => 'Insurer call timed out.',
                'data' => $policy,
            ], 504);
        }

        // Default SUCCESS scenario:
        $policy->status = 'ISSUED';
        $policy->policy_number = 'POL-' . date('Ymd') . '-' . rand(1000, 9999);
        $policy->provider_reference = 'PRV-' . rand(10000, 99999);
        $policy->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Policy issued successfully.',
            'data' => $policy,
        ], 200);
    }

    /**
     * POST /api/mock-insurer/callback
     * Basic callback receiver.
     */
    public function callback(Request $request): JsonResponse
    {
        $ref = $request->input('provider_reference');
        $policyNumber = $request->input('policy_number');

        $policy = PolicyRequest::where('provider_reference', $ref)->first();

        if ($policy && $policy->status !== 'ISSUED') {
            $policy->status = 'ISSUED';
            $policy->policy_number = $policyNumber ?: ('POL-' . date('Ymd') . '-' . rand(1000, 9999));
            $policy->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Callback received.',
        ], 200);
    }
}

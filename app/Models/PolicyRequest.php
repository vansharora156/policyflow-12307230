<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_request_id',
        'agent_id',
        'customer_name',
        'date_of_birth',
        'destination',
        'trip_start',
        'trip_end',
        'travel_days',
        'plan_code',
        'sum_insured',
        'calculated_premium',
        'status',
        'policy_number',
        'provider_reference',
        'attempt_count',
        'last_response',
        'last_error',
    ];

    protected $casts = [
        'date_of_birth' => 'date:Y-m-d',
        'trip_start' => 'date:Y-m-d',
        'trip_end' => 'date:Y-m-d',
        'sum_insured' => 'decimal:2',
        'calculated_premium' => 'decimal:2',
        'last_response' => 'array',
        'attempt_count' => 'integer',
        'travel_days' => 'integer',
    ];
}

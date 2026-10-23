<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('policy_requests', function (Blueprint $table) {
            $table->id();
            $table->string('client_request_id', 100);
            $table->string('agent_id', 100);
            $table->string('customer_name', 255);
            $table->date('date_of_birth');
            $table->string('destination', 50); // DOMESTIC, INTERNATIONAL
            $table->date('trip_start');
            $table->date('trip_end');
            $table->unsignedInteger('travel_days');
            $table->string('plan_code', 50); // BASIC, PLUS, PREMIUM
            $table->decimal('sum_insured', 12, 2); // 100000, 300000, 500000
            $table->decimal('calculated_premium', 10, 2); // Immutable snapshot
            $table->string('status', 50)->default('RECEIVED');
            $table->string('policy_number', 100)->nullable()->unique();
            $table->string('provider_reference', 100)->nullable()->index();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->json('last_response')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            // Enforce database-level uniqueness for idempotency:
            $table->unique(['agent_id', 'client_request_id'], 'uq_policy_agent_client_req');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_requests');
    }
};

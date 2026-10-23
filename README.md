# PolicyFlow
**Basic Insurance Issuance & Intake Assessment**

A basic Laravel application implementing the PolicyFlow insurance intake and pricing engine according to the assessment specifications.

---

## 1. Setup Instructions

### Prerequisites
- PHP 8.2 or 8.3
- Composer

### Installation Steps
1. Clone the repository and navigate into the folder:
   ```bash
   cd policyflow
   ```

2. Install dependencies:
   ```bash
   composer install --no-audit
   ```

3. Setup environment and database:
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   ```

4. Run the automated tests:
   ```bash
   php artisan test
   ```

5. Start local server:
   ```bash
   php artisan serve
   ```
   Open `http://127.0.0.1:8000` to view the basic web interface.

---

## 2. Implemented Features

1. **Policy Intake API (`POST /api/policy-requests`)**:
   - Validates required fields, date constraints (`trip_end >= trip_start`), and supported plans.
   - Calculates premium using the assignment pricing formula:
     `base_daily_rate * travel_days * age_multiplier * destination_multiplier * coverage_multiplier`.
   - Persists the request with status `VALIDATED`.

2. **Reliability Scenarios Implemented (2 Cases)**:
   - **Case 1 (Duplicate Intake)**: When a request with the same `agent_id` and `client_request_id` is submitted again, the API returns the existing record with `HTTP 200` without creating a duplicate record.
   - **Case 2 (Retry After Temporary Failure)**: When an initial submission fails due to a mock temporary failure (503), retrying the submission succeeds and sets the policy to `ISSUED` without losing the original calculated premium.

3. **Issuance Workflow (`POST /api/policy-requests/{id}/submit`)**:
   - Updates status to `ISSUED` and generates a policy number.

4. **Webhook Callback (`POST /api/mock-insurer/callback`)**:
   - Simple endpoint to receive simulated provider events.

---

## 3. Sample cURL Request

```bash
curl -X POST http://127.0.0.1:8000/api/policy-requests \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "client_request_id": "AGT-2026-0001",
    "agent_id": "AGENT-17",
    "customer_name": "Sample Customer",
    "date_of_birth": "1998-04-10",
    "destination": "INTERNATIONAL",
    "trip_start": "2026-11-10",
    "trip_end": "2026-11-14",
    "plan_code": "PLUS",
    "sum_insured": 300000
  }'
```

---

## 4. Known Limitations
- Basic local mock insurer without external webhooks.
- Uses SQLite for simplified local setup.

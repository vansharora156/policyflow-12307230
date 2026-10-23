#!/bin/bash
# PolicyFlow API cURL Examples

BASE_URL="http://127.0.0.1:8000"

echo "=================================================="
echo "1. Intake: Create New Policy Request (Expected: 201 Created)"
echo "=================================================="
curl -s -X POST "$BASE_URL/api/policy-requests" \
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
  }' | jq .

echo -e "\n=================================================="
echo "2. Intake: Repeated Submission (Idempotent: 200 OK)"
echo "=================================================="
curl -s -X POST "$BASE_URL/api/policy-requests" \
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
  }' | jq .

echo -e "\n=================================================="
echo "3. Submit for Issuance (SUCCESS Scenario)"
echo "=================================================="
curl -s -X POST "$BASE_URL/api/policy-requests/1/submit" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"scenario": "SUCCESS"}' | jq .

echo -e "\n=================================================="
echo "4. Get Policy Audit Trail & State History"
echo "=================================================="
curl -s -X GET "$BASE_URL/api/policy-requests/1" \
  -H "Accept: application/json" | jq .

echo -e "\n=================================================="
echo "5. Provider Callback Simulation (Idempotent 200 OK)"
echo "=================================================="
curl -s -X POST "$BASE_URL/api/mock-insurer/callback" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "event_id": "EVT-CURL-001",
    "provider_reference": "PRV-SAMPLE123",
    "policy_number": "POL-20261023-XYZ",
    "status": "CONFIRMED"
  }' | jq .

# PolicyFlow API PowerShell Examples

$baseUrl = "http://127.0.0.1:8000"

Write-Host "`n=== 1. Intake: Create New Policy Request (Expected: 201 Created) ===" -ForegroundColor Cyan
$body1 = @{
    client_request_id = "AGT-2026-0001"
    agent_id          = "AGENT-17"
    customer_name     = "Sample Customer"
    date_of_birth     = "1998-04-10"
    destination       = "INTERNATIONAL"
    trip_start        = "2026-11-10"
    trip_end          = "2026-11-14"
    plan_code         = "PLUS"
    sum_insured       = 300000
} | ConvertTo-Json

$resp1 = Invoke-RestMethod -Uri "$baseUrl/api/policy-requests" -Method Post -Body $body1 -ContentType "application/json"
$resp1 | ConvertTo-Json -Depth 5

Write-Host "`n=== 2. Intake: Repeated Submission (Idempotent: 200 OK) ===" -ForegroundColor Cyan
$resp2 = Invoke-RestMethod -Uri "$baseUrl/api/policy-requests" -Method Post -Body $body1 -ContentType "application/json"
$resp2 | ConvertTo-Json -Depth 5

Write-Host "`n=== 3. Submit for Issuance (SUCCESS Scenario) ===" -ForegroundColor Cyan
$bodySubmit = @{ scenario = "SUCCESS" } | ConvertTo-Json
$respSubmit = Invoke-RestMethod -Uri "$baseUrl/api/policy-requests/1/submit" -Method Post -Body $bodySubmit -ContentType "application/json"
$respSubmit | ConvertTo-Json -Depth 5

Write-Host "`n=== 4. Get Policy Details & Audit Trail ===" -ForegroundColor Cyan
$respDetails = Invoke-RestMethod -Uri "$baseUrl/api/policy-requests/1" -Method Get
$respDetails | ConvertTo-Json -Depth 5

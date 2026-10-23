<!DOCTYPE html>
<html>
<head>
    <title>PolicyFlow Assignment</title>
</head>
<body>
    <h2>PolicyFlow Intake Form</h2>
    <p>Student practical assessment submission.</p>

    <form method="POST" action="/api/policy-requests">
        <p>Agent ID: <input type="text" name="agent_id" value="AGENT-1"></p>
        <p>Client Request ID: <input type="text" name="client_request_id" value="REQ-001"></p>
        <p>Customer Name: <input type="text" name="customer_name" value="Test Customer"></p>
        <p>Date of Birth: <input type="date" name="date_of_birth" value="1995-01-01"></p>
        <p>Destination: 
            <select name="destination">
                <option value="DOMESTIC">DOMESTIC</option>
                <option value="INTERNATIONAL">INTERNATIONAL</option>
            </select>
        </p>
        <p>Trip Start: <input type="date" name="trip_start" value="2026-11-01"></p>
        <p>Trip End: <input type="date" name="trip_end" value="2026-11-05"></p>
        <p>Plan: 
            <select name="plan_code">
                <option value="BASIC">BASIC</option>
                <option value="PLUS">PLUS</option>
                <option value="PREMIUM">PREMIUM</option>
            </select>
        </p>
        <p>Sum Insured: 
            <select name="sum_insured">
                <option value="100000">100000</option>
                <option value="300000">300000</option>
                <option value="500000">500000</option>
            </select>
        </p>
        <button type="submit">Submit Request</button>
    </form>
</body>
</html>

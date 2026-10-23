<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PolicyFlow - Basic Insurance Engine</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            margin-top: 0;
            font-size: 24px;
        }
        p.subtitle {
            color: #666;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 14px;
        }
        input, select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .row {
            display: flex;
            gap: 15px;
        }
        .col {
            flex: 1;
        }
        button {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background: #1d4ed8;
        }
        .btn-secondary {
            background: #64748b;
        }
        .btn-secondary:hover {
            background: #475569;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            text-align: left;
            font-size: 13px;
        }
        th {
            background: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-VALIDATED { background: #dbeafe; color: #1e40af; }
        .badge-ISSUED { background: #dcfce7; color: #166534; }
        .badge-FAILED { background: #fee2e2; color: #991b1b; }
        .badge-NEEDS_RECONCILIATION { background: #fef3c7; color: #92400e; }
        .alert {
            padding: 10px 15px;
            background: #e0f2fe;
            color: #0369a1;
            border-radius: 4px;
            margin-bottom: 15px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>PolicyFlow - Basic Insurance Engine</h1>
        <p class="subtitle">Campus Placement Practical Assessment (Basic Implementation)</p>

        <div id="alertBox" class="alert"></div>

        <h3>Create Policy Request</h3>
        <form id="policyForm" onsubmit="submitForm(event)">
            <div class="row">
                <div class="col form-group">
                    <label>Agent ID</label>
                    <input type="text" id="agent_id" value="AGENT-17" required>
                </div>
                <div class="col form-group">
                    <label>Client Request ID</label>
                    <input type="text" id="client_request_id" value="AGT-2026-0001" required>
                </div>
            </div>

            <div class="form-group">
                <label>Customer Name</label>
                <input type="text" id="customer_name" value="Sample Customer" required>
            </div>

            <div class="row">
                <div class="col form-group">
                    <label>Date of Birth</label>
                    <input type="date" id="date_of_birth" value="1998-04-10" required>
                </div>
                <div class="col form-group">
                    <label>Destination</label>
                    <select id="destination">
                        <option value="INTERNATIONAL" selected>INTERNATIONAL (1.80x)</option>
                        <option value="DOMESTIC">DOMESTIC (1.00x)</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col form-group">
                    <label>Trip Start</label>
                    <input type="date" id="trip_start" value="2026-11-10" required>
                </div>
                <div class="col form-group">
                    <label>Trip End</label>
                    <input type="date" id="trip_end" value="2026-11-14" required>
                </div>
            </div>

            <div class="row">
                <div class="col form-group">
                    <label>Plan Code</label>
                    <select id="plan_code">
                        <option value="BASIC">BASIC (₹12/day)</option>
                        <option value="PLUS" selected>PLUS (₹20/day)</option>
                        <option value="PREMIUM">PREMIUM (₹30/day)</option>
                    </select>
                </div>
                <div class="col form-group">
                    <label>Sum Insured</label>
                    <select id="sum_insured">
                        <option value="100000">₹100,000 (1.00x)</option>
                        <option value="300000" selected>₹300,000 (1.25x)</option>
                        <option value="500000">₹500,000 (1.50x)</option>
                    </select>
                </div>
            </div>

            <button type="submit">Submit Intake Request</button>
        </form>

        <hr style="margin: 25px 0; border: 0; border-top: 1px solid #e2e8f0;">

        <h3>Policy Requests List</h3>
        <button class="btn-secondary" onclick="fetchPolicies()">Refresh List</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Request ID</th>
                    <th>Agent</th>
                    <th>Customer</th>
                    <th>Days</th>
                    <th>Premium</th>
                    <th>Status</th>
                    <th>Policy #</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="policyTable">
                <tr><td colspan="9" style="text-align: center;">Loading...</td></tr>
            </tbody>
        </table>
    </div>

    <script>
        function showAlert(msg) {
            const box = document.getElementById('alertBox');
            box.textContent = msg;
            box.style.display = 'block';
            setTimeout(() => { box.style.display = 'none'; }, 4000);
        }

        async function submitForm(e) {
            e.preventDefault();
            const data = {
                agent_id: document.getElementById('agent_id').value,
                client_request_id: document.getElementById('client_request_id').value,
                customer_name: document.getElementById('customer_name').value,
                date_of_birth: document.getElementById('date_of_birth').value,
                destination: document.getElementById('destination').value,
                trip_start: document.getElementById('trip_start').value,
                trip_end: document.getElementById('trip_end').value,
                plan_code: document.getElementById('plan_code').value,
                sum_insured: document.getElementById('sum_insured').value
            };

            const res = await fetch('/api/policy-requests', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json();

            if (res.status === 201) {
                showAlert('Created request #' + json.data.id + ' (Premium: ₹' + json.data.calculated_premium + ')');
            } else if (res.status === 200) {
                showAlert('Existing request returned (Idempotent 200 OK)');
            } else {
                showAlert(json.message || 'Error occurred');
            }
            fetchPolicies();
        }

        async function fetchPolicies() {
            const res = await fetch('/api/policy-requests');
            const json = await res.json();
            const list = json.data.data || json.data || [];
            const tbody = document.getElementById('policyTable');

            if (list.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;">No records yet</td></tr>';
                return;
            }

            tbody.innerHTML = list.map(p => `
                <tr>
                    <td>${p.id}</td>
                    <td>${p.client_request_id}</td>
                    <td>${p.agent_id}</td>
                    <td>${p.customer_name}</td>
                    <td>${p.travel_days}</td>
                    <td>₹${p.calculated_premium}</td>
                    <td><span class="badge badge-${p.status}">${p.status}</span></td>
                    <td>${p.policy_number || '—'}</td>
                    <td>
                        ${p.status !== 'ISSUED' ? `<button onclick="submitToInsurer(${p.id})">Issue</button>` : 'Issued'}
                    </td>
                </tr>
            `).join('');
        }

        async function submitToInsurer(id) {
            const res = await fetch('/api/policy-requests/' + id + '/submit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ scenario: 'SUCCESS' })
            });
            const json = await res.json();
            showAlert(json.message || 'Updated');
            fetchPolicies();
        }

        document.addEventListener('DOMContentLoaded', fetchPolicies);
    </script>
</body>
</html>

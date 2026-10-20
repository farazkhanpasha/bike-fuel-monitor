<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History — Bike Fuel Monitor</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background: #0f0f0f;
            color: white;
            min-height: 100vh;
            padding-bottom: 80px;
        }

        .header {
            background: #1a1a1a;
            padding: 15px 20px;
            text-align: center;
            border-bottom: 2px solid #f39c12;
            position: relative;
        }

        .header h1 { color: #f39c12; font-size: 20px; }

        .container {
            padding: 20px;
            max-width: 500px;
            margin: 0 auto;
        }

        .card {
            background: #1a1a1a;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #333;
        }

        .card h2 {
            color: #f39c12;
            margin-bottom: 15px;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Filter buttons */
        .filter-btns {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filter-btn {
            flex: 1;
            padding: 8px;
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            color: #888;
            font-size: 13px;
            cursor: pointer;
            text-align: center;
        }

        .filter-btn.active {
            border-color: #f39c12;
            color: #f39c12;
        }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #1a1a1a;
            border-radius: 12px;
            padding: 15px;
            border: 1px solid #333;
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #f39c12;
        }

        .stat-label {
            font-size: 11px;
            color: #888;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .chart-container {
            position: relative;
            height: 220px;
        }

        /* History table */
        .history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .history-table th {
            text-align: left;
            padding: 10px;
            color: #888;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 1px solid #333;
        }

        .history-table td {
            padding: 10px;
            font-size: 13px;
            border-bottom: 1px solid #1f1f1f;
        }

        .fuel-high { color: #2ecc71; }
        .fuel-medium { color: #f39c12; }
        .fuel-low { color: #e74c3c; }

        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #1a1a1a;
            border-top: 1px solid #333;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
        }

        .nav-item {
            text-align: center;
            text-decoration: none;
            color: #555;
            font-size: 11px;
            flex: 1;
        }

        .nav-item.active { color: #f39c12; }
        .nav-item span { display: block; font-size: 22px; }
    </style>
</head>
<body>

<div class="header">
    <h1>📊 Fuel History</h1>
</div>

<div class="container">

    <!-- Filter Buttons -->
    <div class="filter-btns">
        <div class="filter-btn active" onclick="loadHistory(24)">24 Hours</div>
        <div class="filter-btn" onclick="loadHistory(168)">7 Days</div>
        <div class="filter-btn" onclick="loadHistory(720)">30 Days</div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value" id="avgFuel">--%</div>
            <div class="stat-label">Average Fuel</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="minFuel">--%</div>
            <div class="stat-label">Lowest Level</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="maxFuel">--%</div>
            <div class="stat-label">Highest Level</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="totalReadings">--</div>
            <div class="stat-label">Total Readings</div>
        </div>
    </div>

    <!-- Chart -->
    <div class="card">
        <h2>Fuel Level Chart</h2>
        <div class="chart-container">
            <canvas id="historyChart"></canvas>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <h2>Recent Readings</h2>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Fuel %</th>
                    <th>Liters</th>
                </tr>
            </thead>
            <tbody id="historyTable">
                <tr>
                    <td colspan="3" style="text-align:center; 
                        color:#555">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<!-- Bottom Navigation -->
<div class="bottom-nav">
    <a href="index.php" class="nav-item">
        <span>⛽</span>
        Dashboard
    </a>
    <a href="alerts.php" class="nav-item">
        <span>⚠️</span>
        Alerts
    </a>
    <a href="history.php" class="nav-item active">
        <span>📊</span>
        History
    </a>
    <a href="settings.php" class="nav-item">
        <span>⚙️</span>
        Settings
    </a>
</div>

<script>
    const ctx = document.getElementById('historyChart').getContext('2d');
    const historyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Fuel %',
                data: [],
                borderColor: '#f39c12',
                backgroundColor: 'rgba(243, 156, 18, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    min: 0,
                    max: 100,
                    ticks: { color: '#888' },
                    grid: { color: '#333' }
                },
                x: {
                    ticks: {
                        color: '#888',
                        maxTicksLimit: 6
                    },
                    grid: { color: '#333' }
                }
            },
            plugins: { legend: { display: false } }
        }
    });

    function loadHistory(hours) {
        // Update active filter button
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');

        fetch('../backend/api/get_history.php?hours=' + hours)
            .then(response => response.json())
            .then(data => {
                if (data.readings.length === 0) {
                    document.getElementById('historyTable').innerHTML =
                        '<tr><td colspan="3" style="text-align:center;' +
                        'color:#555;padding:20px">No data yet</td></tr>';
                    return;
                }

                // Calculate stats
                const values = data.readings.map(r => 
                               parseFloat(r.fuel_percentage));
                const avg = (values.reduce((a, b) => a + b, 0) / 
                            values.length).toFixed(1);
                const min = Math.min(...values).toFixed(1);
                const max = Math.max(...values).toFixed(1);

                document.getElementById('avgFuel').textContent = avg + '%';
                document.getElementById('minFuel').textContent = min + '%';
                document.getElementById('maxFuel').textContent = max + '%';
                document.getElementById('totalReadings').textContent = 
                    values.length;

                // Update chart
                const labels = data.readings.map(r => {
                    const d = new Date(r.timestamp);
                    return d.getHours() + ':' + 
                           String(d.getMinutes()).padStart(2, '0');
                });
                historyChart.data.labels = labels;
                historyChart.data.datasets[0].data = values;
                historyChart.update();

                // Update table
                let tableHTML = '';
                data.readings.slice().reverse()
                             .slice(0, 20).forEach(row => {
                    const d = new Date(row.timestamp);
                    const timeStr = d.toLocaleDateString() + ' ' +
                        d.getHours() + ':' + 
                        String(d.getMinutes()).padStart(2, '0');
                    const fuel = parseFloat(row.fuel_percentage);
                    const fuelClass = fuel < 20 ? 'fuel-low' : 
                                     fuel < 50 ? 'fuel-medium' : 'fuel-high';

                    tableHTML += `
                        <tr>
                            <td style="color:#888">${timeStr}</td>
                            <td class="${fuelClass}">${fuel}%</td>
                            <td style="color:#888">
                                ${parseFloat(row.fuel_liters).toFixed(1)}L
                            </td>
                        </tr>`;
                });
                document.getElementById('historyTable').innerHTML = tableHTML;
            });
    }

    loadHistory(24);
</script>

</body>
</html>
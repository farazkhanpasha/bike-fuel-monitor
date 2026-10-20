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
    <title>Bike Fuel Monitor</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #0f0f0f;
            color: white;
            min-height: 100vh;
        }

        .header {
    background: #1a1a1a;
    padding: 15px 20px;
    text-align: center;
    border-bottom: 2px solid #f39c12;
    position: relative;
}

        .header h1 {
            color: #f39c12;
            font-size: 22px;
        }

        .container {
            padding: 20px;
            max-width: 500px;
            margin: 0 auto;
        }

        /* Fuel Gauge Card */
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

        /* Gauge */
        .gauge-container {
            text-align: center;
            padding: 20px 0;
        }

        .gauge-circle {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: conic-gradient(
                #f39c12 0deg,
                #f39c12 var(--fuel-angle),
                #333 var(--fuel-angle),
                #333 360deg
            );
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            position: relative;
        }

        .gauge-inner {
            width: 130px;
            height: 130px;
            background: #1a1a1a;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .fuel-percentage {
            font-size: 36px;
            font-weight: bold;
            color: #f39c12;
        }

        .fuel-liters {
            font-size: 14px;
            color: #888;
        }

        /* Status bar */
        .status-bar {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .status-item {
            text-align: center;
            flex: 1;
        }

        .status-value {
            font-size: 18px;
            font-weight: bold;
            color: #f39c12;
        }

        .status-label {
            font-size: 11px;
            color: #888;
            margin-top: 3px;
        }

        /* Alert */
        .alert-box {
            background: #c0392b;
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 20px;
            display: none;
            align-items: center;
            gap: 10px;
        }

        .alert-box.show {
            display: flex;
        }

        .alert-icon {
            font-size: 24px;
        }

        .alert-text {
            font-size: 14px;
        }

        /* Chart */
        .chart-container {
            position: relative;
            height: 200px;
        }

        /* Last updated */
        .last-updated {
            text-align: center;
            color: #555;
            font-size: 12px;
            margin-top: 10px;
        }

        /* Theft alert */
        .theft-alert {
            background: #8e44ad;
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 20px;
            display: none;
        }

        .theft-alert.show {
            display: block;
        }
        
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

body { padding-bottom: 80px; }

    </style>
</head>
<body>

<div class="header">
    <h1>⛽ Bike Fuel Monitor</h1>
    <a href="../backend/auth/logout.php" style="
        position: absolute;
        right: 15px;
        top: 15px;
        color: #f39c12;
        text-decoration: none;
        font-size: 13px;
        border: 1px solid #f39c12;
        padding: 4px 10px;
        border-radius: 5px;
    ">Logout</a>
</div>

<div class="container">

    <!-- Low Fuel Alert -->
    <div class="alert-box" id="lowFuelAlert">
        <span class="alert-icon">⚠️</span>
        <div class="alert-text">
            <strong>Low Fuel Warning!</strong><br>
            Fuel level is critically low. Refuel soon.
        </div>
    </div>

    <!-- Theft Alert -->
    <div class="theft-alert" id="theftAlert">
        ⚠️ <strong>Theft Alert!</strong> Sudden fuel drop detected.
    </div>

    <!-- Fuel Gauge Card -->
    <div class="card">
        <h2>Current Fuel Level</h2>
        <div class="gauge-container">
            <div class="gauge-circle" id="gaugeCircle" style="--fuel-angle: 0deg">
                <div class="gauge-inner">
                    <div class="fuel-percentage" id="fuelPercentage">--%</div>
                    <div class="fuel-liters" id="fuelLiters">-- L</div>
                </div>
            </div>
        </div>
       <div class="status-bar">
    <div class="status-item">
        <div class="status-value" id="statusValue">--</div>
        <div class="status-label">Status</div>
    </div>
    <div class="status-item">
        <div class="status-value" id="rangeKm">-- KM</div>
        <div class="status-label">Est. Range</div>
    </div>
    <div class="status-item">
        <div class="status-value" id="lastTime">--</div>
        <div class="status-label">Updated</div>
    </div>
</div>
    </div>

    <!-- History Chart Card -->
    <div class="card">
        <h2>Fuel History</h2>
        <div class="chart-container">
            <canvas id="fuelChart"></canvas>
        </div>
    </div>

    <div class="last-updated" id="lastUpdated">Connecting...</div>

</div>

<script>
    // Initialize Chart
    const ctx = document.getElementById('fuelChart').getContext('2d');
    const fuelChart = new Chart(ctx, {
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
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Update dashboard
    function updateDashboard() {
        fetch('../backend/api/get_fuel.php')
            .then(response => response.json())
            .then(data => {
                if (!data.current) return;

                const fuel = parseFloat(data.current.fuel_percentage);
                const liters = parseFloat(data.current.fuel_liters).toFixed(1);

                // Update gauge
                document.getElementById('fuelPercentage').textContent = fuel + '%';
                document.getElementById('fuelLiters').textContent = liters + ' L';

                // Update gauge circle color
                const angle = (fuel / 100) * 360;
                const color = fuel < 20 ? '#e74c3c' : fuel < 50 ? '#f39c12' : '#2ecc71';
                document.getElementById('gaugeCircle').style.background = 
                    `conic-gradient(${color} 0deg, ${color} ${angle}deg, #333 ${angle}deg, #333 360deg)`;

                // Update status
                let status = fuel < 20 ? 'Critical' : fuel < 50 ? 'Low' : 'Good';
                document.getElementById('statusValue').textContent = status;
                document.getElementById('statusValue').style.color = 
                    fuel < 20 ? '#e74c3c' : fuel < 50 ? '#f39c12' : '#2ecc71';

                // Update time
                const time = new Date(data.current.timestamp);
                document.getElementById('lastTime').textContent = 
                    time.getHours() + ':' + String(time.getMinutes()).padStart(2, '0');
                    // Calculate range from settings
const settings = JSON.parse(
    localStorage.getItem('bikeSettings') || '{}'
);
const mileage = settings.mileage || 40;
const tankSize = settings.tankSize || 15;
const estimatedLiters = (fuel / 100) * tankSize;
const range = (estimatedLiters * mileage).toFixed(0);
document.getElementById('rangeKm').textContent = range + ' KM';

                // Show low fuel alert
                if (fuel < 20) {
                    document.getElementById('lowFuelAlert').classList.add('show');
                } else {
                    document.getElementById('lowFuelAlert').classList.remove('show');
                }

                // Check theft alerts
                if (data.alerts && data.alerts.length > 0) {
                    data.alerts.forEach(alert => {
                        if (alert.type === 'theft') {
                            document.getElementById('theftAlert').classList.add('show');
                        }
                    });
                }

                // Update chart
                if (data.history && data.history.length > 0) {
                    const labels = data.history.map(row => {
                        const d = new Date(row.timestamp);
                        return d.getHours() + ':' + String(d.getMinutes()).padStart(2, '0');
                    });
                    const values = data.history.map(row => row.fuel_percentage);
                    fuelChart.data.labels = labels;
                    fuelChart.data.datasets[0].data = values;
                    fuelChart.update();
                }

                // Last updated
                document.getElementById('lastUpdated').textContent = 
                    'Last updated: ' + new Date().toLocaleTimeString();
            })
            .catch(err => {
                document.getElementById('lastUpdated').textContent = 'Connection error...';
            });
    }

    // Run immediately then every 10 seconds
    updateDashboard();
    setInterval(updateDashboard, 10000);
</script>
<!-- Bottom Navigation -->
<div class="bottom-nav">
    <a href="index.php" class="nav-item active">
        <span>⛽</span>
        Dashboard
    </a>
    <a href="alerts.php" class="nav-item">
        <span>⚠️</span>
        Alerts
    </a>
    <a href="history.php" class="nav-item">
        <span>📊</span>
        History
    </a>
    <a href="settings.php" class="nav-item">
        <span>⚙️</span>
        Settings
    </a>
</div>

</body>
</html>
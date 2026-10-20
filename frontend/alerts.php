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
    <title>Alerts — Bike Fuel Monitor</title>
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

        .back-btn {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #f39c12;
            text-decoration: none;
            font-size: 13px;
            border: 1px solid #f39c12;
            padding: 4px 10px;
            border-radius: 5px;
        }

        .container {
            padding: 20px;
            max-width: 500px;
            margin: 0 auto;
        }

        .alert-item {
            background: #1a1a1a;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 12px;
            border-left: 4px solid #333;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .alert-item.low_fuel {
            border-left-color: #e74c3c;
        }

        .alert-item.theft {
            border-left-color: #8e44ad;
        }

        .alert-icon { font-size: 24px; }

        .alert-content { flex: 1; }

        .alert-type {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .low_fuel .alert-type { color: #e74c3c; }
        .theft .alert-type { color: #8e44ad; }

        .alert-message {
            font-size: 14px;
            color: #ddd;
            margin-bottom: 6px;
        }

        .alert-time {
            font-size: 11px;
            color: #555;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #555;
        }

        .empty-state span {
            font-size: 48px;
            display: block;
            margin-bottom: 15px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #555;
        }

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
    <a href="index.php" class="back-btn">← Back</a>
    <h1>⚠️ Alerts</h1>
</div>

<div class="container">
    <div id="alertsList">
        <div class="loading">Loading alerts...</div>
    </div>
</div>

<!-- Bottom Navigation -->
<div class="bottom-nav">
    <a href="index.php" class="nav-item">
        <span>⛽</span>
        Dashboard
    </a>
    <a href="alerts.php" class="nav-item active">
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

<script>
function loadAlerts() {
    fetch('../backend/api/get_alerts.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('alertsList');

            if (data.alerts.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <span>✅</span>
                        No alerts. Everything is fine.
                    </div>`;
                return;
            }

            let html = '';
            data.alerts.forEach(alert => {
                const date = new Date(alert.timestamp);
                const timeStr = date.toLocaleDateString() + ' ' + 
                                date.toLocaleTimeString();
                const icon = alert.type === 'theft' ? '🔴' : '⚠️';
                const typeLabel = alert.type === 'theft' ? 
                                  'Theft Detection' : 'Low Fuel';

                html += `
                    <div class="alert-item ${alert.type}">
                        <div class="alert-icon">${icon}</div>
                        <div class="alert-content">
                            <div class="alert-type">${typeLabel}</div>
                            <div class="alert-message">${alert.message}</div>
                            <div class="alert-time">${timeStr}</div>
                        </div>
                    </div>`;
            });

            container.innerHTML = html;
        });
}

loadAlerts();
</script>

</body>
</html>
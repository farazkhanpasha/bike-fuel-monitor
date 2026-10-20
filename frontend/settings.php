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
    <title>Settings — Bike Fuel Monitor</title>
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
            margin-bottom: 20px;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            color: #888;
            font-size: 12px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            background: #0f0f0f;
            border: 1px solid #333;
            border-radius: 8px;
            color: white;
            font-size: 15px;
            outline: none;
        }

        .form-group input:focus {
            border-color: #f39c12;
        }

        .form-group .hint {
            font-size: 11px;
            color: #555;
            margin-top: 5px;
        }

        .btn-save {
            width: 100%;
            padding: 13px;
            background: #f39c12;
            border: none;
            border-radius: 8px;
            color: #0f0f0f;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .success-box {
            background: #27ae60;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
            text-align: center;
        }

        .success-box.show { display: block; }

        .bike-info {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #333;
            margin-bottom: 15px;
        }

        .bike-icon { font-size: 40px; }

        .bike-details h3 {
            font-size: 16px;
            color: white;
        }

        .bike-details p {
            font-size: 13px;
            color: #888;
            margin-top: 3px;
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
    </style>
</head>
<body>

<div class="header">
    <h1>⚙️ Settings</h1>
</div>

<div class="container">

    <div class="success-box" id="successBox">
        ✓ Settings saved successfully
    </div>

    <!-- Bike Info -->
    <div class="card">
        <h2>Bike Information</h2>
        <div class="bike-info">
            <div class="bike-icon">🏍️</div>
            <div class="bike-details">
                <h3 id="bikeNameDisplay">My Bike</h3>
                <p id="bikeTankDisplay">Tank: 15L</p>
            </div>
        </div>

        <div class="form-group">
            <label>Bike Name</label>
            <input type="text" id="bikeName" 
                   placeholder="e.g. Honda CD 70">
        </div>

        <div class="form-group">
            <label>Tank Size (Liters)</label>
            <input type="number" id="tankSize" 
                   placeholder="e.g. 15" min="1" max="50">
            <div class="hint">
                Used to calculate fuel in liters from percentage
            </div>
        </div>

        <div class="form-group">
            <label>Fuel Mileage (KM per Liter)</label>
            <input type="number" id="mileage" 
                   placeholder="e.g. 40" min="1" max="100">
            <div class="hint">
                Used to estimate how far you can travel
            </div>
        </div>
    </div>

    <!-- Alert Settings -->
    <div class="card">
        <h2>Alert Settings</h2>

        <div class="form-group">
            <label>Low Fuel Alert Threshold (%)</label>
            <input type="number" id="lowFuelThreshold" 
                   placeholder="e.g. 20" min="5" max="50">
            <div class="hint">
                Get alerted when fuel drops below this level
            </div>
        </div>

        <div class="form-group">
            <label>Theft Detection Sensitivity (%)</label>
            <input type="number" id="theftThreshold" 
                   placeholder="e.g. 15" min="5" max="50">
            <div class="hint">
                Alert if fuel drops more than this % suddenly
            </div>
        </div>
    </div>

    <!-- Range Estimator -->
    <div class="card">
        <h2>Range Estimator</h2>
        <div class="form-group">
            <label>Current Fuel Level</label>
            <input type="number" id="currentFuel" 
                   placeholder="Enter %" min="0" max="100"
                   oninput="calculateRange()">
        </div>
        <div style="text-align:center; padding: 15px 0;">
            <div style="font-size: 36px; 
                        font-weight: bold; 
                        color: #f39c12" id="rangeKm">-- KM</div>
            <div style="font-size: 13px; 
                        color: #888; 
                        margin-top: 5px">Estimated range</div>
        </div>
    </div>

    <button class="btn-save" onclick="saveSettings()">
        Save Settings
    </button>

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
    <a href="history.php" class="nav-item">
        <span>📊</span>
        History
    </a>
    <a href="settings.php" class="nav-item active">
        <span>⚙️</span>
        Settings
    </a>
</div>

<script>
    // Load saved settings
    function loadSettings() {
        const settings = JSON.parse(
            localStorage.getItem('bikeSettings') || '{}'
        );
        if (settings.bikeName) {
            document.getElementById('bikeName').value = settings.bikeName;
            document.getElementById('bikeNameDisplay').textContent = 
                settings.bikeName;
        }
        if (settings.tankSize) {
            document.getElementById('tankSize').value = settings.tankSize;
            document.getElementById('bikeTankDisplay').textContent = 
                'Tank: ' + settings.tankSize + 'L';
        }
        if (settings.mileage)
            document.getElementById('mileage').value = settings.mileage;
        if (settings.lowFuelThreshold)
            document.getElementById('lowFuelThreshold').value = 
                settings.lowFuelThreshold;
        if (settings.theftThreshold)
            document.getElementById('theftThreshold').value = 
                settings.theftThreshold;
    }

    // Save settings
    function saveSettings() {
        const settings = {
            bikeName: document.getElementById('bikeName').value 
                      || 'My Bike',
            tankSize: document.getElementById('tankSize').value 
                      || 15,
            mileage: document.getElementById('mileage').value 
                     || 40,
            lowFuelThreshold: document.getElementById('lowFuelThreshold')
                              .value || 20,
            theftThreshold: document.getElementById('theftThreshold')
                            .value || 15
        };

        localStorage.setItem('bikeSettings', JSON.stringify(settings));

        document.getElementById('bikeNameDisplay').textContent = 
            settings.bikeName;
        document.getElementById('bikeTankDisplay').textContent = 
            'Tank: ' + settings.tankSize + 'L';

        const successBox = document.getElementById('successBox');
        successBox.classList.add('show');
        setTimeout(() => successBox.classList.remove('show'), 3000);
    }

    // Calculate range
    function calculateRange() {
        const settings = JSON.parse(
            localStorage.getItem('bikeSettings') || '{}'
        );
        const mileage = settings.mileage || 40;
        const tankSize = settings.tankSize || 15;
        const fuelPercent = document.getElementById('currentFuel').value;

        if (fuelPercent) {
            const liters = (fuelPercent / 100) * tankSize;
            const range = (liters * mileage).toFixed(0);
            document.getElementById('rangeKm').textContent = range + ' KM';
        }
    }

    loadSettings();
</script>

</body>
</html>
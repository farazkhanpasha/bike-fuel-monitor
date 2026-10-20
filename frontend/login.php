<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bike Fuel Monitor — Login</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 380px;
            padding: 20px;
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo h1 {
            color: #f39c12;
            font-size: 28px;
        }

        .logo p {
            color: #888;
            font-size: 14px;
            margin-top: 5px;
        }

        .card {
            background: #1a1a1a;
            border-radius: 15px;
            padding: 30px;
            border: 1px solid #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #888;
            font-size: 13px;
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
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            border-color: #f39c12;
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: #f39c12;
            border: none;
            border-radius: 8px;
            color: #0f0f0f;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-login:hover {
            background: #e67e22;
        }

        .error-box {
            background: #c0392b;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .error-box.show {
            display: block;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo">
        <h1>⛽ FuelTrack</h1>
        <p>Bike Fuel Monitoring System</p>
    </div>

    <div class="card">
        <div class="error-box" id="errorBox"></div>

        <div class="form-group">
            <label>Username</label>
            <input type="text" id="username" placeholder="Enter username">
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" id="password" placeholder="Enter password">
        </div>

        <button class="btn-login" onclick="doLogin()">Login</button>
    </div>
</div>

<script>
function doLogin() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    if (!username || !password) {
        showError('Please enter username and password');
        return;
    }

    fetch('../backend/auth/login.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'username=' + username + '&password=' + password
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            window.location.href = 'index.php';
        } else {
            showError(data.message);
        }
    });
}

function showError(message) {
    const errorBox = document.getElementById('errorBox');
    errorBox.textContent = message;
    errorBox.classList.add('show');
}

// Login on Enter key
document.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') doLogin();
});
</script>

</body>
</html>
<?php
include '../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get latest reading
$latest = $conn->query("SELECT * FROM fuel_readings 
                        ORDER BY timestamp DESC LIMIT 1")->fetch_assoc();

// Get last 7 days history
$historyResult = $conn->query("SELECT fuel_percentage, timestamp 
                         FROM fuel_readings 
                         WHERE timestamp >= NOW() - INTERVAL 7 DAY 
                         ORDER BY timestamp ASC");

$historyData = [];
while ($row = $historyResult->fetch_assoc()) {
    $historyData[] = $row;
}

// Get unread alerts
$alertsResult = $conn->query("SELECT * FROM alerts 
                              WHERE is_read = 0 
                              ORDER BY timestamp DESC");

$alerts = [];
while ($row = $alertsResult->fetch_assoc()) {
    $alerts[] = $row;
}

echo json_encode([
    'current' => $latest,
    'history' => $historyData,
    'alerts' => $alerts
]);
?>
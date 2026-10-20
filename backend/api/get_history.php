<?php
include '../config/db.php';

header('Content-Type: application/json');

$hours = isset($_GET['hours']) ? intval($_GET['hours']) : 24;

$result = $conn->query("SELECT fuel_percentage, fuel_liters, timestamp 
                        FROM fuel_readings 
                        WHERE timestamp >= NOW() - INTERVAL {$hours} HOUR
                        ORDER BY timestamp ASC");

$readings = [];
while ($row = $result->fetch_assoc()) {
    $readings[] = $row;
}

echo json_encode(['readings' => $readings]);
?>
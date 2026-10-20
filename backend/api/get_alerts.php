<?php
session_start();
include '../config/db.php';

header('Content-Type: application/json');

// Get all alerts
$result = $conn->query("SELECT * FROM alerts 
                        ORDER BY timestamp DESC");

$alerts = [];
while ($row = $result->fetch_assoc()) {
    $alerts[] = $row;
}

// Mark all as read
$conn->query("UPDATE alerts SET is_read = 1");

echo json_encode(['alerts' => $alerts]);
?>
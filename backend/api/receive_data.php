<?php
include '../config/db.php';

if (isset($_POST['fuel_percentage'])) {
    $fuel = floatval($_POST['fuel_percentage']);
    $liters = ($fuel / 100) * 15;
    
    $stmt = $conn->prepare("INSERT INTO fuel_readings 
                           (fuel_percentage, fuel_liters) VALUES (?, ?)");
    $stmt->bind_param("dd", $fuel, $liters);
    
    if ($stmt->execute()) {
        // Auto generate low fuel alert
        if ($fuel < 20) {
            $message = "Fuel level critical: " . $fuel . "%";
            $stmt2 = $conn->prepare("INSERT INTO alerts 
                                    (type, message) VALUES ('low_fuel', ?)");
            $stmt2->bind_param("s", $message);
            $stmt2->execute();
        }
        echo "OK";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Receiving theft alert from NodeMCU
if (isset($_POST['alert_type'])) {
    $type = $_POST['alert_type'];
    $message = $_POST['alert_message'];
    
    $stmt = $conn->prepare("INSERT INTO alerts 
                           (type, message) VALUES (?, ?)");
    $stmt->bind_param("ss", $type, $message);
    $stmt->execute();
    echo "Alert saved";
}
?>
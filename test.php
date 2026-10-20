<?php
$url = "http://localhost/bike-fuel-monitor/backend/api/receive_data.php";

$data = array('fuel_percentage' => 80.0);

$options = array(
    'http' => array(
        'method' => 'POST',
        'header' => 'Content-type: application/x-www-form-urlencoded',
        'content' => http_build_query($data)
    )
);

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "Server response: " . $result;
?>
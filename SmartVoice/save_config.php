<?php
// save_config.php

header('Content-Type: application/json');

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!$data || !isset($data['mirror_id']) || !isset($data['config'])) {
    http_response_code(400);
    echo json_encode(["error" => "Neplatné údaje"]);
    exit();
}

$mirror_id = $data['mirror_id'];
$config = json_encode($data['config']);

// ✅ Tu si nastav vlastné pripojenie k DB
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "smartvoice_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Chyba DB pripojenia"]);
    exit();
}

$sql = "INSERT INTO mirror_configs (mirror_id, config) VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE config = VALUES(config)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $mirror_id, $config);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Chyba pri ukladaní do DB"]);
}

$stmt->close();
$conn->close();
?>
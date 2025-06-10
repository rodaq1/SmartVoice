<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['mirror_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

$mirror_id = $_SESSION['mirror_id'];
$rawInput = file_get_contents("php://input");
$newData = json_decode(file_get_contents("php://input"), true);

if (!$newData) {
    if (!empty($_POST)) {
        $newData = $_POST;
    } else {
        http_response_code(400);
        echo "Bad Request";
        exit();
    }
}

$stmt = $conn->prepare("SELECT config FROM mirror_configs WHERE mirror_id = ?");
$stmt->bind_param("s", $mirror_id);
$stmt->execute();
$result = $stmt->get_result();

$oldConfig = [];
if ($result && $row = $result->fetch_assoc()) {
    $oldConfig = json_decode($row['config'], true);
    if (!is_array($oldConfig)) {
        $oldConfig = [];
    }
}

$mergedConfig = array_merge($oldConfig, $newData);

$configJson = json_encode($mergedConfig);

$updateStmt = $conn->prepare("UPDATE mirror_configs SET config = ? WHERE mirror_id = ?");
$updateStmt->bind_param("ss", $configJson, $mirror_id);

if ($updateStmt->execute()) {
    header('Location: speakersetting.php');
    
} else {
    http_response_code(500);
    echo "Failed to update";
}

require('phpMQTT.php'); 

$server = '192.168.0.201';  
$port = 1883;                  
$username = '';       
$password = '';       
$client_id = 'php-mqtt-' . uniqid();

$mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);

if ($mqtt->connect(true, NULL, $username, $password)) {
    $topic = "smartvoice/mirror/$mirror_id/config";
    $mqtt->publish($topic, $configJson, 0);  
    $mqtt->close();
} else {
    http_response_code(500);
    echo "Failed to connect to MQTT broker";
}

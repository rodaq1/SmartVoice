<?php
session_start();

if (!isset($_POST['code']) || !isset($_SESSION['user_id'])) {
  http_response_code(400);
  echo "Chýbajúci kód alebo používateľ.";
  exit;
}

$code = $_POST['code'];
$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "admin", "admin", "smartvoice_db");
if ($conn->connect_error) {
  http_response_code(500);
  echo "Chyba DB pripojenia.";
  exit;
}

$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  http_response_code(404);
  echo "Používateľ neexistuje.";
  exit;
}

$row = $result->fetch_assoc();
$name = $row['name'];
$conn->close();

$payload = json_encode([
  "code" => (int)$code,
  "user" => $name
]);

require('phpMQTT.php');

    $server = '192.168.0.201';      
    $port = 1883;             
    $client_id = uniqid();      

    $mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);
    if ($mqtt->connect(true, NULL, '', '')) {
      $topic = 'smartvoice/pair';
      
      $mqtt->publish($topic, $payload, 0); 
      $mqtt->close();
  } else {
      error_log("❌ Nepodarilo sa pripojiť na MQTT broker.");
  }



if ($return_var === 0) {
  echo "OK";
} else {
  http_response_code(500);
  echo "Zlyhalo odosielanie na MQTT broker.";
}
?>

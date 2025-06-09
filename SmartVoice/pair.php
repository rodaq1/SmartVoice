<?php
session_start();

if (!isset($_POST['code']) || !isset($_SESSION['user_id'])) {
  http_response_code(400);
  echo "Chýbajúci kód alebo používateľ.";
  exit;
}

$code = $_POST['code'];
$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "smartvoice_db");
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

$topic = "smartvoice/pair";
$cmd = "mosquitto_pub -h localhost -t " . escapeshellarg($topic) . " -m " . '"' . addcslashes($payload, '"\\') . '"';


exec($cmd, $output, $return_var);


if ($return_var === 0) {
  echo "OK";
} else {
  http_response_code(500);
  echo "Zlyhalo odosielanie na MQTT broker.";
}
?>

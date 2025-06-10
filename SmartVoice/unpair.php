<?php 
session_start(); // Dôležité pre prístup k $_SESSION

if (!isset($_SESSION['user_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Neprebiehajúce prihlásenie."]);
    exit;
}

$mysqli = new mysqli("localhost", "admin", "admin", "smartvoice_db");

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Chyba pripojenia k DB."]);
    exit;
}

$user_id = intval($_SESSION['user_id']);

$stmt = $mysqli->prepare("DELETE FROM devices WHERE user_id = ?");
$stmt->bind_param("i", $user_id); 
$success = $stmt->execute();

if (!$success) {
    http_response_code(500);
    echo json_encode(["error" => "Zlyhalo odstránenie zariadení."]);
    exit;
}

echo json_encode(["success" => true, "deleted_rows" => $stmt->affected_rows]);

header('Location: account.php');
?>

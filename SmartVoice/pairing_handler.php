<?php
header("Content-Type: application/json");

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!isset($data["mirror_id"], $data["username"], $data["status"]) || $data["status"] !== "paired") {
    http_response_code(400);
    echo json_encode(["error" => "Neplatné dáta."]);
    exit;
}

$mirror_id = $data["mirror_id"];
$username = $data["username"];

$mysqli = new mysqli("localhost", "admin", "admin", "smartvoice_db");
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Chyba pripojenia k DB."]);
    exit;
}

$stmt = $mysqli->prepare("SELECT id FROM users WHERE name = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Používateľ neexistuje."]);
    exit;
}

$user = $result->fetch_assoc();
$user_id = $user["id"];

$stmt = $mysqli->prepare("INSERT INTO devices (mirror_id, username, user_id, paired_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("ssi", $mirror_id, $username, $user_id);
$stmt->execute();

echo json_encode(["success" => true]);

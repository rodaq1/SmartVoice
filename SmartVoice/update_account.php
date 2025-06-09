<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'smartvoice_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_POST['email'] ?? '';
$name = $_POST['name'] ?? '';
$status = $_POST['status'] ?? 'prihlaseny';

$email = $conn->real_escape_string(trim($email));
$name = $conn->real_escape_string(trim($name));
$status = $conn->real_escape_string(trim($status));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Neplatný formát emailu.");
}

if (empty($name)) {
    die("Meno nemôže byť prázdne.");
}

$user_id = intval($_SESSION['user_id']);
$sql = "UPDATE users SET emailOrPhone = '$email', name = '$name' WHERE id = $user_id";

if ($conn->query($sql) === TRUE) {
    $_SESSION['emailOrPhone'] = $email;

    // 🟢 MQTT PART STARTS HERE
    require('phpMQTT.php'); // cesta podľa tvojho umiestnenia

    $server = 'localhost';      // MQTT broker host (napr. 'localhost' alebo IP)
    $port = 1883;               // Port (default 1883)
    $client_id = uniqid();      // Unikátne ID klienta

    $mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);

    if ($mqtt->connect(true, NULL, '', '')) {
        $topic = 'smartvoice/mirror/user';
        
        // Vytvorenie PHP asociatívneho poľa pre JSON
        $data = ['user' => $name]; 
        
        // Zakódovanie poľa do JSON reťazca
        $message = json_encode($data); 
        
        // Publikovanie JSON reťazca
        $mqtt->publish($topic, $message, 0); 
        $mqtt->close();
    } else {
        error_log("❌ Nepodarilo sa pripojiť na MQTT broker.");
    }
    // 🔴 MQTT PART ENDS HERE

    header('Location: account.php');
    exit();
} else {
    echo "Chyba pri aktualizácii: " . $conn->error;
}

$conn->close();
?>

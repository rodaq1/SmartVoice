<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'smartvoice_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['emailOrPhone'] ?? '';
    $password = $_POST['password'] ?? '';

    $email = $conn->real_escape_string($email);

    $sql = "SELECT id, password FROM users WHERE emailOrPhone = '$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['emailOrPhone'] = $email;

            header('Location: pair.html');
            exit();
        } else {
            $error = "Nesprávne heslo.";
        }
    } else {
        $error = "Používateľ s týmto emailom neexistuje.";
    }
} else {
    $error = "Neplatný prístup.";
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
  <meta charset="UTF-8" />
  <title>Prihlásenie – SmartVoice</title>
  <style>
    body { background: #101010; color: white; font-family: Arial, sans-serif; display: flex; height: 100vh; justify-content: center; align-items: center; }
    .error-box { background: #600; padding: 1rem 2rem; border-radius: 8px; }
    a { color: #ffce00; }
  </style>
</head>
<body>
  <div class="error-box">
    <h2>Chyba pri prihlásení</h2>
    <p><?= htmlspecialchars($error) ?></p>
    <p><a href="login.html">Skúste to znova</a></p>
  </div>
</body>
</html>

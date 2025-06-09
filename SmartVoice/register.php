<?php
// DB credentials - adjust these to your setup
$servername = "localhost";
$username = "root";
$password = "";  // default XAMPP root password is empty
$dbname = "smartvoice_db";  // create this database manually in phpMyAdmin

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Pripojenie zlyhalo: " . $conn->connect_error);
}

// Get POST data and sanitize
$emailOrPhone = $conn->real_escape_string($_POST['emailOrPhone']);
$password1 = $_POST['password'];
$password2 = $_POST['passwordRepeat'];

// Simple validation
if (empty($emailOrPhone) || empty($password1) || empty($password2)) {
    die("Prosím vyplňte všetky polia.");
}

if ($password1 !== $password2) {
    die("Heslá sa nezhodujú.");
}

// Hash password securely
$hashedPassword = password_hash($password1, PASSWORD_DEFAULT);

// Check if user already exists
$sqlCheck = "SELECT * FROM users WHERE emailOrPhone='$emailOrPhone'";
$result = $conn->query($sqlCheck);
if ($result->num_rows > 0) {
    die("Používateľ už existuje.");
}

// Insert new user
$sqlInsert = "INSERT INTO users (emailOrPhone, password) VALUES ('$emailOrPhone', '$hashedPassword')";

if ($conn->query($sqlInsert) === TRUE) {
    echo "Registrácia bola úspešná! <a href='login.html'>Prihlásiť sa</a>";
} else {
    echo "Chyba: " . $conn->error;
}

$conn->close();
?>

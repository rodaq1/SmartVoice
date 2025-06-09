<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.html'); // ak nie je prihlásený, presmeruj
  exit();
}

// Pripojenie k DB (upravi podľa tvojich údajov)
$conn = new mysqli('localhost', 'root', '', 'smartvoice_db');
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$user_id = intval($_SESSION['user_id']);
$sql = "SELECT emailOrPhone, name FROM users WHERE id = $user_id LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows === 1) {
  $user = $result->fetch_assoc();
} else {
  // Ak sa nepodarí načítať používateľa, presmeruj na login alebo chybu
  header('Location: login.html');
  exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="sk">

<head>
  <meta charset="UTF-8" />
  <title>Účet – SmartVoice</title>
  <style>
    body {
      background-color: #101010;
      color: white;
      font-family: "Istok Web", sans-serif;
      margin: 0;
      padding: 2rem;
    }

    .settings-container {
      max-width: 600px;
      margin: 8vh auto;
      padding: 2rem;
      background: #191919;
      border-radius: 15px;
      box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
    }

    .settings-container h1 {
      text-align: center;
      margin-bottom: 2rem;
      color: lightgray;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }

    label {
      font-weight: bold;
    }

    input[type="text"],
    select {
      padding: 10px;
      border-radius: 8px;
      border: none;
      background: #2c2c2c;
      color: white;
    }

    input[type="range"] {
      width: 100%;
    }

    button {
      padding: 10px;
      background: lightgray;
      color: black;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    button:hover {
      background: #e0b800;
    }

    /* Placeholder navbar and footer styles */
    nav.navbar-placeholder,
    footer.footer-placeholder {
      background-color: #191919;
      color: lightgray;
      padding: 1rem 2rem;
      text-align: center;
      font-weight: bold;
      user-select: none;
    }
  </style>
</head>

<body>
  <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>

  <nav id="navbar-placeholder"></nav>

  <div class="settings-container">
    <h1>Účet používateľa</h1>
    <form action="update_account.php" method="post">
      <label for="email">Email:</label>
      <input type="text" id="email" name="email" value="<?= htmlspecialchars($user['emailOrPhone']) ?>" required />

      <label for="name">Meno:</label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required />

      <label for="status">Status:</label>
      <select id="status" name="status" required>
        <option value="prihlaseny">Prihlásený</option>
        <option value="main character syndrome">Nerušiť</option>
        <option value="online">Online</option>
      </select>

      <button type="submit">Uložiť zmeny</button>
    </form>
    <form action="unpair.php" id="unpairForm" method="post">
      <button type="submit" id="unpairBtn" style="background:#b22222; color:white; margin-top: 1rem;">Odstrániť
        zariadenie</button>
    </form>

    <form action="logout.php" method="post" style="margin-top: 1.5rem;">
      <button type="submit">Odhlásiť sa</button>
    </form>
  </div>
  <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
  <script>
    const brokerUrl = 'ws://localhost:9001';

    const client = mqtt.connect(brokerUrl);

    client.on('connect', () => {
      console.log('MQTT pripojený');
    });

    client.on('error', (err) => {
      console.error('MQTT chyba:', err);
    });

    document.getElementById('unpairBtn').addEventListener('click', () => {
      if (client.connected) {
        const payload = JSON.stringify({ unpair: true });
        client.publish('smartvoice/unpair', payload, {}, (err) => {
          if (err) {
            alert('Nepodarilo sa odoslať správu: ' + err.message);
          } else {
            console.log('Správa s JSON { unpair: true } odoslaná na topic "smartvoice/unpair"');
          }
        });
      } else {
        alert('Nie je pripojený MQTT klient.');
      }
    });
  </script>



  <footer style="
    width: 100%;
    height: 60px;
    border-top: 1px solid white;
    background-color: #000;
    color: #aaa;
    text-align: center;
    line-height: 60px;
    font-size: 0.9rem;
    position: relative;
    z-index: 10;
    margin-top: 4.7%;
  ">
    &copy; 2025 SmartVoice |
    <a href="privacy.html" style="color: #aaa; text-decoration: none;">Nyehehehehehhehe</a> |
    <a href="terms.html" style="color: #aaa; text-decoration: none;">catssssssssssss</a>
  </footer>

  <script>
    fetch("navbar.php")
      .then(res => res.text())
      .then(data => {
        document.getElementById("navbar-placeholder").innerHTML = data;
      });
  </script>

</body>

</html>
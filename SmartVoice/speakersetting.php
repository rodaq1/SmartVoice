<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
  echo "<script>
        alert('Najprv sa prosím prihlás!');
        window.location.href = 'login.html';
    </script>";
  exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT mirror_id FROM devices WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (empty($user['mirror_id'])) {
  echo "<script>
        alert('Najprv si prosím spáruj zariadenie!');
        window.location.href = 'pair.html';
    </script>";
  exit();
}

$_SESSION['mirror_id'] = $user['mirror_id'];

$mirror_id = $_SESSION['mirror_id'];
$config = [];

$configStmt = $conn->prepare("SELECT config FROM mirror_configs WHERE mirror_id = ?");
$configStmt->bind_param("s", $mirror_id);
$configStmt->execute();
$configResult = $configStmt->get_result();

if ($configResult && $row = $configResult->fetch_assoc()) {
  $config = json_decode($row['config'], true);
}
?>
<!DOCTYPE html>
<html lang="sk">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="speakersetting.css">
  <title>Nastavenie Speakera</title>
</head>

<body>
  <div id="navbar-placeholder"></div>

  <script>
    fetch("navbar.php")
      .then(res => res.text())
      .then(data => {
        document.getElementById("navbar-placeholder").innerHTML = data;
      });
  </script>

  <main class="settings-container">
    <h1>Nastavenia hlasového asistenta</h1>

    <form action="update_config.php" method="POST">
      <label for="speaker_gender">Typ hlasu:</label>
      <select id="speaker_gender" name="speaker_gender">
        <option value="male" <?php if (($config['speaker_gender'] ?? '') === 'male')
          echo 'selected'; ?>>Mužský</option>
        <option value="female" <?php if (($config['speaker_gender'] ?? '') === 'female')
          echo 'selected'; ?>>Ženský
        </option>
      </select>

      <label for="keyword">Kľúčové slovo pre aktiváciu:</label>
      <input type="text" id="keyword" name="keyword" placeholder="Napr. 'Hey SmartVoice'"
        value="<?php echo htmlspecialchars($config['keyword'] ?? '', ENT_QUOTES); ?>" />


      <label for="language">Jazyk:</label>
      <select id="language" name="language">
        <option value="sk" <?php if (($config['language'] ?? '') === 'sk')
          echo 'selected'; ?>>Slovenčina</option>
        <option value="en" <?php if (($config['language'] ?? '') === 'en')
          echo 'selected'; ?>>Angličtina</option>
      </select>

      <label for="volume">Hlasitosť:</label>
      <input type="range" id="volume" name="volume" min="0" max="100"
        value="<?php echo htmlspecialchars($config['volume'] ?? 50, ENT_QUOTES); ?>" />
      <button type="submit">Uložiť nastavenia</button>
    </form>
  </main>

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
">
    &copy; 2025 SmartVoice |
    <a href="privacy.html" style="color: #aaa; text-decoration: none;">Nyehehehehehhehe</a> |
    <a href="terms.html" style="color: #aaa; text-decoration: none;">catssssssssssss</a>
  </footer>

</body>

</html>
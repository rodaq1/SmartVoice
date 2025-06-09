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
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Smart Mirror Widgets</title>
  <link rel="stylesheet" href="widget.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <?php
  $mirror_id = $_SESSION['mirror_id'];

  $configStmt = $conn->prepare("SELECT config FROM mirror_configs WHERE mirror_id = ?");
  $configStmt->bind_param("s", $mirror_id);
  $configStmt->execute();
  $configResult = $configStmt->get_result();

  $config = [];

  if ($configResult && $row = $configResult->fetch_assoc()) {
    $config = json_decode($row['config'], true);
  }

  echo "<script>const widgetConfig = " . json_encode($config) . ";</script>"; ?>
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
  <main>
    <div id="widget-container" class="widget-grid"></div>

    <script>

      const widgets = [
        { name: "Počasie", key: "weather_widget", iconClass: "fas fa-cloud-sun", equipped: widgetConfig.weather_widget === true },
        { name: "Hodiny", key: "clock_widget", iconClass: "fas fa-clock", equipped: widgetConfig.clock_widget === true },
        { name: "Kalendár", key: "calendar_widget", iconClass: "fas fa-calendar-alt", equipped: widgetConfig.calendar_widget === true },
        { name: "Spotify", key: "spotify_widget", iconClass: "fab fa-spotify", equipped: widgetConfig.spotify_widget === true },
        { name: "Doorbell", key: "doorbell_widget", iconClass: "fas fa-door-open", equipped: widgetConfig.doorbell_widget === true },
      ];

      const container = document.getElementById("widget-container");

      function renderWidgets() {
        container.innerHTML = "";
        widgets.forEach((widget, index) => {
          const card = document.createElement("div");
          card.className = "card" + (widget.equipped ? " equipped" : "");
          card.innerHTML = `
        <div class="imgBox">
          <i class="${widget.iconClass} widget-icon"></i>
        </div>
        <div class="contentBox">
          <h3>${widget.name}</h3>
          <br>
            <a href="#" class="equip-btn" data-index="${index}">
              ${widget.equipped ? "Odstráň" : "Použi"}
            </a>
        </div>
      `;
          container.appendChild(card);
        });

        document.querySelectorAll(".equip-btn").forEach(btn => {
          btn.addEventListener("click", function (e) {
            e.preventDefault();
            const index = parseInt(this.dataset.index);
            const equippedCount = widgets.filter(w => w.equipped).length;

            if (widgets[index].equipped) {
              widgets[index].equipped = false;
            } else if (equippedCount < 4) {
              widgets[index].equipped = true;
            } else {
              alert("Môžeš mať max 4 vybavené widgety!");
            }

            renderWidgets();
          });
          fetch("update_config.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({
              weather_widget: widgets[0].equipped,
              clock_widget: widgets[1].equipped,
              calendar_widget: widgets[2].equipped,
              spotify_widget: widgets[3].equipped,
              doorbell_widget: widgets[4].equipped,
            }),
          })
            .then(res => {
              if (!res.ok) {
                alert("Nepodarilo sa uložiť nastavenia!");
              }
            })
            .catch(err => {
              console.error("Chyba pri ukladaní:", err);
              alert("Chyba pri ukladaní nastavení!");
            });

        });
      }

      renderWidgets();
    </script>
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
  margin-top: 7.7%;
">
    &copy; 2025 SmartVoice |
    <a href="privacy.html" style="color: #aaa; text-decoration: none;">Nyehehehehehhehe</a> |
    <a href="terms.html" style="color: #aaa; text-decoration: none;">catssssssssssss</a>
  </footer>

</body>

</html>
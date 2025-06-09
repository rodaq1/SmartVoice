<?php
session_start();
?>
<link rel="stylesheet" href="navbar.css">

<header class="navbar">
    <a href="#" class="navbar_left">SmartVoice</a>
  
    <button class="menu-toggle" aria-label="Toggle menu">&#9776;</button>
  
    <nav class="navbar_links">
      <a href="index.html">Domov</a>
      <a href="widget.php">Widgety</a>
      <a href="speakersetting.php">Nastavenia Speakera</a>

      <?php if (isset($_SESSION['user_id'])): ?>
          <a href="account.php">Účet</a>
      <?php else: ?>
          <a href="login.html">Pripojiť sa</a>
      <?php endif; ?>
    </nav>
</header>

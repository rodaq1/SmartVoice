<?php
session_start();

// Vyčistenie session
$_SESSION = array();
session_destroy();

// Presmerovanie na login stránku
header('Location: login.html');
exit();
?>
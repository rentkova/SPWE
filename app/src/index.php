<?php
session_start();
// Přihlášený uživatel rovnou na dashboard
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8">
  <title>Hostingové centrum</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <h1>Hostingové centrum</h1>
  <p>Vítejte. Přihlaste se nebo si vytvořte účet.</p>
  <hr>
  <div style="display:flex;gap:10px;margin-top:16px;">
    <a href="register.php"><button>Registrace</button></a>
    <a href="login.php"><button>Přihlášení</button></a>
  </div>
</div>
</body>
</html>

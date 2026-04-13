<?php
session_start();
$pdo = require __DIR__ . '/db.php';

$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $u = $_POST['username'];
  $p = $_POST['password'];

  // změna TABULKY + SLOUPCE
  $stmt = $pdo->prepare("SELECT * FROM uzivatele WHERE uzivatelske_jmeno = ?");
  $stmt->execute([$u]);
  $user = $stmt->fetch();

  if ($user && password_verify($p, $user['heslo_hash'])) {

    $_SESSION['user'] = $user['uzivatelske_jmeno'];
    $_SESSION['id'] = $user['id'];

    header("Location: dashboard.php");
    exit;

  } else {
    $msg = "Chyba přihlášení";
  }
}
?>

<!doctype html>
<html>
<head>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

<h1>Login</h1>

<?php if ($msg) echo "<p>$msg</p>"; ?>

<form method="post">
  <input name="username" placeholder="Username">
  <input name="password" type="password" placeholder="Password">
  <button>Přihlásit</button>
</form>

<a href="index.php">Zpět</a>

</div>

</body>
</html>
<?php
session_start();
$pdo = require __DIR__ . '/db.php';

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

// ✔ načtení uživatele
$stmt = $pdo->prepare("
  SELECT id, email, datum_registrace, aktivni
  FROM uzivatele
  WHERE uzivatelske_jmeno = ?
");
$stmt->execute([$_SESSION['user']]);
$user = $stmt->fetch();

// ✔ načtení domény (pokud existuje)
$stmt2 = $pdo->prepare("
  SELECT domena 
  FROM domeny 
  WHERE uzivatel_id = ?
  LIMIT 1
");
$stmt2->execute([$user['id']]);
$domena = $stmt2->fetch();
?>

<!doctype html>
<html>
<head>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

<h1>Vítej, <?= htmlspecialchars($_SESSION['user']) ?> 👋</h1>

<p><b>Email:</b> <?= htmlspecialchars($user['email']) ?></p>
<p><b>Registrace:</b> <?= $user['datum_registrace'] ?></p>

<hr>

<h3>Status účtu</h3>

<?php if ($user['aktivni']): ?>
  <p style="color:green;">✔ Aktivní účet</p>
<?php else: ?>
  <p style="color:red;">✖ Neaktivní účet</p>
<?php endif; ?>

<hr>

<h3>Tvůj web</h3>

<?php if ($domena): ?>
  <a href="http://<?= htmlspecialchars($domena['domena']) ?>" target="_blank">
    🌐 Otevřít web (<?= htmlspecialchars($domena['domena']) ?>)
  </a>
<?php else: ?>
  <p>Žádná doména zatím není přiřazena</p>
<?php endif; ?>

<br>

<a href="logout.php">Odhlásit se</a>

</div>

</body>
</html>
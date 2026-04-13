<?php
session_start();
$pdo = require __DIR__ . '/db.php';

$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username']);
    $e = trim($_POST['email']);
    $p = $_POST['password'];

    if ($u && $e && $p) {
        try {
            $check = $pdo->prepare("
                SELECT id
                FROM uzivatele
                WHERE uzivatelske_jmeno = ? OR email = ?
            ");
            $check->execute([$u, $e]);

            if ($check->fetch()) {
                $msg = "Uživatel nebo email už existuje";
            } else {
                $hash = password_hash($p, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO uzivatele (uzivatelske_jmeno, email, heslo_hash)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$u, $e, $hash]);

                $msg = "OK registrace ✔";
            }
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }
    } else {
        $msg = "Vyplň vše";
    }
}
?>

<!doctype html>
<html>
<head>
  <link rel="stylesheet" href="style.css">
  <meta charset="utf-8">
  <title>Registrace</title>
</head>
<body>

<div class="container">
  <h1>Registrace</h1>

  <?php if ($msg) echo "<p>$msg</p>"; ?>

  <form method="post">
    <input name="username" placeholder="Username">
    <input name="email" type="email" placeholder="Email">
    <input name="password" type="password" placeholder="Password">
    <button type="submit">Registrovat</button>
  </form>

  <a href="index.php">Zpět</a>
</div>

</body>
</html>
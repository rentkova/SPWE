<?php
session_start();

// Pokud je uživatel přihlášen, přesměrovat rovnou na dashboard
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

$pdo = require __DIR__ . '/db.php';
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM uzivatele WHERE uzivatelske_jmeno = ? AND aktivni = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['heslo_hash'])) {
            session_regenerate_id(true); // ochrana proti session fixation
            $_SESSION['user'] = $user['uzivatelske_jmeno'];
            $_SESSION['id']   = $user['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            $msg = "Nesprávné jméno nebo heslo.";
        }
    } else {
        $msg = "Vyplňte prosím všechna pole.";
    }
}
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8">
  <title>Přihlášení – Hosting</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <h1>Přihlášení</h1>

  <?php if ($msg): ?>
    <p class="msg error"><?= htmlspecialchars($msg) ?></p>
  <?php endif; ?>

  <form method="post">
    <input name="username" placeholder="Uživatelské jméno" required
           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
    <input name="password" type="password" placeholder="Heslo" required>
    <button type="submit">Přihlásit se</button>
  </form>

  <a href="index.php">← Zpět</a>
</div>
</body>
</html>

<?php
session_start();
$pdo = require __DIR__ . '/db.php';

$msg     = null;
$msgType = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $email && $password) {
        // Oprava: přejmenované proměnné ($username, $email) – dříve $u, $e kolidovalo s catch($e)
        try {
            $check = $pdo->prepare("SELECT id FROM uzivatele WHERE uzivatelske_jmeno = ? OR email = ?");
            $check->execute([$username, $email]);

            if ($check->fetch()) {
                $msg = "Uživatelské jméno nebo e-mail již existuje.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO uzivatele (uzivatelske_jmeno, email, heslo_hash) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hash]);
                $msg     = "Registrace proběhla úspěšně. Můžete se přihlásit.";
                $msgType = 'success';
            }
        } catch (Exception $ex) {
            // Oprava: nevypisovat interní chybu ($ex->getMessage()) uživateli
            $msg = "Při registraci došlo k chybě. Zkuste to prosím znovu.";
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
  <title>Registrace – Hosting</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <h1>Registrace</h1>

  <?php if ($msg): ?>
    <p class="msg <?= $msgType ?>"><?= htmlspecialchars($msg) ?></p>
  <?php endif; ?>

  <form method="post">
    <input name="username" placeholder="Uživatelské jméno" required
           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
    <input name="email" type="email" placeholder="E-mail" required
           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    <input name="password" type="password" placeholder="Heslo" required>
    <button type="submit">Registrovat</button>
  </form>

  <a href="index.php">← Zpět</a>
</div>
</body>
</html>

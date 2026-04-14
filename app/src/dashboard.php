<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$pdo = require __DIR__ . '/db.php';

// Načtení uživatele
$stmt = $pdo->prepare("SELECT id, email, datum_registrace, aktivni FROM uzivatele WHERE uzivatelske_jmeno = ?");
$stmt->execute([$_SESSION['user']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$msg     = null;
$msgType = 'error';

// Přidání nové domény
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_domain') {
    $domena  = trim($_POST['domena']       ?? '');
    $ftpUser = trim($_POST['ftp_uzivatel'] ?? '');
    $ftpPass =      $_POST['ftp_heslo']    ?? '';

    if (!$domena || !$ftpUser || !$ftpPass) {
        $msg = "Vyplnte prosim vsechna pole.";
    } elseif (!preg_match('/^[a-z0-9.\-]+$/i', $domena)) {
        $msg = "Nazev domeny obsahuje nepovolene znaky.";
    } elseif (!preg_match('/^[a-z0-9_\-]+$/i', $ftpUser)) {
        $msg = "FTP uzivatelske jmeno obsahuje nepovolene znaky.";
    } else {
        try {
            $check = $pdo->prepare("SELECT id FROM domeny WHERE domena = ? OR ftp_uzivatel = ?");
            $check->execute([$domena, $ftpUser]);

            if ($check->fetch()) {
                $msg = "Domena nebo FTP uzivatel jiz existuje.";
            } else {
                $adresar = $ftpUser;
                $ftpHash = sha1($ftpPass);

                $ins = $pdo->prepare("
                    INSERT INTO domeny (uzivatel_id, domena, ftp_uzivatel, ftp_heslo_hash, ftp_adresar)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $ins->execute([$user['id'], $domena, $ftpUser, $ftpHash, $adresar]);

                $webDir = __DIR__ . '/public/uploads/' . $adresar;
                if (!is_dir($webDir)) {
                    mkdir($webDir, 0777, true);
                    chmod($webDir, 0777);
                    file_put_contents($webDir . '/index.php', "<?php echo 'Web {$domena} funguje!';");
                    chmod($webDir . '/index.php', 0666);
                }

                $msg     = "Domena \"{$domena}\" pridana! Web: /w/{$ftpUser}/";
                $msgType = 'success';
            }
        } catch (Exception $ex) {
            $msg = "Chyba pri ukladani domeny.";
        }
    }
}

// Smazání domény
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_domain') {
    $domenaId = (int)($_POST['domena_id'] ?? 0);

    if ($domenaId > 0) {
        try {
            // Ověřit že doména patří přihlášenému uživateli
            $check = $pdo->prepare("SELECT ftp_adresar FROM domeny WHERE id = ? AND uzivatel_id = ?");
            $check->execute([$domenaId, $user['id']]);
            $row = $check->fetch();

            if ($row) {
                // Smazat z DB
                $del = $pdo->prepare("DELETE FROM domeny WHERE id = ? AND uzivatel_id = ?");
                $del->execute([$domenaId, $user['id']]);

                // Smazat adresář webu včetně obsahu
                $webDir = __DIR__ . '/public/uploads/' . $row['ftp_adresar'];
                if (is_dir($webDir)) {
                    // Rekurzivní smazání adresáře
                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($webDir, RecursiveDirectoryIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::CHILD_FIRST
                    );
                    foreach ($files as $file) {
                        $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
                    }
                    rmdir($webDir);
                }

                $msg     = "Domena byla smazana.";
                $msgType = 'success';
            } else {
                $msg = "Domena nenalezena.";
            }
        } catch (Exception $ex) {
            $msg = "Chyba pri mazani domeny.";
        }
    }
}

// Načtení domén
$stmt2 = $pdo->prepare("SELECT id, domena, ftp_uzivatel, ftp_adresar, datum_vytvoreni FROM domeny WHERE uzivatel_id = ? ORDER BY datum_vytvoreni DESC");
$stmt2->execute([$user['id']]);
$domeny = $stmt2->fetchAll();
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8">
  <title>Dashboard - Hosting</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

  <h1>Vitej, <?= htmlspecialchars($_SESSION['user']) ?> 👋</h1>
  <p><b>E-mail:</b> <?= htmlspecialchars($user['email']) ?></p>
  <p><b>Registrace:</b> <?= htmlspecialchars($user['datum_registrace']) ?></p>
  <p><b>Stav:</b>
    <?php if ($user['aktivni']): ?>
      <span style="color:green;">✔ Aktivni</span>
    <?php else: ?>
      <span style="color:red;">✖ Neaktivni</span>
    <?php endif; ?>
  </p>

  <hr>
  <h2>Moje domeny</h2>

  <?php if ($msg): ?>
    <p class="msg <?= $msgType ?>"><?= htmlspecialchars($msg) ?></p>
  <?php endif; ?>

  <?php if ($domeny): ?>
    <table>
      <thead>
        <tr>
          <th>Domena</th>
          <th>Web</th>
          <th>FTP uzivatel</th>
          <th>FTP adresa</th>
          <th>Pridana</th>
          <th>Akce</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($domeny as $d): ?>
          <tr>
            <td><?= htmlspecialchars($d['domena']) ?></td>
            <td>
              <a href="/w/<?= htmlspecialchars($d['ftp_adresar']) ?>/" target="_blank">
                /w/<?= htmlspecialchars($d['ftp_adresar']) ?>/
              </a>
            </td>
            <td><?= htmlspecialchars($d['ftp_uzivatel']) ?></td>
            <td>IP_VM:21</td>
            <td><?= htmlspecialchars($d['datum_vytvoreni']) ?></td>
            <td>
              <form method="post" onsubmit="return confirm('Opravdu smazat domenu <?= htmlspecialchars($d['domena']) ?>? Vsechny soubory budou smazany.');">
                <input type="hidden" name="action" value="delete_domain">
                <input type="hidden" name="domena_id" value="<?= (int)$d['id'] ?>">
                <button type="submit" class="btn-delete">Smazat</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>Zatim zadna domena.</p>
  <?php endif; ?>

  <hr>
  <h2>Pridat domenu</h2>
  <form method="post">
    <input type="hidden" name="action" value="add_domain">
    <input name="domena"       placeholder="Nazev domeny (napr. kalkulacka.local)" required>
    <input name="ftp_uzivatel" placeholder="FTP uzivatel (napr. kalkulacka)" required>
    <input name="ftp_heslo"    type="password" placeholder="FTP heslo" required>
    <button type="submit">Pridat domenu</button>
  </form>

  <hr>
  <a href="logout.php">Odhlasit se</a>

</div>
</body>
</html>

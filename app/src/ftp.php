<?php
// Správa FTP uživatelů
// PHP a FTP kontejner sdílejí volume ftp_passwd montovaný na /etc/vsftpd/
// PHP přímo zapisuje do /etc/vsftpd/passwd – vsftpd soubor okamžitě vidí

function addFtpUser(string $username, string $password): bool
{
    $passwdFile = '/etc/vsftpd/passwd';

    // Vytvořit soubor pokud neexistuje
    if (!file_exists($passwdFile)) {
        touch($passwdFile);
        chmod($passwdFile, 0600);
    }

    // Zkontrolovat duplicitu
    $current = file_get_contents($passwdFile);
    if (preg_match("/^{$username}:/m", $current)) {
        return true; // uživatel již existuje
    }

    // Zahashovat heslo – MD5 crypt kompatibilní s pam_pwdfile
    $salt = '$1$' . substr(md5(uniqid(mt_rand(), true)), 0, 8) . '$';
    $hash = crypt($password, $salt);

    // Přidat uživatele
    return file_put_contents($passwdFile, "{$username}:{$hash}\n", FILE_APPEND | LOCK_EX) !== false;
}

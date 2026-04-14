#!/bin/bash
set -e

# Nastavit PASV adresu z env proměnné
PASV_ADDRESS="${PASV_ADDRESS:-127.0.0.1}"
echo "pasv_address=${PASV_ADDRESS}" >> /etc/vsftpd.conf

# Vytvořit prázdný passwd soubor pokud neexistuje
touch /etc/vsftpd/passwd

# Spustit vsftpd
echo "FTP server starting (PASV: ${PASV_ADDRESS})..."
exec vsftpd /etc/vsftpd.conf

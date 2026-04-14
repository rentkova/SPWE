#!/bin/bash
set -e

: "${DB_HOST:=mariadb}"
: "${DB_NAME:=hosting_db}"
: "${DB_AUTH_USER:=root}"
: "${DB_AUTH_PASSWORD:=change_me}"
: "${PASV_ADDRESS:=127.0.0.1}"
: "${PASV_MIN_PORT:=21100}"
: "${PASV_MAX_PORT:=21110}"

# Přeložit hostname na IP aby pam_mysql nedělal pomalý DNS lookup
DB_IP=$(getent hosts "${DB_HOST}" | awk '{print $1}' | head -1)
if [ -z "$DB_IP" ]; then
    DB_IP="${DB_HOST}"
fi
echo "DB IP: ${DB_IP}"

# Přepsat existující hodnoty místo přidávání duplicit
sed -i "s|^pasv_address=.*|pasv_address=${PASV_ADDRESS}|" /etc/vsftpd/vsftpd.conf
sed -i "s|^pasv_min_port=.*|pasv_min_port=${PASV_MIN_PORT}|" /etc/vsftpd/vsftpd.conf
sed -i "s|^pasv_max_port=.*|pasv_max_port=${PASV_MAX_PORT}|" /etc/vsftpd/vsftpd.conf
sed -i "s|^pam_service_name=.*|pam_service_name=vsftpd.mysql|" /etc/vsftpd/vsftpd.conf

# PAM konfigurace s IP adresou místo hostname
cat > /etc/pam.d/vsftpd.mysql << PAM
auth    required pam_mysql.so user=${DB_AUTH_USER} passwd=${DB_AUTH_PASSWORD} host=${DB_IP} db=${DB_NAME} table=domeny usercolumn=ftp_uzivatel passwdcolumn=ftp_heslo_hash crypt=4
account required pam_mysql.so user=${DB_AUTH_USER} passwd=${DB_AUTH_PASSWORD} host=${DB_IP} db=${DB_NAME} table=domeny usercolumn=ftp_uzivatel passwdcolumn=ftp_heslo_hash crypt=4
PAM

echo "PAM config:"
cat /etc/pam.d/vsftpd.mysql

exec /usr/sbin/run-vsftpd.sh

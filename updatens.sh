#!/bin/bash
# Přidá testovací domény do /etc/hosts (spustit jednorázově ve VM)
# Použití: sudo bash updatens.sh

HOSTS_FILE="/etc/hosts"
DOMAINS=("app.local" "web1.local" "web2.local" "web3.local" "web4.local" "ftp.local")

echo "Přidávám testovací domény do $HOSTS_FILE..."

for domain in "${DOMAINS[@]}"; do
    if grep -q "$domain" "$HOSTS_FILE"; then
        echo "  ✔ $domain – již existuje, přeskakuji"
    else
        echo "127.0.0.1  $domain" >> "$HOSTS_FILE"
        echo "  ✔ $domain – přidáno"
    fi
done

echo ""
echo "Hotovo. Aktuální záznamy:"
grep -E "app\.local|web[1-4]\.local|ftp\.local" "$HOSTS_FILE"

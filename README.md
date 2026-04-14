# Hostingové centrum – SPWE

## Spuštění

```powershell
cp .env.example .env
# Nastav hesla v .env

docker compose up -d --build
```

Otevři http://localhost – hotovo, žádné další nastavení.

## Jak to funguje

| Co                 | Jak                                              |
|--------------------|--------------------------------------------------|
| Aplikace           | http://localhost                                 |
| Web zákazníka      | http://localhost/w/<ftp-uzivatel>/               |
| FTP přístup        | ftp://localhost:21 · přihlašovací údaje z dashboardu |
| Virtual hosty      | Automaticky přes Apache VirtualDocumentRoot      |
| FTP uživatelé      | Automaticky při přidání domény v dashboardu      |

## Přidání domény

1. Registruj se a přihlaš na http://localhost
2. Vyplň formulář – název domény, FTP uživatel, FTP heslo
3. Web je ihned dostupný na http://localhost/w/<ftp-uzivatel>/
4. FTP přístup funguje okamžitě přes FileZillu

## FileZilla – připojení k FTP

- Host: `127.0.0.1`
- Uživatel: FTP uživatel zadaný v dashboardu
- Heslo: FTP heslo zadané v dashboardu
- Port: `21`
- Přenosový mód: Pasivní (PASV)

## Příkazy

```powershell
docker compose down           # zastavení
docker compose down -v        # reset včetně DB a FTP uživatelů
docker compose logs -f web    # logy Apache
docker compose logs -f ftp    # logy FTP
```

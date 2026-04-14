# add-local-domains.ps1

$domains = @(
    "app.local",
    "moje.local",
    "tvoje.local",
    "jeho.local",
    "jeji.local",
    "nase.local"
)

$ip = "127.0.0.1"
$hostsPath = "$env:SystemRoot\System32\drivers\etc\hosts"

# kontrola spuštění jako admin
$isAdmin = ([Security.Principal.WindowsPrincipal] `
    [Security.Principal.WindowsIdentity]::GetCurrent()
).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "Skript musí být spuštěný jako správce." -ForegroundColor Red
    exit 1
}

if (-not (Test-Path $hostsPath)) {
    Write-Host "Soubor hosts nebyl nalezen: $hostsPath" -ForegroundColor Red
    exit 1
}

$hostsContent = Get-Content $hostsPath -ErrorAction Stop

foreach ($domain in $domains) {
    $entry = "$ip`t$domain"

    $alreadyExists = $hostsContent | Where-Object {
        $_ -match "^\s*127\.0\.0\.1\s+$([regex]::Escape($domain))\s*$"
    }

    if (-not $alreadyExists) {
        Add-Content -Path $hostsPath -Value $entry
        Write-Host "Přidáno: $entry" -ForegroundColor Green
    }
    else {
        Write-Host "Už existuje: $domain" -ForegroundColor Yellow
    }
}

Write-Host "`nHotovo." -ForegroundColor Cyan
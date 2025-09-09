# URLX – Minimaler URL-Shortener (Laravel)

Kleines Showcase-Projekt mit **Laravel 12**, **OOP**, **Validation**, **DB-Migrationen**, **Weiterleitung & Tracking** und **QR-Code**.  
Ziel: Kompaktes Repo, das moderne PHP- und Web-Skills zeigt (Routing, Controller, Models, Tests, CI).

## Features
- POST `/api/links` → Shortlink erstellen (Validation, optional `slug`, optional `expires_at`)
- GET `/{slug}` → Weiterleitung mit Klick-Tracking (Events)
- GET `/api/qr/{slug}` → QR-Code (PNG)
- GET `/api/links` → Links paginiert listen
- GET `/api/links/{slug}/stats` → Klicks je Tag (30 Tage)

---

## Schnellstart (Windows + SQLite, ohne Docker)

Voraussetzungen: PHP 8.3+ (mit `fileinfo`, `mbstring`, `zip`, `gd`), Composer, Node (optional).

```powershell
# 1) Abhängigkeiten
composer install

# 2) .env + App Key
Copy-Item .env.example .env
php artisan key:generate

# 3) SQLite-DB anlegen und .env auf SQLite stellen
New-Item -ItemType Directory -Force -Path .\database | Out-Null
New-Item -ItemType File -Force -Path .\database\database.sqlite | Out-Null

# .env patchen
(Get-Content .env) `
 -replace 'DB_CONNECTION=mysql','DB_CONNECTION=sqlite' `
 -replace 'DB_HOST=.*','' `
 -replace 'DB_PORT=.*','' `
 -replace 'DB_DATABASE=.*','DB_DATABASE=database/database.sqlite' `
 -replace 'DB_USERNAME=.*','' `
 -replace 'DB_PASSWORD=.*','' |
 Set-Content .env

# 4) Migrationen
php artisan migrate

# 5) Start
php artisan serve
```

---

## API Beispiele (PowerShell)

**Link erstellen**
```powershell
Invoke-RestMethod -Method Post `
  -Uri http://127.0.0.1:8000/api/links `
  -ContentType application/json `
  -Body (@{ target_url = "https://example.com" } | ConvertTo-Json)
```

Optional mit Slug/Expiry:
```powershell
Invoke-RestMethod -Method Post `
  -Uri http://127.0.0.1:8000/api/links `
  -ContentType application/json `
  -Body (@{ target_url="https://example.com"; slug="meinSlug"; expires_at="2030-01-01" } | ConvertTo-Json)
```

**Weiterleiten**  
Im Browser: `http://127.0.0.1:8000/<slug>`

**QR-Code**  
Im Browser: `http://127.0.0.1:8000/api/qr/<slug>`

**Links paginiert**
```
GET http://127.0.0.1:8000/api/links
```

**Stats je Tag**
```
GET http://127.0.0.1:8000/api/links/<slug>/stats
```

---

## Wie es funktioniert (kurz & einfach)

- **Route** verbindet URL → **Controller**-Methode.  
- **Request-Validation** prüft Eingaben.  
- **Model (Eloquent)** schreibt/liest Daten in Tabellen, die die **Migrationen** anlegen.  
- Weiterleitung zählt Klicks + legt Event ab, QR liefert PNG aus.

---

## Tests (PHPUnit, SQLite In-Memory)

`phpunit.xml` enthält:
```xml
<php>
  <server name="APP_ENV" value="testing"/>
  <server name="DB_CONNECTION" value="sqlite"/>
  <server name="DB_DATABASE" value=":memory:"/>
</php>
```

Tests ausführen:
```powershell
php artisan test
```

---

## CI (GitHub Actions)

- Läuft auf Ubuntu mit PHP 8.3
- Nutzt SQLite (Datei wird erstellt)
- Installiert Composer-Pakete, migriert, führt Tests aus

Siehe `.github/workflows/ci.yml`.

---

## Auf verteilte DB wechseln (YugabyteDB / Postgres)

- **YugabyteDB (YSQL)** spricht Postgres-Protokoll → einfach `.env` anpassen:
```
DB_CONNECTION=pgsql
DB_HOST=<yb-host>
DB_PORT=5433
DB_DATABASE=urlx
DB_USERNAME=<user>
DB_PASSWORD=<pw>
```
- Keine Codeänderung nötig. Die write-lastigen `click_events` profitieren von Sharding/Replikation.

---

## Sicherheit & Privacy

- `ip_hash` statt Roh-IP (hash + Salt, z. B. `APP_KEY` oder eigener `IP_SALT`)
- `expires_at` respektiert: abgelaufene Links → HTTP 410
- `throttle:60,1` schützt API vor Abuse

---

## Roadmap (kurz)
- Admin-UI (Blade) mit Tabelle & Charts
- API Keys (Personal Access Tokens)
- Queue-Job für Event-Write (Batch) + Redis
- CSV/JSON-Export

---

## Tech-Stack
- PHP 8.3/8.4, Laravel 12
- SQLite lokal / Postgres / YugabyteDB
- PHPUnit für Tests
- GitHub Actions CI
- simple-qrcode (PNG) / bacon-qr-code (SVG möglich)

[← Zpět na README](./README.md)

# Nasazení (deployment)

Návod, jak nasadit tento projekt (PHP 8.2 + SQLite backend, React frontend) na linuxový
server s **Apache + PHP-FPM**. Frontend i backend běží na **jedné doméně** (stejný origin,
žádné CORS): statický React build servíruje Apache, cesty `/api`, `/admin`, `/uploads`,
`/swagger` a všechny „stránky" (kvůli SEO meta) jdou přes PHP.

Lokální vývoj je v [DEVELOPMENT.md](./DEVELOPMENT.md), provoz po nasazení
v [RUNBOOK.md](./RUNBOOK.md).

> V celém návodu jsou **vzorové hodnoty**. Nahraď si je svými:
> - `example.com` → tvoje doména
> - `/var/www/example.com` → cílová složka
> - `php8.2-fpm.sock` → socket tvé verze PHP-FPM
> - placeholder secrets (`<...>`) → vlastní vygenerované hodnoty

---

## 1. Požadavky

- **PHP 8.2** (CLI i FPM) s rozšířeními: `pdo`, `pdo_sqlite`, `sqlite3`, `json`, `mbstring`
- **Apache 2.4** s moduly: `rewrite`, `proxy`, `proxy_fcgi`, `ssl`
- **PHP-FPM** (`php8.2-fpm`)
- **Composer**
- **Node.js 20+** a **npm**
- **git**, **certbot** (pro HTTPS)

Na Debianu/Ubuntu zhruba:

```bash
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-sqlite3 php8.2-mbstring \
                    apache2 git certbot python3-certbot-apache
sudo a2enmod rewrite proxy proxy_fcgi ssl
```

Composer a Node si nainstaluj dle oficiálních návodů (např. Composer přes `getcomposer.org`,
Node přes `nvm` nebo distro balíček).

---

## 2. Získání kódu

```bash
sudo git clone https://github.com/lunax86/profolio.git /var/www/example.com
sudo chown -R "$USER":www-data /var/www/example.com
```

Struktura:

```
/var/www/example.com/
├── backend/     # PHP API + administrace
└── frontend/    # React (build → frontend/dist)
```

---

## 3. Backend

```bash
cd /var/www/example.com/backend

# závislosti (bez dev nástrojů)
composer install --no-dev --optimize-autoloader

# konfigurace
cp .env.example .env
openssl rand -hex 32          # vygenerovaný řetězec vlož do JWT_SECRET
nano .env                     # ⚠️ vyplň PŘED migrací/seedem (viz vzorový .env níže)
```

**Vzorový `.env`** (produkce):

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com

DB_PATH=database/database.sqlite

JWT_SECRET=<vlož výstup: openssl rand -hex 32>
JWT_TTL=3600

CORS_ALLOWED_ORIGIN=https://example.com

ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=<zvol silné heslo>
```

**Databáze** - teprve **s hotovým `.env`** vytvoř schéma a naplň výchozí obsah. Seed založí
admina podle `ADMIN_EMAIL`/`ADMIN_PASSWORD` z `.env`, takže pořadí je důležité:

```bash
cd /var/www/example.com/backend
php database/migrate.php      # vytvoří schéma
php database/seed.php         # vytvoří admina (dle .env) + demo obsah
```

**Práva pro zápis** - web server (`www-data`) musí umět zapisovat do databáze a nahraných
souborů; `.env` naopak jen číst:

```bash
# DB a uploady zapisovatelné pro skupinu www-data (+ dědění skupiny u nových souborů)
sudo chown -R "$USER":www-data database storage
sudo chmod -R g+rwX database storage
sudo find database storage -type d -exec chmod g+s {} \;

# .env: čte vlastník + www-data, nikdo jiný
sudo chown "$USER":www-data .env
chmod 640 .env
```

---

## 4. Frontend

```bash
cd /var/www/example.com/frontend
npm ci
npm run build     # výstup → frontend/dist/
```

---

## 5. Apache - virtuální host

Frontend je `DocumentRoot`, backend se volá přes rewrite. Vytvoř
`/etc/apache2/sites-available/example.com.conf`:

```apache
<VirtualHost *:80>
    ServerName example.com

    DocumentRoot /var/www/example.com/frontend/dist

    <Directory /var/www/example.com/frontend/dist>
        Options -Indexes
        AllowOverride None
        Require all granted
    </Directory>

    # PHP backend leží mimo DocumentRoot - povolit jeho spuštění
    <Directory /var/www/example.com/backend/public>
        Require all granted
    </Directory>

    RewriteEngine On
    # API, administrace, swagger a nahrané obrázky → PHP backend
    RewriteRule ^/(api|admin|swagger|uploads)(/.*)?$ /var/www/example.com/backend/public/index.php [L]
    # Reálné soubory z buildu (JS/CSS/obrázky/favicon) servíruj přímo
    RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -f
    RewriteRule ^ - [L]
    # Vše ostatní (včetně "/") → PHP: SEO shell / robots.txt / sitemap.xml
    RewriteRule ^ /var/www/example.com/backend/public/index.php [L]

    # PHP přes PHP-FPM (uprav verzi/socket dle systému)
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost"
    </FilesMatch>

    ErrorLog  ${APACHE_LOG_DIR}/example.com_error.log
    CustomLog ${APACHE_LOG_DIR}/example.com_access.log combined
</VirtualHost>
```

Zapni a otestuj:

```bash
sudo a2ensite example.com.conf
sudo apache2ctl configtest      # musí říct: Syntax OK
sudo systemctl reload apache2
```

---

## 6. HTTPS (Let's Encrypt)

```bash
sudo certbot --apache -d example.com
```

Certbot vytvoří SSL variantu vhostu (`example.com-le-ssl.conf`) a přidá přesměrování z HTTP na
HTTPS. **Zkontroluj, že se do SSL vhostu přenesl i `RewriteRule` blok a `<FilesMatch>` PHP-FPM
handler** (certbot je většinou zkopíruje; pokud ne, doplň je tam stejně jako výše).
Nakonec `sudo systemctl reload apache2`.

---

## 7. Ověření

Nahraď `example.com` svou doménou (nebo testuj lokálně přes
`--resolve example.com:443:127.0.0.1`):

```bash
# Homepage má v <head> serverová SEO meta (title/OG/JSON-LD z nastavení):
curl -s https://example.com/ | grep -oE '<title>[^<]*|og:title|application/ld\+json' | head

# Veřejné API vrací JSON:
curl -s https://example.com/api/settings

# SEO soubory:
curl -s https://example.com/robots.txt;  echo
curl -s https://example.com/sitemap.xml; echo
```

Očekáváš: titulek + OG + JSON-LD v homepage, JSON z API, obsah robots/sitemap. A web se
normálně načte v prohlížeči.

---

## 8. Aktualizace (redeploy)

Podle toho, co se změnilo:

| Změna v repu | Co spustit na serveru |
|---|---|
| Backend / admin PHP (`backend/…`) | `git pull` → `sudo systemctl reload php8.2-fpm` |
| Frontend (`frontend/src`, `index.html`, …) | `git pull` → `npm --prefix frontend run build` |
| Změna DB schématu (nové migrace) | `php backend/database/migrate.php` (idempotentní) |
| Úprava Apache vhostu | editace → `sudo apache2ctl configtest` → `sudo systemctl reload apache2` |

Typický redeploy „všeho":

```bash
cd /var/www/example.com
git pull
npm --prefix frontend run build
php backend/database/migrate.php
sudo systemctl reload php8.2-fpm
```

---

## 9. Po nasazení v administraci

Přihlas se na `https://example.com/admin` (údaje z `.env`) a projdi jednotlivé stránky:

- **Obecné** - název, slogan (dědí ho úvod, patička i SEO), kontakt, favicon, časová zóna
- **Úvod** - hlavní nadpis (co děláte), místo (kde), stručné „o mně" a úvodní fotka
- **Ukázky** - fotky práce; volitelná fotka „před" zapne posuvník před/po
- **Služby** - karty služeb
- **Recenze** - reference zákazníků (jen skutečné - viz upozornění přímo v adminu)
- **Patička** - text patičky, portrét u kontaktu a odkazy na sociální sítě
- **Vzhled** - barevné schéma (shade + accent)
- **Sekce a pořadí** - které sekce se zobrazí a v jakém pořadí
- **SEO** - SEO titulek/popis, obrázek pro sdílení, přepínač **Indexování** (na testovacím
  webu klidně „Ne", na ostrém „Ano")
- **GDPR** - text zásad ochrany osobních údajů; doplň své údaje (jméno, IČO, místo podnikání)
- **Účet** - změna hesla/e-mailu; správce webu (super admin) může přidat další účty

> **Upgrade stávajícího webu:** nové sekce (např. Recenze) se po redeployi objeví
> v **Sekce a pořadí** vypnuté na konci - zapni je a přetáhni na správné místo. Na čerstvé
> instalaci jsou rovnou v pořadí a zapnuté.

> **Super admin:** při upgradu stávajícího webu `migrate.php` povýší nejstarší účet na
> super admina (spravuje ostatní účty, nelze ho smazat). Nového super admina nelze
> vytvořit z UI.

---

## 10. Provoz

- **Zálohy databáze** - `backend/database/database.sqlite` obsahuje veškerý obsah i přijaté
  poptávky. Doporučeno pravidelně zálohovat (např. denní `cron`, který soubor kopíruje stranou
  a rotuje posledních N dní).
- **Reset rate-limitu** - kdyby ses zamkl při přihlašování (nebo pro test), smaž počítadla:
  `rm -f backend/storage/ratelimit/*.json`
- **Logy** - `${APACHE_LOG_DIR}/example.com_error.log` a `..._access.log`.
- **Skrytí Swaggeru** - při `APP_ENV=production` je `/swagger` i `/api/openapi.json` vypnutý
  (vrací 404); dostupné je jen v lokálním vývoji.

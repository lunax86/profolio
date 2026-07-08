[← Zpět na README](./README.md)

# Provoz a údržba

Taháky pro **běžící** nasazení - hardening, zálohy, vychytávky. Nasazení od nuly je
v [DEPLOYMENT.md](./DEPLOYMENT.md); lokální vývoj v [DEVELOPMENT.md](./DEVELOPMENT.md).
Tenhle soubor je „co dělat potom".

> Vzorové hodnoty (`example.com`, `/var/www/example.com`) si nahraď svými.

---

## Bezpečnostní hlavičky (Apache)

Do SSL vhostu (`<VirtualHost *:443>`) a povolit `mod_headers`:

```apache
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "frame-ancestors 'self'"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
```

```bash
sudo a2enmod headers
sudo apache2ctl configtest && sudo systemctl reload apache2
# ověření:
curl -sI https://example.com/ | grep -iE 'strict-transport|x-content|x-frame|referrer|content-security|permissions'
```

- **HSTS** = vždy HTTPS (po prvním načtení prohlížeč po dobu `max-age` odmítá HTTP).
- `frame-ancestors 'self'` = anti-clickjacking (minimální CSP, neomezuje skripty/styly).
- **Plné CSP** (`script-src`/`style-src`) záměrně NENÍ - rozbilo by inline skripty/styly/handlery
  v adminu a Unsplash obrázky; je to samostatná, pečlivá práce.

---

## Web pod heslem během výstavby (basic auth)

Celý web za heslem, dokud si klient nenastaví obsah; pak heslo sundáš.

⚠️ Nestačí `<Directory .../frontend/dist>` - rewrity posílají `/api`, `/admin` do
`backend/public` a ty by auth obešly. Použij **`<Location "/">`**, aby heslo krylo úplně vše:

```apache
    <Location "/">
        AuthType Basic
        AuthName "Web ve vystavbe"
        AuthUserFile /etc/apache2/.htpasswd-profolio
        Require valid-user
    </Location>
    # výjimka: obnova Let's Encrypt certu musí zůstat veřejná
    <Location "/.well-known/">
        Require all granted
    </Location>
```

```bash
# heslo (samostatný soubor, ne sdílený s jinými weby):
sudo htpasswd -c /etc/apache2/.htpasswd-profolio klient
sudo systemctl reload apache2
```

Sundání hesla = smazat `<Location "/">` blok + `sudo systemctl reload apache2`.
(SPA za basic auth funguje - prohlížeč přiloží heslo i k `/api` voláním.)

---

## Zálohy databáze (cron)

`backend/database/database.sqlite` obsahuje veškerý obsah i přijaté poptávky - na SD kartě
Raspberry zálohuj pravidelně. Jednoduchý tahák (denně, rotace 14 dní):

```sh
# /etc/cron.daily/profolio-backup   (nezapomeň: sudo chmod +x)
#!/bin/sh
SRC=/var/www/example.com/backend/database/database.sqlite
DEST=/var/backups/profolio
mkdir -p "$DEST"
cp "$SRC" "$DEST/database-$(date +%F).sqlite"
find "$DEST" -name 'database-*.sqlite' -mtime +14 -delete
```

> Pro rušnější DB je čistší `sqlite3 "$SRC" ".backup" "$DEST/..."` (konzistentní kopie i při
> souběžném zápisu) - vyžaduje balíček `sqlite3`. Pro málo vytížený web stačí `cp`.

---

## Užitečné příkazy

```bash
# reset rate-limitu (odemkne zamčený login / vynuluje počítadla poptávek):
rm -f /var/www/example.com/backend/storage/ratelimit/*.json

# logy - posledních 50 řádků:
sudo tail -n 50 /var/log/apache2/example.com_error.log

# logy živě (follow - vypisuje nové řádky v reálném čase, Ctrl+C ukončí):
sudo tail -f /var/log/apache2/example.com_error.log

# -F místo -f přežije i rotaci logu (logrotate) - vhodné pro delší sledování:
sudo tail -F /var/log/apache2/example.com_error.log
```

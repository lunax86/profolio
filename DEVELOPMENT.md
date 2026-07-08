[← Zpět na README](./README.md)

# Vývoj

Lokální vývoj: první spuštění, dev servery, test na mobilu. Nasazení je
v [DEPLOYMENT.md](./DEPLOYMENT.md), provoz běžícího webu v [RUNBOOK.md](./RUNBOOK.md),
architektura a konvence v [CLAUDE.md](./CLAUDE.md).

---

## První spuštění

### Backend (z `backend/`)

```bash
cp .env.example .env
composer install
php database/migrate.php     # vytvoří schéma v database/database.sqlite
php database/seed.php        # výchozí admin + demo obsah
php -S localhost:8000 -t public
```

- API: <http://localhost:8000/api/settings>
- Administrace: <http://localhost:8000/admin> (výchozí `admin@example.com` / `admin123`)
- Swagger: <http://localhost:8000/swagger>

### Frontend (z `frontend/`)

```bash
npm install
npm run dev
```

Web běží na <http://localhost:5173> (API i obrázky proxuje na backend `:8000`).

---

## Test na mobilu v lokální síti

Otevřít dev build na telefonu ve stejné Wi-Fi (užitečné pro věci, které se na reálném
mobilním prohlížeči chovají jinak než v DevTools).

```bash
# terminál 1 - backend (z backend/):
php -S localhost:8000 -t public

# terminál 2 - frontend (z frontend/):
npm run dev -- --host
```

Pak na telefonu otevři `http://<IP-počítače>:5173` (stejná Wi-Fi).

```bash
# IP počítače v LAN:
ipconfig getifaddr en0        # macOS (Wi-Fi bývá en0)
# příchozí spojení nesmí blokovat firewall:
/usr/libexec/ApplicationFirewall/socketfilterfw --getglobalstate   # macOS
```

- **`--host`**: bez něj Vite poslouchá jen na `localhost` a z telefonu je nedostupný.
  S ním poslouchá na všech rozhraních (`0.0.0.0`).
- **`--`** za `npm run dev` předá zbylé argumenty do skriptu, takže se spustí
  `vite --host`. Bez `--` by npm `--host` interpretoval jako svůj vlastní flag.
- Backend může zůstat na `localhost:8000`, telefon k němu nesahá přímo, jde přes
  Vite proxy (`/api`, `/uploads`), která běží na počítači.

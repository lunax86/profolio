[← Zpět na README](./README.md)

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Přehled

Jednostránkový firemní web. Dvě samostatné aplikace, které spolu mluví přes REST API:

- **`backend/`** - PHP 8.2 (OOP), SQLite. Poskytuje veřejné REST API, server-rendered administraci a OpenAPI/Swagger dokumentaci. Bez frameworku - vlastní lehké jádro.
- **`frontend/`** - React 18 + TypeScript (Vite), Tailwind + shadcn-style komponenty. Veřejný one-page web s parallaxem a dark/light režimem.

Tok dat: administrace (PHP) spravuje obsah v SQLite → API vystaví JSON → React ho načte a vykreslí. Poptávkový formulář posílá data přes API zpět do DB a zobrazí je v administraci.

**V produkci** Apache servíruje statický React build jako DocumentRoot; cesty `/api`, `/admin`, `/uploads` a **všechny „stránky" (i `/`)** jdou přes PHP. Stránky proto, aby se do `<head>` serverově vkládala SEO meta (viz `Support\SeoRenderer`). Krok-za-krokem nasazení je v **`DEPLOYMENT.md`**.

## Příkazy

### Backend (spouštět z `backend/`)
```bash
composer install                 # závislosti (nebo `php composer.phar install`, pokud máš phar; není verzovaný v repu)
php database/migrate.php          # vytvoří schéma v database/database.sqlite
php database/seed.php             # výchozí admin + demo obsah
php -S localhost:8000 -t public   # dev server → API, /admin, /swagger
find src -name '*.php' -exec php -l {} \;   # lint
composer cs                       # PHP-CS-Fixer (composer cs:check = jen kontrola)
```
Výchozí admin a JWT tajný klíč se berou z `.env` (zkopíruj z `.env.example`). Seed vytvoří admina `ADMIN_EMAIL`/`ADMIN_PASSWORD`.

### Frontend (spouštět z `frontend/`)
```bash
npm install
npm run dev       # Vite dev server na :5173 (proxuje /api a /uploads na :8000)
npm run build     # tsc -b && vite build → dist/
npm run format    # Prettier (+ tailwind plugin řadí utility třídy); format:check = kontrola
```
Pro plný běh musí backend běžet na `:8000` (viz proxy v `vite.config.ts`). Test na mobilu v lokální síti a další dev taháky jsou v **`DEVELOPMENT.md`**.

## Architektura backendu

Front controller **`public/index.php`** je jediný vstupní bod. V pořadí řeší: CORS → statické `/uploads/*` → `/admin*` (deleguje na `admin/index.php`) → `/swagger` + `/api/openapi.json` (**jen mimo produkci**, `APP_ENV != production`) → `/robots.txt` + `/sitemap.xml` → **cesty mimo `/api`** = SPA stránka (vrátí `frontend/dist/index.html` se SEO meta vloženými přes `Support\SeoRenderer`) → jinak REST routy přes `Core\Router` (`/api/*`).

- **`src/Core/`** - jádro: `Router` (regex cesty s `{param}` + middleware), `Request`/`Response`, `Database` (PDO/SQLite singleton), `Config` (čte `.env` přes phpdotenv).
- **`src/Repository/`** - jediná vrstva přístupu k DB (PDO, prepared statements). Kontrolery ani admin nesmí sahat na PDO přímo, jdou přes repozitáře.
- **`src/Controller/Api/`** - REST kontrolery. **OpenAPI atributy (`#[OA\...]`) na metodách jsou zdroj pravdy pro Swagger** - každá operace musí mít alespoň jednu `OA\Response`, jinak `swagger-php` scan spadne. `/api/openapi.json` scanuje složku `src/Controller`.
- **`src/Support/`** - `Jwt` (vlastní HS256, žádná externí lib - firebase/php-jwt bylo odstraněno kvůli security advisory), `Auth` (JWT middleware pro API + session pro admin UI), `Validator`, `Csrf`, `Uploader`, **`RateLimiter`** (souborový, klouzavé okno - anti-spam formuláře + brute-force ochrana loginu), **`SeoRenderer`** (vkládá title/description/OG/Twitter/JSON-LD do HTML shellu z nastavení), **`Clock`** (časová zóna webu z nastavení `timezone`, fallback `Europe/Prague`).
- **`admin/`** - server-rendered administrace. `admin/index.php` je akční router (`/admin/{action}`) se session auth a CSRF; šablony v `admin/views/`, layout v `admin/layout/`.

**Autorizace má dvě cesty:** admin API endpointy chrání `Auth::apiMiddleware()` (Bearer JWT); admin UI používá PHP session (`Auth::login/check`) + CSRF token v každém formuláři.

## Klíčové funkce a kde jsou

Obsah spravuje administrace přes tabulku `site_settings` (**key/value**); `GET /api/settings` vrací **všechny** klíče, takže nové nastavení (`seo_title`, `privacy_policy`, `favicon_path`, …) je hned dostupné frontendu i `SeoRenderer` bez zásahu do API.

- **Poptávky** (`inquiries`): anti-spam v `PublicController::createInquiry` (honeypot `website` + time-trap `elapsed` + IP rate-limit). Sloupec `is_archived` → v adminu přepínač Aktivní/Archiv; smazat lze **jen** archivované (`InquiryRepository::deleteArchived`).
- **Návštěvnost** (`page_views`, `PageViewRepository`): `POST /api/hit` zapíše denně solený hash návštěvníka (bez cookies, IP se neukládá čitelně); dashboard ukazuje unikáty za den.
- **SEO**: `SeoRenderer` + nastavení `seo_title`/`seo_description`/`seo_image`/`seo_index` (přepínač indexování ovládá i `robots.txt`).
- **GDPR**: `privacy_policy` (editovatelný text v Nastavení) → modál na frontendu + info u formuláře.
- **Login**: rate-limit (`RateLimiter::tooMany`/`record`, 5 neúspěchů / 15 min na IP).
- **Admin účty** (`admin_users`, sekce Účet): dvě role přes sloupec `is_super`. Super admin (účet ze seedu, nelze smazat) spravuje ostatní; běžný admin mění jen svoje heslo/e-mail. Každý mění svůj účet po zadání současného hesla. Migrace povýší nejstarší účet na super admina, když žádný není.
- **Časy**: v DB se ukládají v UTC (`datetime('now')`). Zobrazení (admin views) i výpočet „dne" pro návštěvnost jdou přes `Support\Clock`, který respektuje nastavení `timezone` (default `Europe/Prague`). Nezobrazovat `created_at` syrově, vždy přes `Clock::formatUtc()`.

Migrace (`database/migrate.php`) je **idempotentní** - nové sloupce se přidávají přes `PRAGMA table_info` + `ALTER TABLE`, výchozí nastavení přes `INSERT OR IGNORE`, takže lze bezpečně spouštět opakovaně.

## Architektura frontendu

- **`src/lib/api.ts`** - typovaný API klient a všechny doménové typy (`SiteSettings`, `Service`, `PortfolioItem`). Jediné místo, kde se volá `fetch`.
- **`src/lib/theme.tsx`** - `ThemeProvider` přepíná třídu `.dark` na `<html>` (Tailwind `darkMode: 'class'`), ukládá do localStorage.
- **`src/components/ui/`** - shadcn-style primitiva (Button, Card, Input, Textarea) postavená na `cn()` z `lib/utils.ts` a CSS proměnných z `index.css`.
- **`src/components/Icon.tsx`** - všechny ikony jdou přes tenhle obal (`@iconify/react` + sada `lucide`). Nepoužívat jiné ikony přímo.
- **`src/sections/`** - jednotlivé sekce one-page webu (Hero, Services, InquiryForm, Portfolio, Footer). `App.tsx` je načte jedním `Promise.all` a předá dolů.
- **`src/components/PrivacyModal.tsx`** - lehký modál se zásadami GDPR (text z `settings.privacy_policy`), otevírá se z patičky i od formuláře.
- `App.tsx` navíc jednou za relaci pingne `POST /api/hit` (návštěvnost). `InquiryForm` má skryté honeypot pole `website` a měří čas od načtení (`elapsed`) - obojí kvůli anti-spamu.

Barvy jsou HSL CSS proměnné v `index.css` (light + `.dark` blok), mapované v `tailwind.config.js`. Nová barva = přidat proměnnou i mapování.

## Konvence

- PHP: `declare(strict_types=1)`, `final` třídy, typované vlastnosti, `match`. Namespace `App\` → `src/` (PSR-4).
- **Názvy proměnných: popisné, celá slova** (PHP i TS): **žádné 1-3písmenné názvy ani zkratky**: `$matches` ne `$m`, `$inquiry` ne `$q`, `$escapeHtml` ne `$e`, `$value` ne `$val`, `$fileHandle` ne `$fh`. Čitelnost pro údržbu má přednost před úsporností.
- **Formátování** (dev-only, spusť před commitem): frontend **Prettier** + `prettier-plugin-tailwindcss` (zdroj pravdy `.prettierrc.json`: **4 mezery, středníky, jednoduché uvozovky, trailing commas**, plugin řadí utility třídy) → `npm run format`; backend **PHP-CS-Fixer** (`.php-cs-fixer.dist.php`, PSR-12, taky 4 mezery) → `composer cs`. Společný `.editorconfig` (4 mezery všude).
- Uživatelsky viditelné texty jsou česky (včetně validačních hlášek). **Pozor na ASCII uvozovky `"` v PHP double-quoted stringech** - používej typografické `„"` nebo konkatenaci.
- Cílová verze je **PHP 8.2** (composer `platform` je zamčený na 8.2), i když lokálně může běžet novější PHP. `swagger-php` na PHP 8.5 hlásí deprecations - proto se při scanu v `index.php` dočasně vypínají.

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Přehled

Jednostránkový firemní web. Dvě samostatné aplikace, které spolu mluví přes REST API:

- **`backend/`** - PHP 8.2 (OOP), SQLite. Poskytuje veřejné REST API, server-rendered administraci a OpenAPI/Swagger dokumentaci. Bez frameworku - vlastní lehké jádro.
- **`frontend/`** - React 18 + TypeScript (Vite), Tailwind + shadcn-style komponenty. Veřejný one-page web s parallaxem a dark/light režimem.

Tok dat: administrace (PHP) spravuje obsah v SQLite → API vystaví JSON → React ho načte a vykreslí. Poptávkový formulář posílá data přes API zpět do DB a zobrazí je v administraci.

## Příkazy

### Backend (spouštět z `backend/`)
```bash
composer install                 # závislosti (nebo `php composer.phar install`, pokud máš phar; není verzovaný v repu)
php database/migrate.php          # vytvoří schéma v database/database.sqlite
php database/seed.php             # výchozí admin + demo obsah
php -S localhost:8000 -t public   # dev server → API, /admin, /swagger
find src -name '*.php' -exec php -l {} \;   # lint
```
Výchozí admin a JWT tajný klíč se berou z `.env` (zkopíruj z `.env.example`). Seed vytvoří admina `ADMIN_EMAIL`/`ADMIN_PASSWORD`.

### Frontend (spouštět z `frontend/`)
```bash
npm install
npm run dev       # Vite dev server na :5173 (proxuje /api a /uploads na :8000)
npm run build     # tsc -b && vite build → dist/
```
Pro plný běh musí backend běžet na `:8000` (viz proxy v `vite.config.ts`).

## Architektura backendu

Front controller **`public/index.php`** je jediný vstupní bod. V pořadí řeší: CORS → statické `/uploads/*` → `/admin*` (deleguje na `admin/index.php`) → `/swagger` a `/api/openapi.json` → jinak REST routy přes `Core\Router`.

- **`src/Core/`** - jádro: `Router` (regex cesty s `{param}` + middleware), `Request`/`Response`, `Database` (PDO/SQLite singleton), `Config` (čte `.env` přes phpdotenv).
- **`src/Repository/`** - jediná vrstva přístupu k DB (PDO, prepared statements). Kontrolery ani admin nesmí sahat na PDO přímo, jdou přes repozitáře.
- **`src/Controller/Api/`** - REST kontrolery. **OpenAPI atributy (`#[OA\...]`) na metodách jsou zdroj pravdy pro Swagger** - každá operace musí mít alespoň jednu `OA\Response`, jinak `swagger-php` scan spadne. `/api/openapi.json` scanuje složku `src/Controller`.
- **`src/Support/`** - `Jwt` (vlastní HS256, žádná externí lib - firebase/php-jwt bylo odstraněno kvůli security advisory), `Auth` (JWT middleware pro API + session pro admin UI), `Validator`, `Csrf`, `Uploader`.
- **`admin/`** - server-rendered administrace. `admin/index.php` je akční router (`/admin/{action}`) se session auth a CSRF; šablony v `admin/views/`, layout v `admin/layout/`.

**Autorizace má dvě cesty:** admin API endpointy chrání `Auth::apiMiddleware()` (Bearer JWT); admin UI používá PHP session (`Auth::login/check`) + CSRF token v každém formuláři.

## Architektura frontendu

- **`src/lib/api.ts`** - typovaný API klient a všechny doménové typy (`SiteSettings`, `Service`, `PortfolioItem`). Jediné místo, kde se volá `fetch`.
- **`src/lib/theme.tsx`** - `ThemeProvider` přepíná třídu `.dark` na `<html>` (Tailwind `darkMode: 'class'`), ukládá do localStorage.
- **`src/components/ui/`** - shadcn-style primitiva (Button, Card, Input, Textarea) postavená na `cn()` z `lib/utils.ts` a CSS proměnných z `index.css`.
- **`src/components/Icon.tsx`** - všechny ikony jdou přes tenhle obal (`@iconify/react` + sada `lucide`). Nepoužívat jiné ikony přímo.
- **`src/sections/`** - jednotlivé sekce one-page webu (Hero, Services, InquiryForm, Portfolio, Footer). `App.tsx` je načte jedním `Promise.all` a předá dolů.

Barvy jsou HSL CSS proměnné v `index.css` (light + `.dark` blok), mapované v `tailwind.config.js`. Nová barva = přidat proměnnou i mapování.

## Konvence

- PHP: `declare(strict_types=1)`, `final` třídy, typované vlastnosti, `match`. Namespace `App\` → `src/` (PSR-4).
- Uživatelsky viditelné texty jsou česky (včetně validačních hlášek). **Pozor na ASCII uvozovky `"` v PHP double-quoted stringech** - používej typografické `„"` nebo konkatenaci.
- Cílová verze je **PHP 8.2** (composer `platform` je zamčený na 8.2), i když lokálně může běžet novější PHP. `swagger-php` na PHP 8.5 hlásí deprecations - proto se při scanu v `index.php` dočasně vypínají.

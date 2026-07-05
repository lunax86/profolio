# Firemní web

Jednostránkový firemní web s parallaxem, dark/light režimem, poptávkovým formulářem a administrací.

- **backend/** - PHP 8.2 (OOP), SQLite: REST API + administrace + Swagger
- **frontend/** - React + TypeScript (Vite), Tailwind + shadcn styl, ikony lucide

## Rychlý start

### 1. Backend
```bash
cd backend
cp .env.example .env
composer install
php database/migrate.php
php database/seed.php
php -S localhost:8000 -t public
```

- Web API: <http://localhost:8000/api/settings>
- Administrace: <http://localhost:8000/admin> (výchozí `admin@example.com` / `admin123`)
- Swagger: <http://localhost:8000/swagger>

### 2. Frontend
```bash
cd frontend
npm install
npm run dev
```
Web běží na <http://localhost:5173> (API i obrázky proxuje na backend `:8000`).

## Co kde upravit

| Chci změnit | Kde |
|---|---|
| Titulek, slogan, úvodní fotku, kontakt | Administrace → Nastavení |
| Služby (cards) | Administrace → Služby |
| Ukázky práce (fotky) | Administrace → Portfolio |
| Přijaté poptávky | Administrace → Poptávky |

Podrobná architektura je v [CLAUDE.md](./CLAUDE.md), přehled projektu v [PLAN.md](./PLAN.md).

# Firemní web

Jednostránkový firemní web s parallaxem, dark/light režimem, poptávkovým formulářem
a serverovou administrací. Dvě samostatné aplikace komunikující přes REST API:

- **backend/** - PHP 8.2 (OOP), SQLite: REST API + administrace + Swagger
- **frontend/** - React + TypeScript (Vite), Tailwind + shadcn styl, ikony lucide

Jak projekt rozjet lokálně najdeš v [DEVELOPMENT.md](./DEVELOPMENT.md).

## Struktura projektu

```
profolio/
├── backend/          # PHP 8.2 (OOP), SQLite: REST API + administrace + Swagger
│   ├── public/       # webroot (index.php = jediný vstupní bod)
│   ├── src/          # aplikační kód (PSR-4, namespace App\)
│   ├── admin/        # server-rendered administrace (PHP šablony)
│   └── database/     # migrace + seed (SQLite)
└── frontend/         # React (Vite + TS): veřejný one-page web
    └── src/
        ├── components/   # + ui/ (shadcn-style primitiva)
        ├── sections/     # Hero, Services, InquiryForm, Portfolio, Footer
        └── lib/          # API klient, theme
```

Tok dat: administrace (PHP) spravuje obsah v SQLite → API ho vystaví jako JSON →
React ho načte a vykreslí. Poptávkový formulář posílá data přes API zpět do DB.

## Dokumentace

| Soubor | K čemu |
|---|---|
| [DEVELOPMENT.md](./DEVELOPMENT.md) | Lokální vývoj: spuštění, dev servery, test na mobilu |
| [DEPLOYMENT.md](./DEPLOYMENT.md) | Nasazení na server krok za krokem |
| [RUNBOOK.md](./RUNBOOK.md) | Provoz a údržba běžícího webu |

Podrobná architektura a konvence jsou v [CLAUDE.md](./CLAUDE.md) (primárně pro vývoj
a práci s AI asistentem).

## Správa obsahu

Obsah webu se edituje v administraci (`/admin`), ne v kódu:

| Chci změnit | Kde v administraci |
|---|---|
| Titulek, slogan, úvodní fotku, kontakt | Nastavení |
| Služby (karty) | Služby |
| Ukázky práce (fotky) | Portfolio |
| Přijaté poptávky | Poptávky |

# Profolio

Konfigurovatelný jednostránkový web pro OSVČ a živnostníky: „O mně", ukázky práce
s posuvníkem před/po, služby, reference, poptávkový formulář, parallax a dark/light režim.
Sekce jsou modulární (zapnout/vypnout, přeuspořádat) a barevné téma se mění v administraci.
Dvě samostatné aplikace komunikující přes REST API:

- **backend/** - PHP 8.2 (OOP), SQLite: REST API + serverová administrace
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
        ├── sections/     # Hero, About, Portfolio (před/po), Services, Reviews, InquiryForm, Instagram, Footer
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
| Název, slogan, kontakt, favicon, časovou zónu | Obecné |
| Úvodní sekci (hero) | Úvod |
| Medailonek „O mně" | O mně |
| Ukázky práce (fotky, před/po) | Ukázky |
| Služby (karty) | Služby |
| Reference (recenze) | Recenze |
| Patičku a sociální sítě | Patička |
| Barvy webu | Vzhled |
| Viditelnost a pořadí sekcí | Sekce a pořadí |
| SEO / zásady ochrany údajů (GDPR) | SEO / GDPR |
| Přijaté poptávky | Poptávky |

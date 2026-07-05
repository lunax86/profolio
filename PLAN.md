# Přehled projektu – Firemní web s administrací

Jednostránkový (one-page) firemní web s parallax scrollem: React frontend + PHP administrace a REST API.

---

## 1. Architektura

Projekt je rozdělený na dvě samostatné části, které spolu komunikují přes REST API:

```
profolio/
├── backend/          # PHP 8.2, OOP – API + administrace + Swagger
│   ├── public/       # webroot (index.php = API router, /admin, /swagger)
│   ├── src/          # aplikační kód (PSR-4, namespace App\)
│   ├── admin/        # server-rendered administrace (PHP šablony)
│   ├── database/     # migrace + seed (SQLite)
│   ├── storage/      # nahrané obrázky (uploads)
│   └── composer.json
│
└── frontend/         # React (Vite + TS) – veřejný one-page web
    ├── src/
    │   ├── components/  # + ui/ (shadcn-style primitiva)
    │   ├── sections/    # Hero, Services, InquiryForm, Portfolio, Footer
    │   └── lib/         # api client, theme
    └── package.json
```

**Tok dat:** Administrace (PHP) spravuje obsah v databázi → API vystaví data jako JSON → React frontend je načte a vykreslí. Poptávkový formulář na frontendu posílá data zpět přes API, uloží se do DB a zobrazí v administraci.

---

## 2. Technologie

### Backend (PHP)
- **PHP 8.2** – typed properties, readonly, constructor promotion, `match`, `final` třídy
- **Composer** + PSR-4 autoloading (`App\`)
- **SQLite** přes PDO – databázová vrstva (repository pattern, jediné místo přístupu k DB)
- Vlastní lehký **router** – bez frameworku
- **JWT** (vlastní HS256, bez externí lib) pro API autentizaci admin endpointů + **PHP session** pro admin UI
- **zircote/swagger-php** – generování OpenAPI 3.0 z PHP atributů, Swagger UI na `/swagger`
- Validace, hashování hesel (`password_hash`), CSRF ochrana v adminu

### Frontend (React)
- **Vite + React 18 + TypeScript**
- **Tailwind CSS** + shadcn-style komponenty (Button, Card, Input, Textarea)
- **Ikony:** Iconify + sada **lucide** (`@iconify/react`), vše přes obal `components/Icon.tsx`
- **Dark/light** – theme provider (přepínač v horní liště, uloženo v localStorage)
- **Parallax** v Hero sekci
- **React Hook Form + Zod** – validace poptávkového formuláře
- Ilustrační fotky: Unsplash / picsum (placeholdery v Hero a v seedu)

---

## 3. Sekce webu (shora dolů)

1. **Horní lišta (sticky menu)** – kontakt z administrace, navigační kotvy, přepínač dark/light.
2. **Hero** – úvodní fotka na pozadí (parallax), title, slogan, CTA tlačítko „Nezávazně poptat".
3. **Naše služby** – responzivní cards (ikona lucide, název, popis), obsah z administrace.
4. **Poptávkový formulář** – jméno, e-mail, telefon, zpráva → odešle na API.
5. **Ukázky práce / Portfolio** – galerie fotek spravovaná v administraci (upload obrázků).
6. **Patička** – kontaktní údaje, copyright.

---

## 4. Datový model (SQLite)

| Tabulka         | Sloupce (hlavní)                                                        |
|-----------------|------------------------------------------------------------------------|
| `admin_users`   | id, email, password_hash, created_at                                   |
| `site_settings` | id, klíč/hodnota – title, slogan, hero_image, contact_email, phone, address, social (JSON) |
| `services`      | id, title, description, icon (lucide název), sort_order                 |
| `portfolio`     | id, title, description, image_path, sort_order                         |
| `inquiries`     | id, name, email, phone, message, created_at, is_read                   |

`site_settings` jako key/value → jeden řádek na položku, snadno rozšiřitelné.

---

## 5. API (REST, JSON) + Swagger

**Veřejné (bez auth):**
- `GET  /api/settings`   – title, slogan, kontakt, hero obrázek
- `GET  /api/services`   – seznam služeb (cards)
- `GET  /api/portfolio`  – ukázky práce (fotky)
- `POST /api/inquiries`  – odeslání poptávky

**Admin (JWT / session):**
- `POST /api/auth/login`
- `CRUD /api/admin/services`
- `CRUD /api/admin/portfolio` (+ upload obrázku, multipart)
- `GET/PUT /api/admin/settings`
- `GET  /api/admin/inquiries` (+ označit přečteno / smazat)

Swagger UI na `/swagger`, OpenAPI JSON na `/api/openapi.json`.

Administrace = server-rendered PHP stránky (`/admin`) s přihlášením a CSRF; views: `dashboard`, `login`, `services`, `portfolio`, `settings`, `inquiries`.

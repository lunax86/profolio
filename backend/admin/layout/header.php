<?php

declare(strict_types=1);

use App\Support\Auth;

/** @var string $title */
$loggedIn = Auth::check();
$navigation = [
    'dashboard' => 'Přehled',
    'inquiries' => 'Poptávky',
    'services' => 'Služby',
    'portfolio' => 'Portfolio',
    'settings' => 'Nastavení',
];
$current = trim(str_replace('/admin', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: ''), '/') ?: 'dashboard';
$escape = static fn (?string $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($title ?? 'Administrace') ?> · Administrace</title>
    <style>
        :root { --bg:#0f172a; --panel:#1e293b; --muted:#94a3b8; --text:#e2e8f0; --brand:#6366f1; --border:#334155; --danger:#ef4444; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; background:#f1f5f9; color:#0f172a; }
        a { color: var(--brand); text-decoration: none; }
        .topbar { background: var(--bg); color: var(--text); padding: 0 1.5rem; display:flex; align-items:center; gap:1.5rem; height:56px; }
        .topbar .brand { font-weight:700; }
        .topbar nav { display:flex; gap:1rem; flex:1; align-items:center; }
        .topbar nav a { color: var(--muted); padding: .35rem .1rem; border-bottom: 2px solid transparent; }
        .topbar nav a.active, .topbar nav a:hover { color:#fff; border-bottom-color: var(--brand); }
        .topbar nav a.logout { margin-left:auto; color: var(--muted); border-bottom:0; }
        .burger { display:none; margin-left:auto; cursor:pointer; color:#fff; font-size:1.5rem; line-height:1; padding:.25rem .5rem; user-select:none; }
        @media (max-width: 640px) {
            .topbar { flex-wrap:wrap; height:auto; min-height:56px; padding:.5rem 1rem; gap:.5rem; }
            .burger { display:block; }
            .topbar nav { display:none; flex-basis:100%; flex-direction:column; gap:0; }
            .nav-toggle:checked ~ nav { display:flex; }
            .topbar nav a { width:100%; padding:.7rem .25rem; border-bottom:1px solid var(--border); }
            .topbar nav a.logout { margin-left:0; }
            .card { overflow-x:auto; }
        }
        .wrap { max-width: 960px; margin: 2rem auto; padding: 0 1.5rem; }
        h1 { font-size: 1.5rem; margin-top: 0; }
        .card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:1.25rem; margin-bottom:1.25rem; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        label { display:block; font-size:.85rem; font-weight:600; margin:.6rem 0 .25rem; }
        input, textarea, select { width:100%; padding:.55rem .7rem; border:1px solid #cbd5e1; border-radius:8px; font:inherit; }
        textarea { min-height: 80px; }
        button { background: var(--brand); color:#fff; border:0; padding:.55rem 1rem; border-radius:8px; font:inherit; font-weight:600; cursor:pointer; }
        button.ghost { background:#e2e8f0; color:#0f172a; }
        button.danger { background: var(--danger); }
        table { width:100%; border-collapse: collapse; }
        th, td { text-align:left; padding:.6rem; border-bottom:1px solid #e2e8f0; vertical-align: top; }
        th { font-size:.8rem; color:#64748b; text-transform: uppercase; letter-spacing:.03em; }
        .grid { display:grid; grid-template-columns: repeat(auto-fill,minmax(220px,1fr)); gap:1rem; }
        .thumb { width:100%; height:120px; object-fit:cover; border-radius:8px; }
        .stat { font-size:2rem; font-weight:700; }
        .row { display:flex; gap:.5rem; flex-wrap:wrap; }
        .badge { background:#e0e7ff; color:#4338ca; border-radius:999px; padding:.1rem .5rem; font-size:.75rem; }
        .alert { background:#fee2e2; color:#991b1b; padding:.7rem 1rem; border-radius:8px; margin-bottom:1rem; }
    </style>
</head>
<body>
<?php if ($loggedIn): ?>
<div class="topbar">
    <span class="brand">⚙ Administrace</span>
    <input type="checkbox" id="navToggle" class="nav-toggle" hidden>
    <label for="navToggle" class="burger" aria-label="Menu">☰</label>
    <nav>
        <?php foreach ($navigation as $key => $label): ?>
            <a href="/admin/<?= $key ?>" class="<?= $current === $key ? 'active' : '' ?>"><?= $label ?></a>
        <?php endforeach; ?>
        <a href="/admin/logout" class="logout">Odhlásit</a>
    </nav>
</div>
<?php endif; ?>
<div class="wrap">

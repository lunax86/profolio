<?php

declare(strict_types=1);

use App\Support\Auth;

/** @var string $title */
$loggedIn = Auth::check();
$currentUserEmail = Auth::user()['email'] ?? '';

// Navigace seskupená do sekcí. Přidání položky menu nerozbije layout, jen přibude řádek.
$navigationGroups = [
    ['section' => null, 'items' => [
        'dashboard' => ['Přehled', 'dashboard'],
        'inquiries' => ['Poptávky', 'inbox'],
    ]],
    ['section' => 'Obsah', 'items' => [
        'services' => ['Služby', 'briefcase'],
        'portfolio' => ['Portfolio', 'image'],
        'settings' => ['Nastavení', 'settings'],
    ]],
    ['section' => 'Systém', 'items' => [
        'security' => ['Bezpečnost', 'shield'],
        'smtp' => ['SMTP', 'mail'],
        'account' => ['Účet', 'user'],
    ]],
];
$current = trim(str_replace('/admin', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: ''), '/') ?: 'dashboard';
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= escape($title ?? 'Administrace') ?> · Administrace</title>
    <style><?php readfile(__DIR__ . '/../admin.css'); ?></style>
    <script>
        // Režim nastav ještě před vykreslením těla, ať neproblikne světlý.
        (function () {
            try {
                var saved = localStorage.getItem('admin-theme');
                if (saved === 'dark' || saved === 'light') {
                    document.documentElement.setAttribute('data-theme', saved);
                }
            } catch (error) { /* localStorage nedostupné: necháme systémový režim */ }
        })();
    </script>
</head>
<body>
<?php require __DIR__ . '/sprite.php'; ?>
<?php if ($loggedIn): ?>
<div class="app" id="app">
    <aside class="side">
        <div class="side-brand">
            <span class="mark"><?= icon('cube') ?></span>
            Administrace
        </div>
        <nav class="side-nav">
            <?php foreach ($navigationGroups as $group): ?>
                <?php if ($group['section'] !== null): ?>
                    <div class="side-sec"><?= escape($group['section']) ?></div>
                <?php endif; ?>
                <?php foreach ($group['items'] as $key => [$label, $iconName]): ?>
                    <a href="/admin/<?= $key ?>" class="nav-item<?= $current === $key ? ' active' : '' ?>">
                        <?= icon($iconName) ?> <?= escape($label) ?>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </nav>
        <div class="side-foot">
            <div class="side-user">
                <span class="av"><?= icon('user', 'ic') ?></span>
                <span class="who"><b><?= escape($currentUserEmail) ?></b><span>administrace</span></span>
            </div>
            <a href="/admin/logout" class="nav-item"><?= icon('logout') ?> Odhlásit</a>
        </div>
    </aside>
    <div class="nav-backdrop" id="navBackdrop" aria-hidden="true"></div>
    <div class="main">
        <div class="topbar">
            <button class="burger" id="burger" type="button" aria-label="Menu"><?= icon('menu') ?></button>
            <h1><?= escape($title ?? 'Administrace') ?></h1>
            <button class="theme-btn" id="themeBtn" type="button">
                <?= icon('sun', 'ic') ?><span>Režim</span>
            </button>
        </div>
        <div class="content">
<?php else: ?>
<div class="login-shell">
<?php endif; ?>

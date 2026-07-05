<?php

declare(strict_types=1);

/** @var int $servicesCount @var int $portfolioCount @var int $unread */
?>
<h1>Přehled</h1>
<div class="grid">
    <div class="card">
        <div class="stat"><?= (int) $unread ?></div>
        <div>Nepřečtené poptávky</div>
        <p><a href="/admin/inquiries">Zobrazit poptávky →</a></p>
    </div>
    <div class="card">
        <div class="stat"><?= (int) $servicesCount ?></div>
        <div>Služby</div>
        <p><a href="/admin/services">Spravovat služby →</a></p>
    </div>
    <div class="card">
        <div class="stat"><?= (int) $portfolioCount ?></div>
        <div>Ukázky práce</div>
        <p><a href="/admin/portfolio">Spravovat portfolio →</a></p>
    </div>
</div>
<div class="card">
    <p>Obsah webu upravíte v sekci <a href="/admin/settings">Nastavení</a> (titulek, slogan, kontaktní údaje, úvodní fotka).</p>
    <?php if (\App\Core\Config::get('APP_ENV') !== 'production'): ?>
    <p>API dokumentace: <a href="/swagger" target="_blank">/swagger</a></p>
    <?php endif; ?>
</div>

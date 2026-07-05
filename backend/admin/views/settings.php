<?php

declare(strict_types=1);

use App\Support\Csrf;

/** @var array<string, string> $settings */
$e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$val = static fn (string $k): string => htmlspecialchars((string) ($settings[$k] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<h1>Nastavení webu</h1>
<form method="post" action="/admin/settings">
    <?= Csrf::field() ?>
    <div class="card">
        <h2 style="margin-top:0;font-size:1.1rem;">Úvodní sekce (hero)</h2>
        <label>Název webu</label>
        <input type="text" name="site_title" value="<?= $val('site_title') ?>">
        <label>Hlavní titulek</label>
        <input type="text" name="hero_title" value="<?= $val('hero_title') ?>">
        <label>Slogan</label>
        <input type="text" name="hero_slogan" value="<?= $val('hero_slogan') ?>">
        <label>URL úvodní fotky</label>
        <input type="url" name="hero_image" value="<?= $val('hero_image') ?>">
    </div>
    <div class="card">
        <h2 style="margin-top:0;font-size:1.1rem;">Kontaktní údaje</h2>
        <label>E-mail</label>
        <input type="email" name="contact_email" value="<?= $val('contact_email') ?>">
        <label>Telefon</label>
        <input type="text" name="contact_phone" value="<?= $val('contact_phone') ?>">
        <label>Adresa</label>
        <input type="text" name="contact_address" value="<?= $val('contact_address') ?>">
        <label>Facebook</label>
        <input type="url" name="social_facebook" value="<?= $val('social_facebook') ?>">
        <label>Instagram</label>
        <input type="url" name="social_instagram" value="<?= $val('social_instagram') ?>">
    </div>
    <button type="submit">Uložit nastavení</button>
</form>

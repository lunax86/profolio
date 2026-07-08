<?php

declare(strict_types=1);

use App\Support\Csrf;

/** @var array<string, string> $settings */
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$setting = static fn (string $key): string => htmlspecialchars((string) ($settings[$key] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<h1>Nastavení webu</h1>
<?php if (!empty($_GET['err'])): ?>
<div class="alert"><?= $escape($_GET['err']) ?></div>
<?php endif; ?>
<form method="post" action="/admin/settings" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <div class="card">
        <h2 style="margin-top:0;font-size:1.1rem;">Úvodní sekce (hero)</h2>
        <label>Název webu</label>
        <input type="text" name="site_title" value="<?= $setting('site_title') ?>">
        <label>Hlavní titulek</label>
        <input type="text" name="hero_title" value="<?= $setting('hero_title') ?>">
        <label>Slogan</label>
        <input type="text" name="hero_slogan" value="<?= $setting('hero_slogan') ?>">
        <label>URL úvodní fotky</label>
        <input type="url" name="hero_image" value="<?= $setting('hero_image') ?>">
    </div>
    <div class="card">
        <h2 style="margin-top:0;font-size:1.1rem;">Kontaktní údaje</h2>
        <label>E-mail</label>
        <input type="email" name="contact_email" value="<?= $setting('contact_email') ?>">
        <label>Telefon</label>
        <input type="text" name="contact_phone" value="<?= $setting('contact_phone') ?>">
        <label>Adresa</label>
        <input type="text" name="contact_address" value="<?= $setting('contact_address') ?>">
        <label>Facebook</label>
        <input type="url" name="social_facebook" value="<?= $setting('social_facebook') ?>">
        <label>Instagram</label>
        <input type="url" name="social_instagram" value="<?= $setting('social_instagram') ?>">
    </div>
    <div class="card">
        <h2 style="margin-top:0;font-size:1.1rem;">Ikona webu (favicon)</h2>
        <p style="color:#64748b;font-size:.85rem;margin:.25rem 0 .5rem;">
            Ikonka v záložce prohlížeče. Ideálně čtvercový PNG (např. 512×512). Prázdné = výchozí ikona.
        </p>
        <div style="display:flex;align-items:center;gap:1rem;">
            <img src="<?= $setting('favicon_path') ?: '/favicon.svg' ?>" alt="favicon" width="48" height="48"
                 style="border:1px solid #e2e8f0;border-radius:8px;background:#fff;object-fit:contain;padding:4px;">
            <div style="flex:1;">
                <input type="file" name="favicon" accept="image/png,image/jpeg,image/webp">
                <?php if ($setting('favicon_path')): ?>
                <label style="font-weight:400;margin-top:.5rem;display:flex;align-items:center;gap:.4rem;">
                    <input type="checkbox" name="favicon_remove" value="1" style="width:auto;"> Odebrat vlastní ikonu (vrátit výchozí)
                </label>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="card">
        <h2 style="margin-top:0;font-size:1.1rem;">SEO a vyhledávače</h2>
        <p style="color:#64748b;font-size:.85rem;margin:.25rem 0 .75rem;">
            Jak se web ukáže ve vyhledávání a při sdílení na sítích. Prázdná pole se doplní automaticky z názvu a sloganu.
        </p>

        <label>SEO titulek <span style="font-weight:400;color:#94a3b8;">(ideálně do ~60 znaků)</span></label>
        <input type="text" name="seo_title" maxlength="70" value="<?= $setting('seo_title') ?>"
               placeholder="<?= $setting('site_title') ?><?= $settings['hero_slogan'] ?? '' ? ' - ' . $setting('hero_slogan') : '' ?>">

        <label>SEO popis <span style="font-weight:400;color:#94a3b8;">(ideálně do ~155 znaků)</span></label>
        <textarea name="seo_description" rows="3" maxlength="180" placeholder="<?= $setting('hero_slogan') ?>"><?= $setting('seo_description') ?></textarea>

        <label>Obrázek pro sdílení - URL <span style="font-weight:400;color:#94a3b8;">(fallback: úvodní fotka)</span></label>
        <input type="url" name="seo_image" value="<?= $setting('seo_image') ?>" placeholder="<?= $setting('hero_image') ?>">

        <label>Indexování vyhledávači</label>
        <select name="seo_index">
            <option value="1" <?= ($settings['seo_index'] ?? '1') !== '0' ? 'selected' : '' ?>>Ano - web se smí zobrazovat ve vyhledávání</option>
            <option value="0" <?= ($settings['seo_index'] ?? '1') === '0' ? 'selected' : '' ?>>Ne - skrýt web před vyhledávači (noindex)</option>
        </select>
    </div>
    <div class="card">
        <h2 style="margin-top:0;font-size:1.1rem;">Zásady ochrany osobních údajů (GDPR)</h2>
        <p style="color:#64748b;font-size:.85rem;margin:.25rem 0 .5rem;">
            Text se zobrazí návštěvníkům přes odkaz u formuláře a v patičce. Doplňte prosím údaje své firmy (název, IČO, sídlo).
        </p>
        <textarea name="privacy_policy" rows="14"><?= $setting('privacy_policy') ?></textarea>
    </div>
    <button type="submit">Uložit nastavení</button>
</form>

<script>
    // Živé počítadlo znaků u SEO polí (title/description).
    document.querySelectorAll('input[name="seo_title"], textarea[name="seo_description"]').forEach(function (el) {
        var hint = document.createElement('div');
        hint.style.cssText = 'font-size:.75rem;color:#94a3b8;margin-top:.15rem;';
        var update = function () {
            hint.textContent = el.value.length + ' / ' + el.getAttribute('maxlength') + ' znaků';
        };
        el.insertAdjacentElement('afterend', hint);
        update();
        el.addEventListener('input', update);
    });
</script>

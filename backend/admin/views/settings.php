<?php

declare(strict_types=1);

use App\Support\Csrf;

/** @var array<string, string> $settings */
$get = static fn (string $key): string => (string) ($settings[$key] ?? '');

// Nabídka časových zón (UTC + Evropa) s aktuálním posunem.
$currentZone = $settings['timezone'] ?? 'Europe/Prague';
$now = new DateTimeImmutable('now');
$zoneOptions = '';
foreach (array_merge(['UTC'], DateTimeZone::listIdentifiers(DateTimeZone::EUROPE)) as $zone) {
    $offsetSeconds = (new DateTimeZone($zone))->getOffset($now);
    $offsetLabel = sprintf('UTC%s%02d:%02d', $offsetSeconds < 0 ? '-' : '+', intdiv(abs($offsetSeconds), 3600), intdiv(abs($offsetSeconds) % 3600, 60));
    $zoneOptions .= '<option value="' . escape($zone) . '"' . ($zone === $currentZone ? ' selected' : '') . '>' . escape($zone . ' (' . $offsetLabel . ')') . '</option>';
}

$indexOn = ($settings['seo_index'] ?? '1') !== '0';
$seoTitlePlaceholder = $get('site_title') . ($get('hero_slogan') !== '' ? ' - ' . $get('hero_slogan') : '');
?>
<?php if (!empty($_GET['err'])): ?>
<div class="notice notice-err"><?= icon('alert', 'ic ic-sm') ?><?= escape($_GET['err']) ?></div>
<?php endif; ?>

<form method="post" action="/admin/settings" enctype="multipart/form-data">
    <?= Csrf::field() ?>

    <?= card_open('Úvodní sekce (hero)') ?>
        <?= field('Název webu', 'site_title', ['value' => $get('site_title')]) ?>
        <?= field('Hlavní titulek', 'hero_title', ['value' => $get('hero_title')]) ?>
        <?= field('Slogan', 'hero_slogan', ['value' => $get('hero_slogan')]) ?>
        <?= field('URL úvodní fotky', 'hero_image', ['type' => 'url', 'value' => $get('hero_image')]) ?>
    <?= card_close() ?>

    <?= card_open('Kontaktní údaje') ?>
        <?= field('E-mail', 'contact_email', ['type' => 'email', 'value' => $get('contact_email')]) ?>
        <?= field('Telefon', 'contact_phone', ['value' => $get('contact_phone')]) ?>
        <?= field('Adresa', 'contact_address', ['value' => $get('contact_address')]) ?>
        <?= field('Facebook', 'social_facebook', ['type' => 'url', 'value' => $get('social_facebook')]) ?>
        <?= field('Instagram', 'social_instagram', ['type' => 'url', 'value' => $get('social_instagram')]) ?>
    <?= card_close() ?>

    <?= card_open('Ikona webu (favicon)') ?>
        <p class="hint">Ikonka v záložce prohlížeče. Ideálně čtvercový PNG (např. 512×512). Prázdné = výchozí ikona.</p>
        <div class="favicon-row">
            <img class="favicon-prev" src="<?= escape($get('favicon_path') ?: '/favicon.svg') ?>" alt="favicon" width="48" height="48">
            <div class="grow">
                <input type="file" name="favicon" accept="image/png,image/jpeg,image/webp">
                <?php if ($get('favicon_path') !== ''): ?>
                <label class="check"><input type="checkbox" name="favicon_remove" value="1"> Odebrat vlastní ikonu (vrátit výchozí)</label>
                <?php endif; ?>
            </div>
        </div>
    <?= card_close() ?>

    <?= card_open('Časová zóna') ?>
        <p class="hint">V této zóně se v administraci zobrazují časy (poptávky, přihlášení) a počítá se den u návštěvnosti.</p>
        <?= field_wrap('Časová zóna webu', '<select name="timezone">' . $zoneOptions . '</select>') ?>
    <?= card_close() ?>

    <?= card_open('SEO a vyhledávače') ?>
        <p class="hint">Jak se web ukáže ve vyhledávání a při sdílení na sítích. Prázdná pole se doplní automaticky z názvu a sloganu.</p>
        <?= field('SEO titulek', 'seo_title', ['value' => $get('seo_title'), 'maxlength' => 70, 'counter' => true, 'sub' => '(ideálně do ~60 znaků)', 'placeholder' => $seoTitlePlaceholder]) ?>
        <?= field_wrap('SEO popis', '<textarea name="seo_description" id="f-seo_description" rows="3" maxlength="180" placeholder="' . escape($get('hero_slogan')) . '">' . escape($get('seo_description')) . '</textarea><div class="counter" data-counter="#f-seo_description"></div>', '(ideálně do ~155 znaků)') ?>
        <?= field('Obrázek pro sdílení - URL', 'seo_image', ['type' => 'url', 'value' => $get('seo_image'), 'sub' => '(fallback: úvodní fotka)', 'placeholder' => $get('hero_image')]) ?>
        <?= field_wrap('Indexování vyhledávači', '<select name="seo_index">'
            . '<option value="1"' . ($indexOn ? ' selected' : '') . '>Ano - web se smí zobrazovat ve vyhledávání</option>'
            . '<option value="0"' . (!$indexOn ? ' selected' : '') . '>Ne - skrýt web před vyhledávači (noindex)</option>'
            . '</select>') ?>
    <?= card_close() ?>

    <?= card_open('Zásady ochrany osobních údajů (GDPR)') ?>
        <p class="hint">Text se zobrazí návštěvníkům přes odkaz u formuláře a v patičce. Doplňte prosím údaje své firmy (název, IČO, sídlo).</p>
        <?= field_wrap('', '<textarea name="privacy_policy" rows="14">' . escape($get('privacy_policy')) . '</textarea>') ?>
    <?= card_close() ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= icon('check', 'ic ic-sm') ?> Uložit nastavení</button>
    </div>
</form>

<?php

declare(strict_types=1);

use App\Support\Csrf;

/** @var array<string, string> $settings */
/** @var bool $ok */
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
?>
<?php if ($ok): ?>
<div class="notice notice-ok"><?= icon('check', 'ic ic-sm') ?>Uloženo.</div>
<?php endif; ?>
<?php if (!empty($_GET['err'])): ?>
<div class="notice notice-err"><?= icon('alert', 'ic ic-sm') ?><?= escape($_GET['err']) ?></div>
<?php endif; ?>

<form method="post" action="/admin/obecne" enctype="multipart/form-data">
    <?= Csrf::field() ?>

    <?= card_open('Základní údaje', 'Základ, ze kterého dědí úvod, patička i SEO') ?>
        <?= field('Název webu', 'site_title', ['value' => $get('site_title')]) ?>
        <?= field('Slogan / krátký popis', 'slogan', ['value' => $get('slogan'), 'sub' => '(jednou větou; použije se v úvodu, patičce i SEO, pokud tam nevyplníte vlastní)']) ?>
    <?= card_close() ?>

    <?= card_open('Kontaktní údaje') ?>
        <?= field('E-mail', 'contact_email', ['type' => 'email', 'value' => $get('contact_email')]) ?>
        <?= field('Telefon', 'contact_phone', ['value' => $get('contact_phone')]) ?>
        <?= field('Adresa', 'contact_address', ['value' => $get('contact_address')]) ?>
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

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= icon('check', 'ic ic-sm') ?> Uložit</button>
    </div>
</form>

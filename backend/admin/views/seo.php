<?php

declare(strict_types=1);

use App\Support\Csrf;

/** @var array<string, string> $settings */
/** @var bool $ok */
$get = static fn (string $key): string => (string) ($settings[$key] ?? '');

$indexOn = ($settings['seo_index'] ?? '1') !== '0';
// Obecný slogan je základ pro odvozený popis; fallback na hlavní text hera.
$slogan = $get('slogan') !== '' ? $get('slogan') : $get('hero_title');
$seoTitlePlaceholder = $get('site_title') . ($slogan !== '' ? ' - ' . $slogan : '');
?>
<?php if ($ok): ?>
<div class="notice notice-ok"><?= icon('check', 'ic ic-sm') ?>Uloženo.</div>
<?php endif; ?>

<form method="post" action="/admin/seo">
    <?= Csrf::field() ?>

    <?= card_open('SEO a vyhledávače') ?>
        <p class="hint">Jak se web ukáže ve vyhledávání a při sdílení na sítích. Prázdná pole se doplní automaticky z názvu a sloganu.</p>
        <?= field('SEO titulek', 'seo_title', ['value' => $get('seo_title'), 'maxlength' => 70, 'counter' => true, 'sub' => '(ideálně do ~60 znaků)', 'placeholder' => $seoTitlePlaceholder]) ?>
        <?= field_wrap('SEO popis', '<textarea name="seo_description" id="f-seo_description" rows="3" maxlength="180" placeholder="' . escape($slogan) . '">' . escape($get('seo_description')) . '</textarea><div class="counter" data-counter="#f-seo_description"></div>', '(ideálně do ~155 znaků)') ?>
        <?= field('Obrázek pro sdílení - URL', 'seo_image', ['type' => 'url', 'value' => $get('seo_image'), 'sub' => '(fallback: úvodní fotka)', 'placeholder' => $get('hero_image')]) ?>
        <?= field_wrap('Indexování vyhledávači', '<select name="seo_index">'
            . '<option value="1"' . ($indexOn ? ' selected' : '') . '>Ano - web se smí zobrazovat ve vyhledávání</option>'
            . '<option value="0"' . (!$indexOn ? ' selected' : '') . '>Ne - skrýt web před vyhledávači (noindex)</option>'
            . '</select>') ?>
    <?= card_close() ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= icon('check', 'ic ic-sm') ?> Uložit</button>
    </div>
</form>

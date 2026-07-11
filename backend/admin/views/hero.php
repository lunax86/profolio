<?php

declare(strict_types=1);

use App\Support\Csrf;

/** @var array<string, string> $settings */
/** @var bool $ok */
$get = static fn (string $key): string => (string) ($settings[$key] ?? '');
?>
<?php if ($ok): ?>
<div class="notice notice-ok"><?= icon('check', 'ic ic-sm') ?>Uloženo.</div>
<?php endif; ?>

<form method="post" action="/admin/hero">
    <?= Csrf::field() ?>

    <?= card_open('Úvodní sekce (hero)', 'První, co návštěvník uvidí') ?>
        <?= field('Hlavní nadpis (co děláte)', 'hero_title', ['value' => $get('hero_title'), 'placeholder' => 'např. Rekonstrukce bytů na klíč', 'sub' => '(velký nadpis; prázdné = název webu)']) ?>
        <?= field('Místo (kde působíte)', 'hero_place', ['value' => $get('hero_place'), 'placeholder' => 'např. Praha a okolí', 'sub' => '(nepovinné; pod nadpisem v barvě webu)']) ?>
        <?= field_wrap('Pár slov o mně', '<textarea name="hero_about" rows="4" placeholder="2-3 věty: kdo jste, jak dlouho, čím se lišíte.">' . escape($get('hero_about')) . '</textarea>', '(zobrazí se vedle nadpisu; prázdné = skryto)') ?>
        <?= field('URL úvodní fotky', 'hero_image', ['type' => 'url', 'value' => $get('hero_image')]) ?>
    <?= card_close() ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= icon('check', 'ic ic-sm') ?> Uložit</button>
    </div>
</form>

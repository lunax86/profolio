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
        <?= field('Hlavní titulek', 'hero_title', ['value' => $get('hero_title'), 'placeholder' => $get('site_title'), 'sub' => '(prázdné = použije se název webu)']) ?>
        <?= field('Podnadpis', 'hero_slogan', ['value' => $get('hero_slogan'), 'placeholder' => $get('slogan'), 'sub' => '(prázdné = použije se obecný slogan)']) ?>
        <?= field('URL úvodní fotky', 'hero_image', ['type' => 'url', 'value' => $get('hero_image')]) ?>
    <?= card_close() ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= icon('check', 'ic ic-sm') ?> Uložit</button>
    </div>
</form>

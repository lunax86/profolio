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

<form method="post" action="/admin/paticka">
    <?= Csrf::field() ?>

    <?= card_open('Patička', 'Spodní část webu') ?>
        <?= field('Text v patičce', 'footer_tagline', ['value' => $get('footer_tagline'), 'placeholder' => $get('slogan'), 'sub' => '(prázdné = použije se obecný slogan)']) ?>
    <?= card_close() ?>

    <?= card_open('Sociální sítě', 'Odkazy na profily - v patičce, Instagram i ve stejnojmenném modulu') ?>
        <?= field('Facebook', 'social_facebook', ['type' => 'url', 'value' => $get('social_facebook')]) ?>
        <?= field('Instagram', 'social_instagram', ['type' => 'url', 'value' => $get('social_instagram')]) ?>
    <?= card_close() ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= icon('check', 'ic ic-sm') ?> Uložit</button>
    </div>
</form>

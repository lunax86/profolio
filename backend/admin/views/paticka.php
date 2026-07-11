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
<?php if (!empty($_GET['err'])): ?>
<div class="notice notice-err"><?= icon('alert', 'ic ic-sm') ?><?= escape($_GET['err']) ?></div>
<?php endif; ?>

<form method="post" action="/admin/paticka" enctype="multipart/form-data">
    <?= Csrf::field() ?>

    <?= card_open('Patička', 'Spodní část webu') ?>
        <?= field('Text v patičce', 'footer_tagline', ['value' => $get('footer_tagline'), 'placeholder' => $get('slogan'), 'sub' => '(prázdné = použije se obecný slogan)']) ?>
    <?= card_close() ?>

    <?= card_open('Portrét u kontaktu', 'Vaše fotka vedle jména - ať zákazník ví, komu volá. Prázdné = nic.') ?>
        <div class="favicon-row">
            <img class="favicon-prev" src="<?= escape($get('footer_portrait') ?: '/favicon.svg') ?>" alt="portrét" width="48" height="48">
            <div class="grow">
                <input type="file" name="footer_portrait_file" accept="image/png,image/jpeg,image/webp">
                <?php if ($get('footer_portrait') !== ''): ?>
                <label class="check"><input type="checkbox" name="footer_portrait_remove" value="1"> Odebrat portrét</label>
                <?php endif; ?>
            </div>
        </div>
        <?= field('…nebo URL portrétu', 'footer_portrait', ['type' => 'url', 'value' => $get('footer_portrait'), 'placeholder' => 'https://…']) ?>
    <?= card_close() ?>

    <?= card_open('Sociální sítě', 'Odkazy na profily - v patičce, Instagram i ve stejnojmenném modulu') ?>
        <?= field('Facebook', 'social_facebook', ['type' => 'url', 'value' => $get('social_facebook')]) ?>
        <?= field('Instagram', 'social_instagram', ['type' => 'url', 'value' => $get('social_instagram')]) ?>
    <?= card_close() ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= icon('check', 'ic ic-sm') ?> Uložit</button>
    </div>
</form>

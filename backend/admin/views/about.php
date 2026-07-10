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

<form method="post" action="/admin/about">
    <?= Csrf::field() ?>

    <?= card_open('O mně', 'Kdo za webem stojí - u živnostníka nejsilnější sekce pro důvěru') ?>
        <?= field('Nadpis', 'about_title', ['value' => $get('about_title'), 'placeholder' => $get('site_title'), 'sub' => '(prázdné = použije se název webu / vaše jméno)']) ?>
        <?= field_wrap('Text o sobě', '<textarea name="about_text" rows="7" placeholder="Pár vět: kdo jste, jak dlouho se řemeslu věnujete, čím se lišíte, něco osobního.">' . escape($get('about_text')) . '</textarea>', '(3-5 vět stačí)') ?>
        <?= field('URL fotky / portrétu', 'about_image', ['type' => 'url', 'value' => $get('about_image'), 'sub' => '(ideálně na výšku; portrét buduje důvěru)']) ?>
    <?= card_close() ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= icon('check', 'ic ic-sm') ?> Uložit</button>
    </div>
</form>

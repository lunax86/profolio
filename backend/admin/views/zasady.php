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

<form method="post" action="/admin/zasady">
    <?= Csrf::field() ?>

    <?= card_open('Zásady ochrany osobních údajů (GDPR)') ?>
        <p class="hint">Text se zobrazí návštěvníkům přes odkaz u formuláře a v patičce. Doplňte prosím údaje své firmy (název, IČO, sídlo).</p>
        <?= field_wrap('', '<textarea name="privacy_policy" rows="16">' . escape($get('privacy_policy')) . '</textarea>') ?>
    <?= card_close() ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= icon('check', 'ic ic-sm') ?> Uložit</button>
    </div>
</form>

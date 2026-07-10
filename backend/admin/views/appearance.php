<?php

declare(strict_types=1);

use App\Support\Csrf;
use App\Support\ThemeRegistry;

/** @var array<string, string> $settings */
/** @var array<string, array{label: string, hint: string, swatch: array{string, string}}> $shades */
/** @var array<string, array{label: string, hint: string, light: array<string, string>, dark: array<string, string>}> $accents */
/** @var bool $ok */

$currentShade = $settings['theme_shade'] ?? ThemeRegistry::DEFAULT_SHADE;
$currentAccent = $settings['theme_accent'] ?? ThemeRegistry::DEFAULT_ACCENT;

// Barvy náhledů z registru → CSS třídy (jediný zdroj hodnot je ThemeRegistry, žádné inline style).
// Náhled je přechod světlá → tmavá varianta.
$swatchCss = '';
foreach ($shades as $shadeKey => $shade) {
    // Stopy protáhneme za okraje (-100% / 200%), takže náhled ukazuje jen prostřední třetinu
    // přechodu a ořízne úplně světlou/tmavou krajní barvu.
    $swatchCss .= '.grad-s-' . $shadeKey . '{background:linear-gradient(to right,hsl(' . $shade['swatch'][0] . ') -100%,hsl(' . $shade['swatch'][1] . ') 200%)}';
}
foreach ($accents as $accentKey => $accent) {
    $swatchCss .= '.grad-a-' . $accentKey . '{background:linear-gradient(to right,hsl(' . $accent['light']['--primary'] . '),hsl(' . $accent['dark']['--primary'] . '))}';
}
?>
<style><?= $swatchCss ?></style>

<?php if ($ok): ?>
<div class="notice notice-ok"><?= icon('check', 'ic ic-sm') ?>Vzhled byl uložen.</div>
<?php endif; ?>

<form method="post" action="/admin/appearance">
    <?= Csrf::field() ?>

    <?= card_open('Barevné schéma', 'Platí pro celý veřejný web') ?>
        <div class="pick-group">
            <span class="lbl">Základní odstín (shade)</span>
            <div class="picks">
                <?php foreach ($shades as $shadeKey => $shade): ?>
                    <label class="pick">
                        <input type="radio" name="theme_shade" value="<?= escape($shadeKey) ?>"<?= $shadeKey === $currentShade ? ' checked' : '' ?>>
                        <span class="pick-body">
                            <span class="pick-preview grad-s-<?= escape($shadeKey) ?>"></span>
                            <span class="pick-meta">
                                <span class="pick-name"><?= escape($shade['label']) ?></span>
                                <span class="pick-hint"><?= escape($shade['hint']) ?></span>
                            </span>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="pick-group">
            <span class="lbl">Hlavní barva (accent) <span class="sub">– světlý / tmavý režim</span></span>
            <div class="picks">
                <?php foreach ($accents as $accentKey => $accent): ?>
                    <label class="pick">
                        <input type="radio" name="theme_accent" value="<?= escape($accentKey) ?>"<?= $accentKey === $currentAccent ? ' checked' : '' ?>>
                        <span class="pick-body">
                            <span class="pick-preview grad-a-<?= escape($accentKey) ?>"></span>
                            <span class="pick-meta">
                                <span class="pick-name"><?= escape($accent['label']) ?></span>
                                <span class="pick-hint"><?= escape($accent['hint']) ?></span>
                            </span>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    <?= card_close() ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= icon('check', 'ic ic-sm') ?> Uložit vzhled</button>
    </div>
</form>

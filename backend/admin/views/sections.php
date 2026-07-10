<?php

declare(strict_types=1);

use App\Support\Csrf;
use App\Support\SectionRegistry;

/** @var list<array{key: string, enabled: bool}> $sections */
/** @var bool $ok */
?>
<?php if ($ok): ?>
<div class="notice notice-ok"><?= icon('check', 'ic ic-sm') ?>Sekce byly uloženy.</div>
<?php endif; ?>

<form method="post" action="/admin/sections">
    <?= Csrf::field() ?>

    <?= card_open('Sekce a pořadí', 'Které sekce se zobrazí a v jakém pořadí') ?>
        <p class="hint">Úvod a patička jsou vždy zobrazené. Ostatní sekce lze vypnout nebo přeuspořádat šipkami.</p>
        <div class="sec-list">
            <div class="sec-row locked">
                <span class="sec-lock"><?= icon('lock') ?></span>
                <span class="sec-name"><?= escape(SectionRegistry::FIXED['hero']) ?></span>
                <span class="sec-fixed">vždy nahoře</span>
            </div>
            <div id="secSortable">
                <?php foreach ($sections as $section): ?>
                    <?php $key = $section['key']; ?>
                    <div class="sec-row">
                        <span class="sec-move">
                            <button type="button" class="sec-btn" data-move="up" aria-label="Posunout nahoru"><?= icon('chevron-up') ?></button>
                            <button type="button" class="sec-btn" data-move="down" aria-label="Posunout dolů"><?= icon('chevron-down') ?></button>
                        </span>
                        <span class="sec-name"><?= escape(SectionRegistry::MODULAR[$key]) ?></span>
                        <label class="switch">
                            <input type="checkbox" name="enabled[<?= escape($key) ?>]" value="1"<?= $section['enabled'] ? ' checked' : '' ?>>
                            Zobrazit
                        </label>
                        <input type="hidden" name="order[]" value="<?= escape($key) ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="sec-row locked">
                <span class="sec-lock"><?= icon('lock') ?></span>
                <span class="sec-name"><?= escape(SectionRegistry::FIXED['footer']) ?></span>
                <span class="sec-fixed">vždy dole</span>
            </div>
        </div>
    <?= card_close() ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= icon('check', 'ic ic-sm') ?> Uložit sekce</button>
    </div>
</form>

<script>
    // Přeuspořádání sekcí (nahoru/dolů). Hidden order[] inputy jsou uvnitř řádků,
    // takže se odešlou v aktuálním vizuálním pořadí. Bez JS zůstane výchozí pořadí.
    (function () {
        var list = document.getElementById('secSortable');
        if (!list) return;
        list.addEventListener('click', function (event) {
            var button = event.target.closest('[data-move]');
            if (!button) return;
            var row = button.closest('.sec-row');
            if (!row) return;
            if (button.dataset.move === 'up') {
                var previous = row.previousElementSibling;
                if (previous) list.insertBefore(row, previous);
            } else {
                var next = row.nextElementSibling;
                if (next) list.insertBefore(next, row);
            }
        });
    })();
</script>

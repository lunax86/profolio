<?php

declare(strict_types=1);

use App\Support\Clock;

/**
 * @var string $period
 * @var array{uniques:int, hits:int} $summary
 * @var array<int, array{label:string, count:int}> $sources
 * @var array<int, array{label:string, count:int}> $devices
 * @var array<int, array{label:string, count:int}> $browsers
 * @var array<int, array{label:string, count:int}> $systems
 * @var array<int, array{label:string, count:int}> $languages
 * @var array<int, array<string, mixed>> $recent
 */
$periodLabels = ['24h' => '24 hodin', '7d' => '7 dní', '30d' => '30 dní'];

/**
 * Donut jako SVG (vyhlazené hrany, 2px mezery mezi výsečemi) + legenda.
 * Přebytek nad 6 barevných výsečí se sloučí do neutrálního „Ostatní".
 */
$donutCard = static function (string $title, array $rows): string {
    $catColors = ['var(--cat-1)', 'var(--cat-2)', 'var(--cat-3)', 'var(--cat-4)', 'var(--cat-5)', 'var(--cat-6)'];
    $slices = $rows;
    if (count($rows) > count($catColors)) {
        $slices = array_slice($rows, 0, count($catColors));
        $rest = array_sum(array_map(static fn ($row) => (int) $row['count'], array_slice($rows, count($catColors))));
        if ($rest > 0) {
            $slices[] = ['label' => 'Ostatní', 'count' => $rest, 'muted' => true];
        }
    }
    $total = array_sum(array_map(static fn ($row) => (int) $row['count'], $slices));

    // Geometrie kroužku: viewBox 104, poloměr 40, tloušťka 16.
    $circumference = 2 * M_PI * 40;
    $svgOpen = '<svg class="donut" viewBox="0 0 104 104" width="100" height="100" aria-hidden="true">';

    if ($total === 0) {
        return card_open($title)
            . '<div class="donut-wrap">' . $svgOpen . '<circle cx="52" cy="52" r="40" fill="none" stroke-width="16" style="stroke:var(--surface-2)"/></svg>'
            . '<p class="rank-empty">Žádná data za období.</p></div>'
            . card_close();
    }

    $gap = count($slices) > 1 ? 2.0 : 0.0; // 2px mezera (odhalí plochu karty) mezi výsečemi
    $circles = '';
    $legend = '';
    $cumulativeFraction = 0.0;
    $colorIndex = 0;
    foreach ($slices as $slice) {
        $count = (int) $slice['count'];
        $fraction = $count / $total;
        $label = $slice['label'] === '' ? '(neznámé)' : (string) $slice['label'];
        // Neznámé / ostatní čtou neutrálně šedě, barvy zůstávají reálným kategoriím.
        $isNeutral = !empty($slice['muted']) || in_array($label, ['(neznámé)', 'Jiný', 'Neznámé'], true);
        $color = $isNeutral ? 'var(--ink-3)' : ($catColors[$colorIndex++] ?? 'var(--ink-3)');

        $visible = max($fraction * $circumference - $gap, 0.6);
        $angle = -90 + $cumulativeFraction * 360; // start nahoře, po směru hodin
        $circles .= '<circle cx="52" cy="52" r="40" fill="none" stroke-width="16" style="stroke:' . $color . '"'
            . ' stroke-dasharray="' . round($visible, 2) . ' ' . round($circumference - $visible, 2) . '"'
            . ' transform="rotate(' . round($angle, 2) . ' 52 52)"/>';
        $cumulativeFraction += $fraction;

        $percent = (int) round($fraction * 100);
        $legend .= '<div class="lg">'
            . '<span class="lg-dot" style="background:' . $color . '"></span>'
            . '<span class="lg-label" title="' . escape($label) . '">' . escape($label) . '</span>'
            . '<span class="lg-val">' . $count . ' · ' . $percent . ' %</span>'
            . '</div>';
    }

    return card_open($title)
        . '<div class="donut-wrap">'
        . $svgOpen . $circles . '</svg>'
        . '<div class="donut-legend">' . $legend . '</div>'
        . '</div>'
        . card_close();
};
?>
<div class="seg">
    <?php foreach ($periodLabels as $periodKey => $periodLabel): ?>
        <a href="/admin/traffic?obdobi=<?= $periodKey ?>" class="<?= $period === $periodKey ? 'on' : '' ?>"><?= $periodLabel ?></a>
    <?php endforeach; ?>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="top">Unikátní návštěvníci <span class="chip"><?= icon('user', 'ic ic-sm') ?></span></div>
        <div class="num"><?= (int) $summary['uniques'] ?></div>
        <div class="cell-sub">za <?= escape($periodLabels[$period] ?? $period) ?></div>
    </div>
    <div class="stat-card">
        <div class="top">Přístupy <span class="chip"><?= icon('chart', 'ic ic-sm') ?></span></div>
        <div class="num"><?= (int) $summary['hits'] ?></div>
        <div class="cell-sub">za <?= escape($periodLabels[$period] ?? $period) ?></div>
    </div>
</div>

<div class="traffic-grid">
    <?= $donutCard('Zdroje návštěv', $sources) ?>
    <?= $donutCard('Zařízení', $devices) ?>
    <?= $donutCard('Prohlížeče', $browsers) ?>
    <?= $donutCard('Operační systémy', $systems) ?>
    <?= $donutCard('Jazyky', $languages) ?>
</div>

<?= card_open('Poslední přístupy', 'nejnovějších ' . count($recent) . ' za období') ?>
    <?php if ($recent === []): ?>
        <p class="empty">Za toto období žádné přístupy.</p>
    <?php else: ?>
        <div class="tbl-wrap">
            <table>
                <thead><tr><th>Kdy</th><th>Zdroj</th><th>Zařízení</th><th>Prohlížeč</th><th>Systém</th><th>Jazyk</th></tr></thead>
                <tbody>
                <?php foreach ($recent as $visit): ?>
                    <tr>
                        <td class="cell-sub nowrap"><?= escape(Clock::formatUtc($visit['created_at'])) ?></td>
                        <td><?= escape($visit['referrer_host'] !== '' ? $visit['referrer_host'] : '(přímý)') ?></td>
                        <td><?= escape($visit['device']) ?></td>
                        <td><?= escape($visit['browser']) ?></td>
                        <td><?= escape($visit['os']) ?></td>
                        <td><?= escape($visit['language'] !== '' ? $visit['language'] : '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?= card_close() ?>

<?php

declare(strict_types=1);

use App\Core\Config;
use App\Support\Csrf;

/**
 * @var int $servicesCount
 * @var int $portfolioCount
 * @var int $unread
 * @var array{today:int, last7:int, total:int, perDay:array<int, array{day:string, count:int}>} $views
 * @var array{current:?string, latest:?string, slug:?string, upToDate:?bool, error:?string, checked:bool} $version
 */
$maxCount = max(1, ...array_map(static fn ($day): int => (int) $day['count'], $views['perDay']));
?>
<div class="stat-grid">
    <div class="stat-card">
        <div class="top">Nepřečtené poptávky <span class="chip"><?= icon('inbox', 'ic ic-sm') ?></span></div>
        <div class="num"><?= (int) $unread ?></div>
        <a class="link" href="/admin/inquiries">Zobrazit <?= icon('arrow', 'ic ic-sm') ?></a>
    </div>
    <div class="stat-card">
        <div class="top">Služby <span class="chip"><?= icon('briefcase', 'ic ic-sm') ?></span></div>
        <div class="num"><?= (int) $servicesCount ?></div>
        <a class="link" href="/admin/services">Spravovat <?= icon('arrow', 'ic ic-sm') ?></a>
    </div>
    <div class="stat-card">
        <div class="top">Ukázky práce <span class="chip"><?= icon('image', 'ic ic-sm') ?></span></div>
        <div class="num"><?= (int) $portfolioCount ?></div>
        <a class="link" href="/admin/portfolio">Spravovat <?= icon('arrow', 'ic ic-sm') ?></a>
    </div>
</div>

<?= card_open('Návštěvnost', 'unikátní návštěvníci za den · posledních ' . count($views['perDay']) . ' dní') ?>
    <div class="visits">
        <div class="figs">
            <div class="fig"><div class="n"><?= (int) $views['today'] ?></div><div class="l">dnes</div></div>
            <div class="fig"><div class="n"><?= (int) $views['last7'] ?></div><div class="l">za 7 dní</div></div>
            <div class="fig"><div class="n"><?= (int) $views['total'] ?></div><div class="l">celkem</div></div>
        </div>
        <div class="chart">
            <?php foreach ($views['perDay'] as $day): ?>
                <span class="bar" title="<?= escape($day['day']) ?>: <?= (int) $day['count'] ?>"><i style="height: <?= (int) round($day['count'] / $maxCount * 100) ?>%"></i></span>
            <?php endforeach; ?>
        </div>
    </div>
<?= card_close() ?>

<?= card_open('Obsah webu') ?>
    <p>Obsah upravíte v sekcích <a href="/admin/obecne">Obecné</a> (název, slogan, kontakt), <a href="/admin/hero">Úvod</a>, <a href="/admin/services">Služby</a> nebo <a href="/admin/portfolio">Portfolio</a>.</p>
    <?php if (Config::get('APP_ENV') !== 'production'): ?>
    <p>API dokumentace: <a href="/swagger" target="_blank" rel="noopener">/swagger</a></p>
    <?php endif; ?>
<?= card_close() ?>

<?= card_open('Verze') ?>
    <p>Nasazená verze: <code><?= $version['current'] ? escape(substr($version['current'], 0, 7)) : 'neznámá' ?></code>
        <?php if ($version['slug']): ?>
            · <a href="https://github.com/<?= escape($version['slug']) ?>" target="_blank" rel="noopener"><?= escape($version['slug']) ?> <?= icon('external', 'ic ic-sm') ?></a>
        <?php endif; ?>
    </p>
    <?php if ($version['checked']): ?>
        <?php if ($version['error']): ?>
            <div class="notice notice-err"><?= icon('alert', 'ic ic-sm') ?><?= escape($version['error']) ?></div>
        <?php elseif ($version['upToDate']): ?>
            <div class="notice notice-ok"><?= icon('check', 'ic ic-sm') ?> Máte nejnovější verzi.</div>
        <?php else: ?>
            <div class="notice notice-warn">
                <?= icon('alert', 'ic ic-sm') ?>
                K dispozici je novější verze (<code><?= escape(substr((string) $version['latest'], 0, 7)) ?></code>).
                <?php if ($version['slug'] && $version['current']): ?>
                    <a href="https://github.com/<?= escape($version['slug']) ?>/compare/<?= escape($version['current']) ?>...main" target="_blank" rel="noopener">Zobrazit změny</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
<?= card_foot('<form method="post" action="/admin/dashboard" class="inline">'
    . Csrf::field()
    . '<input type="hidden" name="_action" value="check_updates">'
    . '<button class="btn btn-ghost btn-sm" type="submit">' . icon('refresh', 'ic ic-sm') . ' Ověřit aktualizace</button>'
    . '</form>') ?>

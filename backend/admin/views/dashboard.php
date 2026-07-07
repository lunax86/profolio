<?php

declare(strict_types=1);

/**
 * @var int $servicesCount
 * @var int $portfolioCount
 * @var int $unread
 * @var array{today:int, last7:int, total:int, perDay:array<int, array{day:string, count:int}>} $views
 * @var array{current:?string, latest:?string, slug:?string, upToDate:?bool, error:?string, checked:bool} $version
 */
$e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$max = max(1, ...array_map(static fn ($d): int => (int) $d['count'], $views['perDay']));
?>
<h1>Přehled</h1>
<div class="grid">
    <div class="card">
        <div class="stat"><?= (int) $unread ?></div>
        <div>Nepřečtené poptávky</div>
        <p><a href="/admin/inquiries">Zobrazit poptávky →</a></p>
    </div>
    <div class="card">
        <div class="stat"><?= (int) $servicesCount ?></div>
        <div>Služby</div>
        <p><a href="/admin/services">Spravovat služby →</a></p>
    </div>
    <div class="card">
        <div class="stat"><?= (int) $portfolioCount ?></div>
        <div>Ukázky práce</div>
        <p><a href="/admin/portfolio">Spravovat portfolio →</a></p>
    </div>
</div>
<div class="card">
    <h2 class="views-title">Návštěvnost</h2>
    <div class="views-stats">
        <div><div class="stat"><?= (int) $views['today'] ?></div><div>dnes</div></div>
        <div><div class="stat"><?= (int) $views['last7'] ?></div><div>za 7 dní</div></div>
        <div><div class="stat"><?= (int) $views['total'] ?></div><div>celkem</div></div>
    </div>
    <div class="views-chart">
        <?php foreach ($views['perDay'] as $d): ?>
            <div class="views-bar" title="<?= htmlspecialchars($d['day'], ENT_QUOTES) ?>: <?= (int) $d['count'] ?>">
                <div class="views-bar-fill" style="height: <?= (int) round($d['count'] / $max * 100) ?>%"></div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="views-note">Unikátní návštěvníci za den · posledních <?= count($views['perDay']) ?> dní</div>
</div>

<style>
    .views-title { margin-top: 0; font-size: 1.1rem; }
    .views-stats { display: flex; gap: 2.5rem; margin: .5rem 0 1.25rem; }
    .views-stats > div > div:last-child { color: #64748b; font-size: .85rem; }
    .views-chart { display: flex; align-items: flex-end; gap: 3px; height: 72px; }
    .views-bar { flex: 1; display: flex; align-items: flex-end; height: 100%; background: #f1f5f9; border-radius: 3px; }
    .views-bar-fill { width: 100%; background: var(--brand); border-radius: 3px 3px 0 0; min-height: 2px; }
    .views-note { margin-top: .5rem; font-size: .8rem; color: #64748b; }
</style>

<div class="card">
    <p>Obsah webu upravíte v sekci <a href="/admin/settings">Nastavení</a> (titulek, slogan, kontaktní údaje, úvodní fotka).</p>
    <?php if (\App\Core\Config::get('APP_ENV') !== 'production'): ?>
    <p>API dokumentace: <a href="/swagger" target="_blank">/swagger</a></p>
    <?php endif; ?>
</div>

<div class="card">
    <h2 style="margin-top:0;font-size:1.1rem;">Verze</h2>
    <p style="color:#64748b;font-size:.9rem;margin:.25rem 0 .75rem;">
        Nasazená verze: <code><?= $version['current'] ? $e(substr($version['current'], 0, 7)) : 'neznámá' ?></code>
        <?php if ($version['slug']): ?>
            · <a href="https://github.com/<?= $e($version['slug']) ?>" target="_blank"><?= $e($version['slug']) ?></a>
        <?php endif; ?>
    </p>

    <?php if ($version['checked']): ?>
        <?php if ($version['error']): ?>
            <div class="alert"><?= $e($version['error']) ?></div>
        <?php elseif ($version['upToDate']): ?>
            <p style="color:#166534;font-weight:600;">✅ Máte nejnovější verzi.</p>
        <?php else: ?>
            <p style="color:#9a3412;font-weight:600;">
                ⬆ K dispozici je novější verze (<code><?= $e(substr((string) $version['latest'], 0, 7)) ?></code>).
                <?php if ($version['slug'] && $version['current']): ?>
                    <a href="https://github.com/<?= $e($version['slug']) ?>/compare/<?= $e($version['current']) ?>...main" target="_blank">Zobrazit změny →</a>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    <?php endif; ?>

    <form method="post" action="/admin/dashboard">
        <?= \App\Support\Csrf::field() ?>
        <input type="hidden" name="_action" value="check_updates">
        <button class="ghost" type="submit">Ověřit aktualizace</button>
    </form>
</div>

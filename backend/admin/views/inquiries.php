<?php

declare(strict_types=1);

use App\Support\Csrf;

/** @var array<int, array<string, mixed>> $inquiries */
$e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
/** created_at je v DB uložený v UTC – zobraz ho v pražském čase. */
$fmtDate = static function ($utc): string {
    try {
        return (new DateTimeImmutable((string) $utc, new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone('Europe/Prague'))
            ->format('j. n. Y H:i');
    } catch (\Exception) {
        return (string) $utc;
    }
};
?>
<h1>Poptávky</h1>
<div class="card">
    <?php if ($inquiries === []): ?>
        <p>Zatím žádné poptávky.</p>
    <?php endif; ?>
    <?php foreach ($inquiries as $q): ?>
        <div class="inquiry<?= $q['is_read'] ? ' is-read' : '' ?>">
            <div class="inquiry-head">
                <span class="inquiry-date"><?= $e($fmtDate($q['created_at'])) ?></span>
                <strong class="inquiry-name">
                    <?= $e($q['name']) ?>
                    <?php if (!$q['is_read']): ?><span class="badge">nové</span><?php endif; ?>
                </strong>
                <span class="inquiry-contact">
                    <a href="mailto:<?= $e($q['email']) ?>"><?= $e($q['email']) ?></a>
                    <?php if ($q['phone']): ?> · <?= $e($q['phone']) ?><?php endif; ?>
                </span>
                <?php if (!$q['is_read']): ?>
                <form method="post" action="/admin/inquiries" class="inquiry-action">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="_action" value="read">
                    <input type="hidden" name="id" value="<?= (int) $q['id'] ?>">
                    <button class="ghost" type="submit">Přečteno</button>
                </form>
                <?php endif; ?>
            </div>
            <div class="inquiry-message"><?= $e($q['message']) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<style>
    .inquiry { padding: .9rem 0; border-bottom: 1px solid #e2e8f0; }
    .inquiry:last-child { border-bottom: 0; }
    .inquiry.is-read { opacity: .55; }
    .inquiry-head { display: flex; flex-wrap: wrap; align-items: baseline; gap: .35rem 1rem; }
    .inquiry-date { white-space: nowrap; color: #64748b; font-size: .85rem; }
    .inquiry-name { white-space: nowrap; }
    .inquiry-contact { white-space: nowrap; color: #64748b; font-size: .9rem; }
    .inquiry-action { margin-left: auto; }
    .inquiry-message { margin-top: .5rem; white-space: pre-wrap; word-break: break-word; line-height: 1.5; }
</style>

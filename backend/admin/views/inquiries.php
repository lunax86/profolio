<?php

declare(strict_types=1);

use App\Support\Csrf;

/**
 * @var array<int, array<string, mixed>> $inquiries
 * @var bool $archivedView
 * @var int  $archivedCount
 */
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
/** created_at je v DB uložený v UTC – zobraz ho v pražském čase. */
$formatDate = static function ($utc): string {
    try {
        return (new DateTimeImmutable((string) $utc, new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone('Europe/Prague'))
            ->format('j. n. Y H:i');
    } catch (\Exception) {
        return (string) $utc;
    }
};
$formAction = '/admin/inquiries' . ($archivedView ? '?archiv=1' : '');
?>
<h1>Poptávky</h1>

<div class="tabs">
    <a href="/admin/inquiries" class="tab<?= $archivedView ? '' : ' active' ?>">Aktivní</a>
    <a href="/admin/inquiries?archiv=1" class="tab<?= $archivedView ? ' active' : '' ?>">Archiv (<?= (int) $archivedCount ?>)</a>
</div>

<div class="card">
    <?php if ($inquiries === []): ?>
        <p><?= $archivedView ? 'Archiv je prázdný.' : 'Zatím žádné poptávky.' ?></p>
    <?php endif; ?>
    <?php foreach ($inquiries as $inquiry): ?>
        <div class="inquiry<?= $inquiry['is_read'] ? ' is-read' : '' ?>">
            <div class="inquiry-head">
                <span class="inquiry-date"><?= $escape($formatDate($inquiry['created_at'])) ?></span>
                <strong class="inquiry-name">
                    <?= $escape($inquiry['name']) ?>
                    <?php if (!$inquiry['is_read'] && !$archivedView): ?><span class="badge">nové</span><?php endif; ?>
                </strong>
                <span class="inquiry-contact">
                    <a href="mailto:<?= $escape($inquiry['email']) ?>"><?= $escape($inquiry['email']) ?></a>
                    <?php if ($inquiry['phone']): ?> · <?= $escape($inquiry['phone']) ?><?php endif; ?>
                </span>
                <div class="inquiry-actions">
                    <?php if (!$archivedView && !$inquiry['is_read']): ?>
                    <form method="post" action="<?= $escape($formAction) ?>">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="_action" value="read">
                        <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                        <button class="ghost" type="submit">Přečteno</button>
                    </form>
                    <?php endif; ?>
                    <form method="post" action="<?= $escape($formAction) ?>">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="_action" value="<?= $archivedView ? 'unarchive' : 'archive' ?>">
                        <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                        <button class="ghost" type="submit"><?= $archivedView ? 'Obnovit' : 'Archivovat' ?></button>
                    </form>
                    <?php if ($archivedView): ?>
                    <form method="post" action="<?= $escape($formAction) ?>" onsubmit="return confirm('Opravdu NEVRATNĚ smazat tuto poptávku? Jméno, e-mail, telefon i zpráva budou trvale ztraceny. Tuto akci nelze vzít zpět.')">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="_action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                        <button class="danger" type="submit">Smazat</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="inquiry-message"><?= $escape($inquiry['message']) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<style>
    .tabs { display: flex; gap: .5rem; margin-bottom: 1rem; }
    .tab { padding: .4rem .9rem; border-radius: 999px; background: #e2e8f0; color: #0f172a; font-size: .9rem; font-weight: 600; }
    .tab.active { background: var(--brand); color: #fff; }
    .inquiry { padding: .9rem 0; border-bottom: 1px solid #e2e8f0; }
    .inquiry:last-child { border-bottom: 0; }
    .inquiry.is-read { opacity: .55; }
    .inquiry-head { display: flex; flex-wrap: wrap; align-items: baseline; gap: .35rem 1rem; }
    .inquiry-date { white-space: nowrap; color: #64748b; font-size: .85rem; }
    .inquiry-name { white-space: nowrap; }
    .inquiry-contact { white-space: nowrap; color: #64748b; font-size: .9rem; }
    .inquiry-actions { margin-left: auto; display: flex; gap: .5rem; }
    .inquiry-message { margin-top: .5rem; white-space: pre-wrap; word-break: break-word; line-height: 1.5; }
</style>

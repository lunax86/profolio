<?php

declare(strict_types=1);

use App\Support\Clock;
use App\Support\Csrf;

/**
 * @var array<int, array<string, mixed>> $inquiries
 * @var bool $archivedView
 * @var int  $archivedCount
 */
$formAction = '/admin/inquiries' . ($archivedView ? '?archiv=1' : '');

/** Skryté pole formuláře akce (CSRF + typ akce + id). */
$hiddenAction = static fn (string $action, int $id): string => Csrf::field()
    . '<input type="hidden" name="_action" value="' . escape($action) . '">'
    . '<input type="hidden" name="id" value="' . $id . '">';
?>
<div class="seg">
    <a href="/admin/inquiries" class="<?= $archivedView ? '' : 'on' ?>">Aktivní</a>
    <a href="/admin/inquiries?archiv=1" class="<?= $archivedView ? 'on' : '' ?>">Archiv (<?= (int) $archivedCount ?>)</a>
</div>

<div class="card">
    <?php if ($inquiries === []): ?>
        <div class="card-body empty"><?= $archivedView ? 'Archiv je prázdný.' : 'Zatím žádné poptávky.' ?></div>
    <?php endif; ?>
    <?php foreach ($inquiries as $inquiry): ?>
        <?php $inquiryId = (int) $inquiry['id']; ?>
        <div class="inq<?= $inquiry['is_read'] ? ' read' : '' ?>">
            <div class="inq-head">
                <span class="inq-name">
                    <?= escape($inquiry['name']) ?>
                    <?php if (!$inquiry['is_read'] && !$archivedView): ?><span class="pill pill-new">nové</span><?php endif; ?>
                </span>
                <span class="inq-contact">
                    <a href="mailto:<?= escape($inquiry['email']) ?>"><?= escape($inquiry['email']) ?></a><?php if ($inquiry['phone']): ?> · <?= escape($inquiry['phone']) ?><?php endif; ?>
                </span>
                <span class="inq-date"><?= escape(Clock::formatUtc($inquiry['created_at'])) ?></span>
                <div class="inq-actions">
                    <?php if (!$archivedView && !$inquiry['is_read']): ?>
                    <form method="post" action="<?= escape($formAction) ?>" class="inline">
                        <?= $hiddenAction('read', $inquiryId) ?>
                        <button class="btn btn-ghost btn-sm" type="submit"><?= icon('check', 'ic ic-sm') ?> Přečteno</button>
                    </form>
                    <?php endif; ?>
                    <form method="post" action="<?= escape($formAction) ?>" class="inline">
                        <?= $hiddenAction($archivedView ? 'unarchive' : 'archive', $inquiryId) ?>
                        <button class="btn btn-ghost btn-sm" type="submit">
                            <?= icon($archivedView ? 'restore' : 'archive', 'ic ic-sm') ?> <?= $archivedView ? 'Obnovit' : 'Archivovat' ?>
                        </button>
                    </form>
                    <?php if ($archivedView): ?>
                    <form method="post" action="<?= escape($formAction) ?>" class="inline" onsubmit="return confirm('Opravdu NEVRATNĚ smazat tuto poptávku? Jméno, e-mail, telefon i zpráva budou trvale ztraceny. Tuto akci nelze vzít zpět.')">
                        <?= $hiddenAction('delete', $inquiryId) ?>
                        <button class="btn btn-danger btn-sm" type="submit"><?= icon('trash', 'ic ic-sm') ?> Smazat</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="inq-msg"><?= escape($inquiry['message']) ?></div>
        </div>
    <?php endforeach; ?>
</div>

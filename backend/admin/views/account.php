<?php

declare(strict_types=1);

use App\Support\Clock;
use App\Support\Csrf;

/**
 * @var array<string, mixed> $currentUser
 * @var bool $isSuper
 * @var array<int, array<string, mixed>> $admins
 * @var string|null $ok
 * @var string|null $err
 */
?>
<?php if ($ok !== null): ?>
    <div class="notice notice-ok"><?= icon('check', 'ic ic-sm') ?><?= escape($ok) ?></div>
<?php endif; ?>
<?php if ($err !== null): ?>
    <div class="notice notice-err"><?= icon('alert', 'ic ic-sm') ?><?= escape($err) ?></div>
<?php endif; ?>

<?= card_open('Přihlášen jako') ?>
    <p><strong><?= escape($currentUser['email']) ?></strong> <span class="badge"><?= $isSuper ? 'super admin' : 'správce' ?></span></p>
<?= card_close() ?>

<form method="post" action="/admin/account">
    <?= Csrf::field() ?>
    <input type="hidden" name="_action" value="change_password">
    <?= card_open('Změna hesla') ?>
        <?= field('Současné heslo', 'current_password', ['type' => 'password', 'required' => true, 'autocomplete' => 'current-password']) ?>
        <?= field('Nové heslo', 'new_password', ['type' => 'password', 'required' => true, 'minlength' => 8, 'autocomplete' => 'new-password', 'sub' => '(alespoň 8 znaků)']) ?>
        <?= field('Nové heslo znovu', 'new_password_confirm', ['type' => 'password', 'required' => true, 'minlength' => 8, 'autocomplete' => 'new-password']) ?>
    <?= card_foot('<button type="submit" class="btn btn-primary">Změnit heslo</button>') ?>
</form>

<form method="post" action="/admin/account">
    <?= Csrf::field() ?>
    <input type="hidden" name="_action" value="change_email">
    <?= card_open('Změna e-mailu') ?>
        <?= field('Nový e-mail', 'new_email', ['type' => 'email', 'value' => (string) $currentUser['email'], 'required' => true]) ?>
        <?= field('Současné heslo', 'current_password', ['type' => 'password', 'required' => true, 'autocomplete' => 'current-password', 'sub' => '(pro potvrzení)', 'id' => 'f-current_password_email']) ?>
    <?= card_foot('<button type="submit" class="btn btn-primary">Změnit e-mail</button>') ?>
</form>

<?= card_open('Správci') ?>
    <div class="tbl-wrap">
        <table>
            <thead>
            <tr>
                <th>E-mail</th><th>Role</th><th>Vytvořeno</th>
                <?php if ($isSuper): ?><th></th><?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($admins as $admin): ?>
                <?php
                $adminIsSuper = (int) $admin['is_super'] === 1;
                $isSelf = (int) $admin['id'] === (int) $currentUser['id'];
                ?>
                <tr>
                    <td class="cell-strong"><?= escape($admin['email']) ?><?= $isSelf ? ' (vy)' : '' ?></td>
                    <td><?= $adminIsSuper ? 'super admin' : 'správce' ?></td>
                    <td class="cell-sub nowrap"><?= escape(Clock::formatUtc($admin['created_at'])) ?></td>
                    <?php if ($isSuper): ?>
                        <td>
                            <?php if (!$adminIsSuper && !$isSelf): ?>
                                <form method="post" action="/admin/account" class="inline" onsubmit="return confirm('Opravdu smazat tohoto správce?')">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="_action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $admin['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"><?= icon('trash', 'ic ic-sm') ?> Smazat</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?= card_close() ?>

<?php if ($isSuper): ?>
    <form method="post" action="/admin/account">
        <?= Csrf::field() ?>
        <input type="hidden" name="_action" value="add">
        <?= card_open('Přidat správce') ?>
            <?= field('E-mail', 'email', ['type' => 'email', 'required' => true]) ?>
            <?= field('Počáteční heslo', 'password', ['type' => 'text', 'required' => true, 'minlength' => 8, 'sub' => '(alespoň 8 znaků)']) ?>
            <p class="hint">Heslo předáte správci, ten si ho pak může sám změnit.</p>
        <?= card_foot('<button type="submit" class="btn btn-primary">' . icon('plus', 'ic ic-sm') . ' Přidat správce</button>') ?>
    </form>
<?php endif; ?>

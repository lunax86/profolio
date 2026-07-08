<?php

declare(strict_types=1);

use App\Support\Csrf;

/**
 * @var array<string, mixed> $currentUser
 * @var bool $isSuper
 * @var array<int, array<string, mixed>> $admins
 * @var string|null $ok
 * @var string|null $err
 */
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
// created_at je v DB v UTC - zobraz ho v pražském čase (stejně jako inquiries/security).
$formatDateTime = static function ($utc): string {
    try {
        return (new DateTimeImmutable((string) $utc, new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone('Europe/Prague'))
            ->format('j. n. Y H:i');
    } catch (\Exception) {
        return (string) $utc;
    }
};
?>
<h1>Účet</h1>

<?php if ($ok !== null): ?>
    <div class="alert" style="background:#dcfce7;color:#166534;"><?= $escape($ok) ?></div>
<?php endif; ?>
<?php if ($err !== null): ?>
    <div class="alert"><?= $escape($err) ?></div>
<?php endif; ?>

<div class="card">
    <h2 style="margin-top:0;font-size:1.1rem;">Přihlášen jako</h2>
    <p>
        <strong><?= $escape($currentUser['email']) ?></strong>
        <span class="badge"><?= $isSuper ? 'super admin' : 'správce' ?></span>
    </p>
</div>

<div class="card">
    <h2 style="margin-top:0;font-size:1.1rem;">Změna hesla</h2>
    <form method="post" action="/admin/account">
        <?= Csrf::field() ?>
        <input type="hidden" name="_action" value="change_password">
        <label>Současné heslo</label>
        <input type="password" name="current_password" required autocomplete="current-password">
        <label>Nové heslo (alespoň 8 znaků)</label>
        <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
        <label>Nové heslo znovu</label>
        <input type="password" name="new_password_confirm" required minlength="8" autocomplete="new-password">
        <div style="margin-top:.75rem;"><button type="submit">Změnit heslo</button></div>
    </form>
</div>

<div class="card">
    <h2 style="margin-top:0;font-size:1.1rem;">Změna e-mailu</h2>
    <form method="post" action="/admin/account">
        <?= Csrf::field() ?>
        <input type="hidden" name="_action" value="change_email">
        <label>Nový e-mail</label>
        <input type="email" name="new_email" value="<?= $escape($currentUser['email']) ?>" required>
        <label>Současné heslo (pro potvrzení)</label>
        <input type="password" name="current_password" required autocomplete="current-password">
        <div style="margin-top:.75rem;"><button type="submit">Změnit e-mail</button></div>
    </form>
</div>

<div class="card">
    <h2 style="margin-top:0;font-size:1.1rem;">Správci</h2>
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
                <td><?= $escape($admin['email']) ?><?= $isSelf ? ' (vy)' : '' ?></td>
                <td><?= $adminIsSuper ? 'super admin' : 'správce' ?></td>
                <td><?= $escape($formatDateTime($admin['created_at'])) ?></td>
                <?php if ($isSuper): ?>
                    <td>
                        <?php if (!$adminIsSuper && !$isSelf): ?>
                            <form method="post" action="/admin/account" onsubmit="return confirm('Opravdu smazat tohoto správce?')">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="_action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $admin['id'] ?>">
                                <button type="submit" class="danger">Smazat</button>
                            </form>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($isSuper): ?>
    <div class="card">
        <h2 style="margin-top:0;font-size:1.1rem;">Přidat správce</h2>
        <form method="post" action="/admin/account">
            <?= Csrf::field() ?>
            <input type="hidden" name="_action" value="add">
            <label>E-mail</label>
            <input type="email" name="email" required>
            <label>Počáteční heslo (alespoň 8 znaků)</label>
            <input type="text" name="password" required minlength="8">
            <p style="color:#64748b;font-size:.8rem;margin:.35rem 0 0;">
                Heslo předáte správci, ten si ho pak může sám změnit.
            </p>
            <div style="margin-top:.75rem;"><button type="submit">Přidat správce</button></div>
        </form>
    </div>
<?php endif; ?>

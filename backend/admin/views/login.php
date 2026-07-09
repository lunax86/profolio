<?php

declare(strict_types=1);

use App\Support\Csrf;

/** @var string|null $error */
?>
<div class="login-card">
    <div class="login-brand"><span class="mark"><?= icon('cube') ?></span> Administrace</div>
    <?php if (!empty($error)): ?>
        <div class="notice notice-err"><?= icon('alert', 'ic ic-sm') ?><?= escape($error) ?></div>
    <?php endif; ?>
    <form method="post" action="/admin/login">
        <?= Csrf::field() ?>
        <?= card_open('Přihlášení do administrace') ?>
            <?= field('E-mail', 'email', ['type' => 'email', 'required' => true, 'autocomplete' => 'username']) ?>
            <?= field_wrap('Heslo', '<div class="pw-field">'
                . '<input id="f-password" type="password" name="password" required autocomplete="current-password">'
                . '<button type="button" class="pw-toggle" aria-label="Zobrazit heslo">' . icon('eye') . '</button>'
                . '</div>') ?>
        <?= card_foot('<button type="submit" class="btn btn-primary">' . icon('logout', 'ic ic-sm') . ' Přihlásit se</button>') ?>
    </form>
</div>

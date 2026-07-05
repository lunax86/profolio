<?php

declare(strict_types=1);

use App\Support\Csrf;

/** @var string|null $error */
$e = static fn (?string $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<div class="card" style="max-width:380px;margin:4rem auto;">
    <h1>Přihlášení do administrace</h1>
    <?php if (!empty($error)): ?>
        <div class="alert"><?= $e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="/admin/login">
        <?= Csrf::field() ?>
        <label for="email">E-mail</label>
        <input id="email" type="email" name="email" required autofocus>
        <label for="password">Heslo</label>
        <input id="password" type="password" name="password" required>
        <div style="margin-top:1rem;"><button type="submit">Přihlásit se</button></div>
    </form>
</div>

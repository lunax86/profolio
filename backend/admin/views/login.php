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
        <div class="pw-field">
            <input id="password" type="password" name="password" required>
            <button type="button" class="pw-toggle" aria-label="Zobrazit heslo"
                onclick="var i=document.getElementById('password'),s=i.type==='password';i.type=s?'text':'password';this.textContent=s?'🙈':'👁';this.setAttribute('aria-label',s?'Skrýt heslo':'Zobrazit heslo');">👁</button>
        </div>
        <div style="margin-top:1rem;"><button type="submit">Přihlásit se</button></div>
    </form>
</div>

<style>
    .pw-field { position: relative; }
    .pw-field input { padding-right: 2.6rem; }
    .pw-toggle {
        position: absolute; top: 0; right: 0; height: 100%;
        background: none; border: 0; border-radius: 0; padding: 0 .6rem;
        font-size: 1.1rem; line-height: 1; cursor: pointer; color: inherit;
    }
</style>

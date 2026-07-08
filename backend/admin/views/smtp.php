<?php

declare(strict_types=1);

use App\Support\Csrf;

/**
 * @var array<string, string> $config
 * @var string|null $ok
 * @var string|null $err
 */
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$value = static fn (string $key): string => htmlspecialchars((string) ($config[$key] ?? ''), ENT_QUOTES, 'UTF-8');
$encryption = $config['smtp_encryption'] ?? 'tls';
$hasPassword = ($config['smtp_password'] ?? '') !== '';
?>
<h1>SMTP (odesílání e-mailů)</h1>

<?php if ($ok !== null): ?>
    <div class="alert" style="background:#dcfce7;color:#166534;"><?= $escape($ok) ?></div>
<?php endif; ?>
<?php if ($err !== null): ?>
    <div class="alert"><?= $escape($err) ?></div>
<?php endif; ?>

<div class="card">
    <p style="color:#64748b;font-size:.85rem;margin:0 0 .5rem;">
        Údaje k vašemu SMTP serveru. Web žádnou poštu nehostuje, jen přes tento server
        e-maily odesílá. Heslo se ukládá jen při vyplnění.
    </p>
    <form method="post" action="/admin/smtp">
        <?= Csrf::field() ?>
        <input type="hidden" name="_action" value="save">
        <label>SMTP server (host)</label>
        <input type="text" name="smtp_host" value="<?= $value('smtp_host') ?>" placeholder="smtp.example.com">
        <label>Port</label>
        <input type="number" name="smtp_port" value="<?= $value('smtp_port') ?: '587' ?>" placeholder="587">
        <label>Šifrování</label>
        <select name="smtp_encryption">
            <option value="tls"<?= $encryption === 'tls' ? ' selected' : '' ?>>TLS (STARTTLS, obvykle port 587)</option>
            <option value="ssl"<?= $encryption === 'ssl' ? ' selected' : '' ?>>SSL (obvykle port 465)</option>
            <option value=""<?= ($encryption !== 'tls' && $encryption !== 'ssl') ? ' selected' : '' ?>>Žádné (nedoporučeno)</option>
        </select>
        <label>Uživatel</label>
        <input type="text" name="smtp_username" value="<?= $value('smtp_username') ?>" placeholder="login k SMTP" autocomplete="off">
        <label>Heslo <span style="font-weight:400;color:#94a3b8;"><?= $hasPassword ? '(uloženo, vyplňte jen při změně)' : '(zatím nenastaveno)' ?></span></label>
        <input type="password" name="smtp_password" value="" placeholder="<?= $hasPassword ? '••••••••' : '' ?>" autocomplete="new-password">
        <label>Odesílatel - e-mail</label>
        <input type="email" name="smtp_from_email" value="<?= $value('smtp_from_email') ?>" placeholder="noreply@example.com">
        <label>Odesílatel - jméno</label>
        <input type="text" name="smtp_from_name" value="<?= $value('smtp_from_name') ?>" placeholder="Vaše firma">
        <div style="margin-top:.75rem;"><button type="submit">Uložit nastavení</button></div>
    </form>
</div>

<div class="card">
    <h2 style="margin-top:0;font-size:1.1rem;">Poslat testovací e-mail</h2>
    <p style="color:#64748b;font-size:.85rem;margin:.25rem 0 .5rem;">
        Odešle test přes <strong>uložené</strong> nastavení. Nejdřív tedy nastavení uložte.
    </p>
    <form method="post" action="/admin/smtp">
        <?= Csrf::field() ?>
        <input type="hidden" name="_action" value="test">
        <label>Poslat na adresu</label>
        <input type="email" name="test_email" required placeholder="vas@email.cz">
        <div style="margin-top:.75rem;"><button type="submit">Poslat test</button></div>
    </form>
</div>

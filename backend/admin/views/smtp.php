<?php

declare(strict_types=1);

use App\Support\Csrf;

/**
 * @var array<string, string> $config
 * @var string|null $ok
 * @var string|null $err
 */
$get = static fn (string $key): string => (string) ($config[$key] ?? '');
$encryption = $config['smtp_encryption'] ?? 'tls';
$hasPassword = ($config['smtp_password'] ?? '') !== '';

$encryptionOptions = '';
foreach (['tls' => 'TLS (STARTTLS, obvykle port 587)', 'ssl' => 'SSL (obvykle port 465)', '' => 'Žádné (nedoporučeno)'] as $optionValue => $optionLabel) {
    $encryptionOptions .= '<option value="' . escape($optionValue) . '"' . ($encryption === $optionValue ? ' selected' : '') . '>' . escape($optionLabel) . '</option>';
}
?>
<?php if ($ok !== null): ?>
    <div class="notice notice-ok"><?= icon('check', 'ic ic-sm') ?><?= escape($ok) ?></div>
<?php endif; ?>
<?php if ($err !== null): ?>
    <div class="notice notice-err"><?= icon('alert', 'ic ic-sm') ?><?= escape($err) ?></div>
<?php endif; ?>

<form method="post" action="/admin/smtp">
    <?= Csrf::field() ?>
    <input type="hidden" name="_action" value="save">
    <?= card_open('Připojení k SMTP serveru') ?>
        <p class="hint">Údaje k vašemu SMTP serveru. Web žádnou poštu nehostuje, jen přes tento server e-maily odesílá. Heslo se ukládá jen při vyplnění.</p>
        <?= field('SMTP server (host)', 'smtp_host', ['value' => $get('smtp_host'), 'placeholder' => 'smtp.example.com']) ?>
        <div class="field-row">
            <?= field('Port', 'smtp_port', ['type' => 'number', 'value' => $get('smtp_port') ?: '587', 'placeholder' => '587']) ?>
            <?= field_wrap('Šifrování', '<select name="smtp_encryption">' . $encryptionOptions . '</select>') ?>
        </div>
        <?= field('Uživatel', 'smtp_username', ['value' => $get('smtp_username'), 'placeholder' => 'login k SMTP', 'autocomplete' => 'off']) ?>
        <?= field('Heslo', 'smtp_password', ['type' => 'password', 'autocomplete' => 'new-password', 'sub' => $hasPassword ? '(uloženo, vyplňte jen při změně)' : '(zatím nenastaveno)', 'placeholder' => $hasPassword ? '••••••••' : '']) ?>
        <div class="field-row">
            <?= field('Odesílatel - e-mail', 'smtp_from_email', ['type' => 'email', 'value' => $get('smtp_from_email'), 'placeholder' => 'noreply@example.com']) ?>
            <?= field('Odesílatel - jméno', 'smtp_from_name', ['value' => $get('smtp_from_name'), 'placeholder' => 'Vaše firma']) ?>
        </div>
    <?= card_foot('<button type="submit" class="btn btn-primary">' . icon('check', 'ic ic-sm') . ' Uložit nastavení</button>') ?>
</form>

<form method="post" action="/admin/smtp">
    <?= Csrf::field() ?>
    <input type="hidden" name="_action" value="test">
    <?= card_open('Poslat testovací e-mail') ?>
        <p class="hint">Odešle test přes <strong>uložené</strong> nastavení. Nejdřív tedy nastavení uložte.</p>
        <?= field('Poslat na adresu', 'test_email', ['type' => 'email', 'required' => true, 'placeholder' => 'vas@email.cz']) ?>
    <?= card_foot('<button type="submit" class="btn btn-primary">' . icon('mail', 'ic ic-sm') . ' Poslat test</button>') ?>
</form>

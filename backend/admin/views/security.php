<?php

declare(strict_types=1);

use App\Support\Clock;

/**
 * @var array<int, array<string, mixed>> $attempts
 * @var int $total
 * @var int $failed
 * @var array<int, array{ip: string, count: int}> $blockedIps
 * @var string $period
 * @var array{https: bool, httpOnly: bool, secure: bool, sameSite: string} $status
 */
$checklist = [
    'HTTPS (šifrované spojení)' => $status['https'],
    'Session cookie: HttpOnly' => $status['httpOnly'],
    'Session cookie: Secure' => $status['secure'],
    'Session cookie: SameSite = ' . ($status['sameSite'] !== '' ? $status['sameSite'] : '-') => $status['sameSite'] !== '',
    'Rate-limit přihlášení (5 pokusů / 15 min)' => true,
    'CSRF ochrana formulářů' => true,
];
$periodLabels = ['24h' => '24 hodin', '7d' => '7 dní', '30d' => '30 dní'];
?>
<?= card_open('Stav zabezpečení') ?>
    <ul class="check-list">
        <?php foreach ($checklist as $label => $ok): ?>
            <li>
                <span class="<?= $ok ? 'ok' : 'bad' ?>"><?= icon($ok ? 'check' : 'x', 'ic ic-sm') ?></span>
                <?= escape($label) ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <p class="note">Bezpečnostní hlavičky (HSTS, X-Frame-Options…) se nastavují v Apache, viz RUNBOOK.</p>
<?= card_close() ?>

<?= card_open('Aktuálně blokované IP') ?>
    <p class="hint">Adresy s ≥ 5 neúspěšnými pokusy za posledních 15 minut, rate-limiter je právě blokuje.</p>
    <?php if ($blockedIps === []): ?>
        <p class="empty">Žádné.</p>
    <?php else: ?>
        <div class="tbl-wrap">
            <table>
                <thead><tr><th>IP</th><th>Neúspěšných pokusů</th></tr></thead>
                <tbody>
                <?php foreach ($blockedIps as $blocked): ?>
                    <tr><td class="cell-strong"><?= escape($blocked['ip']) ?></td><td><?= (int) $blocked['count'] ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?= card_close() ?>

<div class="seg">
    <?php foreach ($periodLabels as $periodKey => $periodLabel): ?>
        <a href="/admin/security?obdobi=<?= $periodKey ?>" class="<?= $period === $periodKey ? 'on' : '' ?>"><?= $periodLabel ?></a>
    <?php endforeach; ?>
</div>

<?= card_open('Historie přihlášení', 'za období: ' . ($periodLabels[$period] ?? $period)) ?>
    <p class="hint"><strong><?= (int) $total ?></strong> přihlášení, z toho <strong><?= (int) $failed ?></strong> neúspěšných.</p>
    <?php if ($attempts === []): ?>
        <p class="empty">Za toto období žádné záznamy.</p>
    <?php else: ?>
        <div class="tbl-wrap">
            <table>
                <thead><tr><th>Kdy</th><th>IP</th><th>E-mail</th><th>Výsledek</th></tr></thead>
                <tbody>
                <?php foreach ($attempts as $attempt): ?>
                    <tr>
                        <td class="nowrap"><?= escape(Clock::formatUtc($attempt['created_at'])) ?></td>
                        <td><?= escape($attempt['ip']) ?></td>
                        <td><?= escape($attempt['email']) ?></td>
                        <td>
                            <?php if ($attempt['success']): ?>
                                <span class="pill pill-good"><?= icon('check', 'ic ic-sm') ?> úspěch</span>
                            <?php else: ?>
                                <span class="pill pill-bad"><?= icon('x', 'ic ic-sm') ?> neúspěch</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (count($attempts) >= 200): ?>
            <p class="note">Zobrazeno posledních 200 záznamů z tohoto období.</p>
        <?php endif; ?>
    <?php endif; ?>
<?= card_close() ?>

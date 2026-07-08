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
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
<h1>Bezpečnost</h1>

<div class="card">
    <h2 style="margin-top:0;font-size:1.1rem;">Stav zabezpečení</h2>
    <ul class="sec-check">
        <?php foreach ($checklist as $label => $ok): ?>
            <li><span class="<?= $ok ? 'ok' : 'bad' ?>"><?= $ok ? '✓' : '✗' ?></span> <?= $escape($label) ?></li>
        <?php endforeach; ?>
    </ul>
    <p style="color:#64748b;font-size:.8rem;margin-top:.5rem;">
        Bezpečnostní hlavičky (HSTS, X-Frame-Options…) se nastavují v Apache - viz RUNBOOK.
    </p>
</div>

<div class="card">
    <h2 style="margin-top:0;font-size:1.1rem;">Aktuálně blokované IP</h2>
    <p style="color:#64748b;font-size:.85rem;margin:.25rem 0 .5rem;">
        Adresy s ≥ 5 neúspěšnými pokusy za posledních 15 minut - rate-limiter je právě blokuje.
    </p>
    <?php if ($blockedIps === []): ?>
        <p>Žádné. 👍</p>
    <?php else: ?>
        <table>
            <thead><tr><th>IP</th><th>Neúspěšných pokusů</th></tr></thead>
            <tbody>
            <?php foreach ($blockedIps as $blocked): ?>
                <tr><td><?= $escape($blocked['ip']) ?></td><td><?= (int) $blocked['count'] ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2 style="margin-top:0;font-size:1.1rem;">Historie přihlášení</h2>
    <div class="tabs">
        <?php foreach ($periodLabels as $periodKey => $periodLabel): ?>
            <a href="/admin/security?obdobi=<?= $periodKey ?>" class="tab<?= $period === $periodKey ? ' active' : '' ?>"><?= $periodLabel ?></a>
        <?php endforeach; ?>
    </div>
    <p style="color:#64748b;font-size:.85rem;margin:.25rem 0 .5rem;">
        Za období <strong><?= $escape($periodLabels[$period] ?? $period) ?></strong>:
        <strong><?= (int) $total ?></strong> přihlášení, z toho <strong><?= (int) $failed ?></strong> neúspěšných.
    </p>
    <?php if ($attempts === []): ?>
        <p>Za toto období žádné záznamy.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Kdy</th><th>IP</th><th>E-mail</th><th>Výsledek</th></tr></thead>
            <tbody>
            <?php foreach ($attempts as $attempt): ?>
                <tr>
                    <td style="white-space:nowrap"><?= $escape(Clock::formatUtc($attempt['created_at'])) ?></td>
                    <td><?= $escape($attempt['ip']) ?></td>
                    <td><?= $escape($attempt['email']) ?></td>
                    <td><?= $attempt['success']
                        ? '<span style="color:#166534">✅ úspěch</span>'
                        : '<span style="color:#9a3412">❌ neúspěch</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (count($attempts) >= 200): ?>
            <p style="color:#64748b;font-size:.8rem;margin-top:.5rem;">Zobrazeno posledních 200 záznamů z tohoto období.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
    .sec-check { list-style: none; padding: 0; margin: .5rem 0 0; }
    .sec-check li { padding: .25rem 0; }
    .sec-check .ok { color: #166534; font-weight: 700; }
    .sec-check .bad { color: #b91c1c; font-weight: 700; }
    .tabs { display: flex; gap: .5rem; margin-bottom: .75rem; }
    .tab { padding: .4rem .9rem; border-radius: 999px; background: #e2e8f0; color: #0f172a; font-size: .9rem; font-weight: 600; }
    .tab.active { background: var(--brand); color: #fff; }
</style>

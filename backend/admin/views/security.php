<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $attempts
 * @var int $failed24h
 * @var array<int, array{ip: string, count: int}> $blockedIps
 * @var array{https: bool, httpOnly: bool, secure: bool, sameSite: string} $status
 */
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$formatDate = static function ($utc): string {
    try {
        return (new DateTimeImmutable((string) $utc, new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone('Europe/Prague'))
            ->format('j. n. Y H:i:s');
    } catch (\Exception) {
        return (string) $utc;
    }
};
$checklist = [
    'HTTPS (šifrované spojení)' => $status['https'],
    'Session cookie: HttpOnly' => $status['httpOnly'],
    'Session cookie: Secure' => $status['secure'],
    'Session cookie: SameSite = ' . ($status['sameSite'] !== '' ? $status['sameSite'] : '—') => $status['sameSite'] !== '',
    'Rate-limit přihlášení (5 pokusů / 15 min)' => true,
    'CSRF ochrana formulářů' => true,
];
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
        Bezpečnostní hlavičky (HSTS, X-Frame-Options…) se nastavují v Apache – viz RUNBOOK.
    </p>
</div>

<div class="card">
    <h2 style="margin-top:0;font-size:1.1rem;">Aktuálně blokované IP</h2>
    <p style="color:#64748b;font-size:.85rem;margin:.25rem 0 .5rem;">
        Adresy s ≥ 5 neúspěšnými pokusy za posledních 15 minut – rate-limiter je právě blokuje.
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
    <h2 style="margin-top:0;font-size:1.1rem;">Poslední přihlášení</h2>
    <p style="color:#64748b;font-size:.85rem;margin:.25rem 0 .5rem;">
        Neúspěšných pokusů za 24 h: <strong><?= (int) $failed24h ?></strong>
    </p>
    <?php if ($attempts === []): ?>
        <p>Zatím žádné záznamy.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Kdy</th><th>IP</th><th>E-mail</th><th>Výsledek</th></tr></thead>
            <tbody>
            <?php foreach ($attempts as $attempt): ?>
                <tr>
                    <td style="white-space:nowrap"><?= $escape($formatDate($attempt['created_at'])) ?></td>
                    <td><?= $escape($attempt['ip']) ?></td>
                    <td><?= $escape($attempt['email']) ?></td>
                    <td><?= $attempt['success']
                        ? '<span style="color:#166534">✅ úspěch</span>'
                        : '<span style="color:#9a3412">❌ neúspěch</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
    .sec-check { list-style: none; padding: 0; margin: .5rem 0 0; }
    .sec-check li { padding: .25rem 0; }
    .sec-check .ok { color: #166534; font-weight: 700; }
    .sec-check .bad { color: #b91c1c; font-weight: 700; }
</style>

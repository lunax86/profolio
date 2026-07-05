<?php

declare(strict_types=1);

use App\Support\Csrf;

/** @var array<int, array<string, mixed>> $inquiries */
$e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<h1>Poptávky</h1>
<div class="card">
    <table>
        <thead><tr><th>Kdy</th><th>Jméno</th><th>Kontakt</th><th>Zpráva</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($inquiries as $q): ?>
            <tr style="<?= $q['is_read'] ? 'opacity:.6' : 'font-weight:600' ?>">
                <td><?= $e($q['created_at']) ?></td>
                <td><?= $e($q['name']) ?><?php if (!$q['is_read']): ?> <span class="badge">nové</span><?php endif; ?></td>
                <td><?= $e($q['email']) ?><br><small><?= $e($q['phone']) ?></small></td>
                <td style="max-width:280px"><?= nl2br($e($q['message'])) ?></td>
                <td>
                    <div class="row">
                        <?php if (!$q['is_read']): ?>
                        <form method="post" action="/admin/inquiries">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="_action" value="read">
                            <input type="hidden" name="id" value="<?= (int) $q['id'] ?>">
                            <button class="ghost" type="submit">Přečteno</button>
                        </form>
                        <?php endif; ?>
                        <form method="post" action="/admin/inquiries" onsubmit="return confirm('Smazat poptávku?')">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="_action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $q['id'] ?>">
                            <button class="danger" type="submit">Smazat</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($inquiries === []): ?><tr><td colspan="5">Zatím žádné poptávky.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

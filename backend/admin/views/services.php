<?php

declare(strict_types=1);

use App\Support\Csrf;

/** @var array<int, array<string, mixed>> $services */
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<h1>Služby</h1>

<div class="card">
    <h2 style="margin-top:0;font-size:1.1rem;">Přidat službu</h2>
    <form method="post" action="/admin/services">
        <?= Csrf::field() ?>
        <label>Název</label>
        <input type="text" name="title" required>
        <label>Popis</label>
        <textarea name="description"></textarea>
        <div class="row">
            <div style="flex:1"><label>Ikona (lucide)</label><input type="text" name="icon" value="sparkles" placeholder="např. hammer, wrench"></div>
            <div style="width:120px"><label>Pořadí</label><input type="number" name="sort_order" value="0"></div>
        </div>
        <div style="margin-top:1rem"><button type="submit">Přidat</button></div>
    </form>
</div>

<div class="card">
    <table>
        <thead><tr><th>Ikona</th><th>Název</th><th>Popis</th><th>Pořadí</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($services as $service): ?>
            <tr>
                <td><span class="badge"><?= $escape($service['icon']) ?></span></td>
                <td><?= $escape($service['title']) ?></td>
                <td><?= $escape($service['description']) ?></td>
                <td><?= (int) $service['sort_order'] ?></td>
                <td>
                    <form method="post" action="/admin/services" onsubmit="return confirm('Smazat službu?')">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="_action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $service['id'] ?>">
                        <button class="danger" type="submit">Smazat</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($services === []): ?><tr><td colspan="5">Zatím žádné služby.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php

declare(strict_types=1);

use App\Support\Csrf;

/** @var array<int, array<string, mixed>> $services */
?>
<form method="post" action="/admin/services">
    <?= Csrf::field() ?>
    <?= card_open('Přidat službu') ?>
        <?= field('Název', 'title', ['required' => true]) ?>
        <?= field_wrap('Popis', '<textarea name="description"></textarea>') ?>
        <div class="field-row">
            <?= field('Ikona (lucide)', 'icon', ['value' => 'sparkles', 'placeholder' => 'např. hammer, wrench']) ?>
            <?= field('Pořadí', 'sort_order', ['type' => 'number', 'value' => '0']) ?>
        </div>
    <?= card_foot('<button type="submit" class="btn btn-primary">' . icon('plus', 'ic ic-sm') . ' Přidat</button>') ?>
</form>

<div class="card">
    <div class="tbl-wrap">
        <table>
            <thead><tr><th>Ikona</th><th>Název</th><th>Popis</th><th>Pořadí</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($services as $service): ?>
                <tr>
                    <td><span class="badge"><?= escape($service['icon']) ?></span></td>
                    <td class="cell-strong"><?= escape($service['title']) ?></td>
                    <td class="cell-sub"><?= escape($service['description']) ?></td>
                    <td><?= (int) $service['sort_order'] ?></td>
                    <td>
                        <form method="post" action="/admin/services" class="inline" onsubmit="return confirm('Smazat službu?')">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="_action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $service['id'] ?>">
                            <button class="btn btn-danger btn-sm" type="submit"><?= icon('trash', 'ic ic-sm') ?> Smazat</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($services === []): ?><tr><td colspan="5" class="empty">Zatím žádné služby.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

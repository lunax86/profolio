<?php

declare(strict_types=1);

use App\Support\Csrf;

/** @var array<int, array<string, mixed>> $items */
?>
<form method="post" action="/admin/portfolio" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <?= card_open('Přidat ukázku') ?>
        <?= field('Název', 'title', ['required' => true]) ?>
        <?= field_wrap('Popis', '<textarea name="description"></textarea>') ?>
        <?= field_wrap('Obrázek (nahrát soubor)', '<input type="file" name="image" accept="image/*">') ?>
        <?= field('…nebo URL obrázku', 'image_url', ['type' => 'url', 'placeholder' => 'https://…']) ?>
    <?= card_foot('<button type="submit" class="btn btn-primary">' . icon('plus', 'ic ic-sm') . ' Přidat</button>') ?>
</form>

<?php if ($items === []): ?>
    <div class="card"><div class="card-body empty">Zatím žádné ukázky.</div></div>
<?php else: ?>
    <div class="grid">
        <?php foreach ($items as $item): ?>
            <div class="card">
                <div class="card-body">
                    <img class="thumb" src="<?= escape($item['image_path']) ?>" alt="<?= escape($item['title']) ?>">
                    <div class="cell-strong"><?= escape($item['title']) ?></div>
                    <p class="cell-sub"><?= escape($item['description']) ?></p>
                    <form method="post" action="/admin/portfolio" class="inline" onsubmit="return confirm('Smazat ukázku?')">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="_action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <button class="btn btn-danger btn-sm" type="submit"><?= icon('trash', 'ic ic-sm') ?> Smazat</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php

declare(strict_types=1);

use App\Support\Csrf;

/** @var array<int, array<string, mixed>> $items */
?>
<form method="post" action="/admin/portfolio" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <?= card_open('Přidat ukázku', 'Fotka „před" je nepovinná; když ji přidáte, na webu se ukáže posuvník před/po') ?>
        <?= field('Název', 'title', ['required' => true]) ?>
        <?= field_wrap('Popis', '<textarea name="description"></textarea>') ?>
        <?= field_wrap('Fotka „po" / hlavní (nahrát)', '<input type="file" name="image" accept="image/*">') ?>
        <?= field('…nebo URL fotky „po"', 'image_url', ['type' => 'url', 'placeholder' => 'https://…']) ?>
        <?= field_wrap('Fotka „před" (nahrát)', '<input type="file" name="image_before" accept="image/*">', '(nepovinné)') ?>
        <?= field('…nebo URL fotky „před"', 'image_before_url', ['type' => 'url', 'placeholder' => 'https://…']) ?>
    <?= card_foot('<button type="submit" class="btn btn-primary">' . icon('plus', 'ic ic-sm') . ' Přidat</button>') ?>
</form>

<?php if ($items === []): ?>
    <div class="card"><div class="card-body empty">Zatím žádné ukázky.</div></div>
<?php else: ?>
    <div class="grid">
        <?php foreach ($items as $item): ?>
            <div class="card">
                <div class="card-body">
                    <div class="po-pair">
                        <?php if (($item['image_before'] ?? '') !== ''): ?>
                        <figure class="po-shot"><img src="<?= escape($item['image_before']) ?>" alt="před"><figcaption>před</figcaption></figure>
                        <figure class="po-shot"><img src="<?= escape($item['image_path']) ?>" alt="po"><figcaption>po</figcaption></figure>
                        <?php else: ?>
                        <figure class="po-shot"><img src="<?= escape($item['image_path']) ?>" alt="<?= escape($item['title']) ?>"></figure>
                        <?php endif; ?>
                    </div>
                    <div class="po-meta">
                        <div class="po-info">
                            <div class="cell-strong"><?= escape($item['title']) ?></div>
                            <p class="cell-sub"><?= escape($item['description']) ?></p>
                        </div>
                        <form method="post" action="/admin/portfolio" class="inline" onsubmit="return confirm('Smazat ukázku?')">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="_action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <button class="btn btn-danger btn-sm" type="submit"><?= icon('trash', 'ic ic-sm') ?> Smazat</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

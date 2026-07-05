<?php

declare(strict_types=1);

use App\Support\Csrf;

/** @var array<int, array<string, mixed>> $items */
$e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<h1>Ukázky práce (Portfolio)</h1>

<div class="card">
    <h2 style="margin-top:0;font-size:1.1rem;">Přidat ukázku</h2>
    <form method="post" action="/admin/portfolio" enctype="multipart/form-data">
        <?= Csrf::field() ?>
        <label>Název</label>
        <input type="text" name="title" required>
        <label>Popis</label>
        <textarea name="description"></textarea>
        <label>Obrázek (nahrát soubor)</label>
        <input type="file" name="image" accept="image/*">
        <label>…nebo URL obrázku</label>
        <input type="url" name="image_url" placeholder="https://…">
        <div style="margin-top:1rem"><button type="submit">Přidat</button></div>
    </form>
</div>

<div class="grid">
    <?php foreach ($items as $i): ?>
        <div class="card">
            <img class="thumb" src="<?= $e($i['image_path']) ?>" alt="<?= $e($i['title']) ?>">
            <strong><?= $e($i['title']) ?></strong>
            <p style="color:#64748b;font-size:.9rem"><?= $e($i['description']) ?></p>
            <form method="post" action="/admin/portfolio" onsubmit="return confirm('Smazat ukázku?')">
                <?= Csrf::field() ?>
                <input type="hidden" name="_action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $i['id'] ?>">
                <button class="danger" type="submit">Smazat</button>
            </form>
        </div>
    <?php endforeach; ?>
    <?php if ($items === []): ?><p>Zatím žádné ukázky.</p><?php endif; ?>
</div>

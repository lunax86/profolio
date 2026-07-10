<?php

declare(strict_types=1);

use App\Support\Csrf;

/** @var array<int, array<string, mixed>> $items */
?>
<div class="notice notice-warn">
    <?= icon('alert', 'ic ic-sm') ?>
    Zveřejňujte jen skutečné recenze od reálných zákazníků (ideálně s jejich svolením). Vymýšlené nebo neověřené recenze zakazuje směrnice Omnibus i český zákon o ochraně spotřebitele a hrozí za ně pokuta.
</div>

<form method="post" action="/admin/reviews">
    <?= Csrf::field() ?>
    <?= card_open('Přidat recenzi', 'Krátká citace spokojeného zákazníka') ?>
        <?= field('Jméno / autor', 'author', ['required' => true, 'placeholder' => 'např. Petra K.']) ?>
        <?= field('Odkud / co (nepovinné)', 'role', ['placeholder' => 'např. Praha, rekonstrukce bytu']) ?>
        <?= field_wrap('Text recenze', '<textarea name="text" required placeholder="Co zákazník řekl o vaší práci."></textarea>') ?>
    <?= card_foot('<button type="submit" class="btn btn-primary">' . icon('plus', 'ic ic-sm') . ' Přidat</button>') ?>
</form>

<?php if ($items === []): ?>
    <div class="card"><div class="card-body empty">Zatím žádné recenze.</div></div>
<?php else: ?>
    <?php foreach ($items as $item): ?>
    <div class="card">
        <div class="card-body">
            <div class="po-meta">
                <div class="po-info">
                    <p class="inq-msg"><?= escape($item['text']) ?></p>
                    <div class="cell-strong"><?= escape($item['author']) ?></div>
                    <?php if (($item['role'] ?? '') !== ''): ?>
                    <span class="cell-sub"><?= escape($item['role']) ?></span>
                    <?php endif; ?>
                </div>
                <form method="post" action="/admin/reviews" class="inline" onsubmit="return confirm('Smazat recenzi?')">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="_action" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                    <button class="btn btn-danger btn-sm" type="submit"><?= icon('trash', 'ic ic-sm') ?> Smazat</button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

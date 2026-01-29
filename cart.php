<?php

require_once __DIR__ . '/includes/bootstrap.php';

$items = cart_items_with_products();
$total = cart_total_amount();

require_once __DIR__ . '/includes/header.php';

?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">سلة المشتريات</h5>
    <a class="btn btn-outline-dark btn-sm" href="<?php echo e(url_path('menu.php')); ?>">متابعة التسوق</a>
</div>

<?php if (!$items): ?>
    <div class="alert alert-secondary">السلة فارغة.</div>
<?php else: ?>
    <div class="card shadow-sm mb-3">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th style="width:180px;">الكمية</th>
                        <th style="width:140px;">السعر</th>
                        <th style="width:140px;">الإجمالي</th>
                        <th style="width:120px;"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <?php $p = $item['product']; ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?php echo e($p['name']); ?></div>
                            <div class="text-muted small"><?php echo ((int)$p['is_available'] === 1) ? 'متاح' : 'غير متاح'; ?></div>
                        </td>
                        <td>
                            <form class="d-flex gap-2" method="post" action="<?php echo e(url_path('cart_action.php')); ?>">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                                <input type="hidden" name="return" value="cart.php">
                                <input type="number" name="quantity" class="form-control form-control-sm" min="0" value="<?php echo (int)$item['quantity']; ?>">
                                <button class="btn btn-sm btn-outline-dark" type="submit">تحديث</button>
                            </form>
                        </td>
                        <td><?php echo number_format((float)$p['price'], 2); ?>$</td>
                        <td class="fw-bold"><?php echo number_format((float)$item['subtotal'], 2); ?>$</td>
                        <td>
                            <form method="post" action="<?php echo e(url_path('cart_action.php')); ?>">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                                <input type="hidden" name="return" value="cart.php">
                                <button class="btn btn-sm btn-outline-danger" type="submit">حذف</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <form method="post" action="<?php echo e(url_path('cart_action.php')); ?>">
            <input type="hidden" name="action" value="clear">
            <input type="hidden" name="return" value="cart.php">
            <button class="btn btn-outline-danger" type="submit">تفريغ السلة</button>
        </form>
        <div class="text-end">
            <div class="text-muted">الإجمالي</div>
            <div class="fs-4 fw-bold"><?php echo number_format((float)$total, 2); ?>$</div>
            <a class="btn btn-coffee mt-2" href="<?php echo e(url_path('checkout.php')); ?>">الدفع</a>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

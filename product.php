<?php

require_once __DIR__ . '/includes/bootstrap.php';

$id = (int)get('id', 0);
if ($id <= 0) {
    redirect_to('menu.php');
}

$stmt = db()->prepare('SELECT p.id, p.name, p.price, p.description, p.is_available, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    redirect_to('menu.php');
}

require_once __DIR__ . '/includes/header.php';

?>
<div class="row g-3">
    <div class="col-12">
        <a class="text-decoration-none" href="<?php echo e(url_path('menu.php')); ?>">رجوع للمنيو</a>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="mb-1"><?php echo e($product['name']); ?></h4>
                        <div class="text-muted"><?php echo e($product['category_name']); ?></div>
                    </div>
                    <div class="fs-4 fw-bold"><?php echo number_format((float)$product['price'], 2); ?>$</div>
                </div>
                <hr>
                <div class="text-muted"><?php echo nl2br(e($product['description'])); ?></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <?php if ((int)$product['is_available'] !== 1): ?>
                    <div class="alert alert-warning mb-0">المنتج غير متاح حالياً.</div>
                <?php else: ?>
                    <form method="post" action="<?php echo e(url_path('cart_action.php')); ?>">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                        <input type="hidden" name="return" value="product.php?id=<?php echo (int)$product['id']; ?>">

                        <label class="form-label">الكمية</label>
                        <input type="number" class="form-control" name="quantity" value="1" min="1" required>

                        <button class="btn btn-coffee w-100 mt-3" type="submit">إضافة للسلة</button>
                        <a class="btn btn-outline-dark w-100 mt-2" href="<?php echo e(url_path('cart.php')); ?>">عرض السلة</a>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

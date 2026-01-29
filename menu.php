<?php

require_once __DIR__ . '/includes/bootstrap.php';

$categoryId = (int)get('category', 0);

$categories = db()->query('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name')->fetchAll();

$sql = 'SELECT p.id, p.name, p.price, p.description, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.is_available = 1';
$params = [];
if ($categoryId > 0) {
    $sql .= ' AND p.category_id = ?';
    $params[] = $categoryId;
}
$sql .= ' ORDER BY p.name';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';

?>
<div class="row g-3">
    <div class="col-12 col-lg-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="fw-bold mb-2">التصنيفات</div>
                <div class="list-group">
                    <a class="list-group-item list-group-item-action <?php echo $categoryId === 0 ? 'active' : ''; ?>" href="<?php echo e(url_path('menu.php')); ?>">الكل</a>
                    <?php foreach ($categories as $cat): ?>
                        <a class="list-group-item list-group-item-action <?php echo ((int)$cat['id'] === $categoryId) ? 'active' : ''; ?>" href="<?php echo e(url_path('menu.php?category=' . (int)$cat['id'])); ?>"><?php echo e($cat['name']); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">المنيو</h5>
            <a class="btn btn-outline-dark btn-sm" href="<?php echo e(url_path('cart.php')); ?>">اذهب للسلة</a>
        </div>

        <div class="row g-3">
            <?php if (!$products): ?>
                <div class="col-12">
                    <div class="alert alert-secondary">لا توجد منتجات حالياً.</div>
                </div>
            <?php endif; ?>

            <?php foreach ($products as $p): ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="fw-bold"><?php echo e($p['name']); ?></div>
                                    <div class="text-muted small"><?php echo e($p['category_name']); ?></div>
                                </div>
                                <div class="fw-bold"><?php echo number_format((float)$p['price'], 2); ?>$</div>
                            </div>
                            <div class="mt-2 text-muted small"><?php echo e(mb_strimwidth((string)$p['description'], 0, 100, '...')); ?></div>

                            <div class="mt-3 d-flex gap-2 align-items-center">
                                <a class="btn btn-sm btn-outline-dark" href="<?php echo e(url_path('product.php?id=' . (int)$p['id'])); ?>">التفاصيل</a>
                                <form method="post" action="<?php echo e(url_path('cart_action.php')); ?>" class="ms-auto d-flex gap-2">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                                    <input type="hidden" name="return" value="menu.php<?php echo $categoryId > 0 ? ('?category=' . $categoryId) : ''; ?>">
                                    <input type="number" class="form-control form-control-sm" name="quantity" value="1" min="1" style="width:90px;">
                                    <button class="btn btn-sm btn-coffee" type="submit">إضافة</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

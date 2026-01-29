<?php

require_once __DIR__ . '/includes/bootstrap.php';

$categories = db()->query('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name')->fetchAll();
$stmt = db()->prepare('SELECT p.id, p.name, p.price, p.description, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.is_available = 1 ORDER BY p.created_at DESC LIMIT 6');
$stmt->execute();
$featured = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';

?>
<div class="p-4 p-md-5 mb-4 text-white rounded" style="background:linear-gradient(120deg,#2b1b12,#8b5a2b);">
    <div class="col-md-8 px-0">
        <h1 class="display-6">أهلاً بك في <?php echo e(APP_NAME); ?></h1>
        <p class="lead my-3">اطلب قهوتك المفضلة واحجز طاولة بسهولة عبر الإنترنت.</p>
        <p class="lead mb-0">
            <a class="btn btn-light" href="<?php echo e(url_path('menu.php')); ?>">استعرض المنيو</a>
            <a class="btn btn-outline-light" href="<?php echo e(url_path('reservation.php')); ?>">احجز طاولة</a>
        </p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <h5 class="mb-3">التصنيفات</h5>
    </div>
    <?php foreach ($categories as $cat): ?>
        <div class="col-6 col-md-3">
            <a class="text-decoration-none" href="<?php echo e(url_path('menu.php?category=' . (int)$cat['id'])); ?>">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="fw-semibold text-dark"><?php echo e($cat['name']); ?></div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3">
    <div class="col-12 d-flex align-items-center justify-content-between">
        <h5 class="mb-0">منتجات مميزة</h5>
        <a href="<?php echo e(url_path('menu.php')); ?>" class="text-decoration-none">عرض الكل</a>
    </div>
    <?php foreach ($featured as $p): ?>
        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fw-bold"><?php echo e($p['name']); ?></div>
                            <div class="text-muted small"><?php echo e($p['category_name']); ?></div>
                        </div>
                        <div class="fw-bold"><?php echo number_format((float)$p['price'], 2); ?>$</div>
                    </div>
                    <div class="mt-2 text-muted small"><?php echo e(mb_strimwidth((string)$p['description'], 0, 90, '...')); ?></div>
                    <div class="mt-3 d-flex gap-2">
                        <a class="btn btn-sm btn-outline-dark" href="<?php echo e(url_path('product.php?id=' . (int)$p['id'])); ?>">التفاصيل</a>
                        <form method="post" action="<?php echo e(url_path('cart_action.php')); ?>" class="ms-auto">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="return" value="index.php">
                            <button class="btn btn-sm btn-coffee" type="submit">إضافة للسلة</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

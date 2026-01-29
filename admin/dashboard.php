<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

$stats = [
    'orders' => (int)db()->query('SELECT COUNT(*) AS c FROM orders')->fetch()['c'],
    'reservations' => (int)db()->query('SELECT COUNT(*) AS c FROM reservations')->fetch()['c'],
    'products' => (int)db()->query('SELECT COUNT(*) AS c FROM products')->fetch()['c'],
    'categories' => (int)db()->query('SELECT COUNT(*) AS c FROM categories')->fetch()['c'],
    'users' => (int)db()->query('SELECT COUNT(*) AS c FROM users')->fetch()['c'],
];

require_once __DIR__ . '/../includes/header.php';

?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">لوحة تحكم الأدمن</h5>
    <a class="btn btn-outline-dark btn-sm" href="<?php echo e(url_path('index.php')); ?>">الواجهة</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-2"><div class="card shadow-sm"><div class="card-body text-center"><div class="text-muted small">الطلبات</div><div class="fs-4 fw-bold"><?php echo $stats['orders']; ?></div></div></div></div>
    <div class="col-6 col-lg-2"><div class="card shadow-sm"><div class="card-body text-center"><div class="text-muted small">الحجوزات</div><div class="fs-4 fw-bold"><?php echo $stats['reservations']; ?></div></div></div></div>
    <div class="col-6 col-lg-2"><div class="card shadow-sm"><div class="card-body text-center"><div class="text-muted small">المنتجات</div><div class="fs-4 fw-bold"><?php echo $stats['products']; ?></div></div></div></div>
    <div class="col-6 col-lg-2"><div class="card shadow-sm"><div class="card-body text-center"><div class="text-muted small">التصنيفات</div><div class="fs-4 fw-bold"><?php echo $stats['categories']; ?></div></div></div></div>
    <div class="col-6 col-lg-2"><div class="card shadow-sm"><div class="card-body text-center"><div class="text-muted small">المستخدمون</div><div class="fs-4 fw-bold"><?php echo $stats['users']; ?></div></div></div></div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">الإدارة</h6>
                <div class="d-grid gap-2">
                    <a class="btn btn-outline-dark" href="<?php echo e(url_path('admin/orders.php')); ?>">إدارة الطلبات</a>
                    <a class="btn btn-outline-dark" href="<?php echo e(url_path('admin/reservations.php')); ?>">إدارة الحجوزات</a>
                    <a class="btn btn-outline-dark" href="<?php echo e(url_path('admin/categories.php')); ?>">إدارة التصنيفات</a>
                    <a class="btn btn-outline-dark" href="<?php echo e(url_path('admin/products.php')); ?>">إدارة المنتجات</a>
                    <a class="btn btn-outline-dark" href="<?php echo e(url_path('admin/users.php')); ?>">إدارة المستخدمين</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">التقارير (مبدئي)</h6>
                <?php
                    $salesToday = db()->query("SELECT COALESCE(SUM(total_amount),0) AS s FROM orders WHERE DATE(created_at) = CURDATE() AND status NOT IN ('cancelled')")->fetch();
                    $salesWeek = db()->query("SELECT COALESCE(SUM(total_amount),0) AS s FROM orders WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) AND status NOT IN ('cancelled')")->fetch();
                    $salesMonth = db()->query("SELECT COALESCE(SUM(total_amount),0) AS s FROM orders WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) AND status NOT IN ('cancelled')")->fetch();
                ?>
                <div class="d-flex justify-content-between mb-2"><div class="text-muted">مبيعات اليوم</div><div class="fw-bold"><?php echo number_format((float)$salesToday['s'], 2); ?>$</div></div>
                <div class="d-flex justify-content-between mb-2"><div class="text-muted">مبيعات الأسبوع</div><div class="fw-bold"><?php echo number_format((float)$salesWeek['s'], 2); ?>$</div></div>
                <div class="d-flex justify-content-between"><div class="text-muted">مبيعات الشهر</div><div class="fw-bold"><?php echo number_format((float)$salesMonth['s'], 2); ?>$</div></div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

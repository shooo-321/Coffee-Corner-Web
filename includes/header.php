<?php

$user = auth_user();
$cartCount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cartCount += (int)$qty;
    }
}

?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(APP_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(url_path('assets/css/style.css')); ?>">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark" style="background:#2b1b12;">
    <div class="container">
        <a class="navbar-brand" href="<?php echo e(url_path('index.php')); ?>"><?php echo e(APP_NAME); ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?php echo e(url_path('index.php')); ?>">الرئيسية</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo e(url_path('menu.php')); ?>">المنيو</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo e(url_path('reservation.php')); ?>">حجز طاولة</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo e(url_path('cart.php')); ?>">السلة (<?php echo (int)$cartCount; ?>)</a></li>
            </ul>
            <ul class="navbar-nav">
                <?php if ($user): ?>
                    <?php if ($user['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(url_path('admin/dashboard.php')); ?>">لوحة الأدمن</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(url_path('customer/dashboard.php')); ?>">حسابي</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo e(url_path('auth/logout.php')); ?>">تسجيل خروج</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo e(url_path('auth/login.php')); ?>">تسجيل دخول</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo e(url_path('auth/register.php')); ?>">حساب جديد</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">

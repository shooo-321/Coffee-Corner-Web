<?php

require_once __DIR__ . '/includes/bootstrap.php';

require_login();

$user = auth_user();
$items = cart_items_with_products();
$total = cart_total_amount();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$items) {
        $errors[] = 'السلة فارغة.';
    }

    $phone = trim((string)post('phone', ''));
    $address = trim((string)post('address', ''));
    $paymentMethod = trim((string)post('payment_method', 'cash'));

    if ($phone === '') {
        $errors[] = 'يرجى إدخال رقم الهاتف.';
    }
    if ($address === '') {
        $errors[] = 'يرجى إدخال العنوان.';
    }
    if (!in_array($paymentMethod, ['cash', 'card'], true)) {
        $errors[] = 'طريقة دفع غير صالحة.';
    }

    foreach ($items as $item) {
        if ((int)$item['product']['is_available'] !== 1) {
            $errors[] = 'يوجد منتج غير متاح في السلة. يرجى إزالته أولاً.';
            break;
        }
    }

    if (!$errors) {
        $pdo = db();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('UPDATE users SET phone = ?, address = ? WHERE id = ?');
            $stmt->execute([$phone, $address, $user['id']]);

            $orderNumber = 'CC-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

            $stmt = $pdo->prepare('INSERT INTO orders (user_id, order_number, total_amount, status, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $user['id'],
                $orderNumber,
                $total,
                'pending',
                $paymentMethod,
                'pending'
            ]);

            $orderId = (int)$pdo->lastInsertId();

            $stmtDetail = $pdo->prepare('INSERT INTO order_details (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)');
            foreach ($items as $item) {
                $p = $item['product'];
                $qty = (int)$item['quantity'];
                $price = (float)$p['price'];
                $subtotal = (float)$item['subtotal'];

                $stmtDetail->execute([$orderId, (int)$p['id'], $qty, $price, $subtotal]);
            }

            $pdo->commit();
            cart_clear();

            redirect_to('customer/dashboard.php');
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'حدث خطأ أثناء إنشاء الطلب. حاول مرة أخرى.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';

?>
<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">الدفع</h5>

                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $e): ?>
                            <div><?php echo e($e); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo e($user['phone'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">العنوان</label>
                        <textarea name="address" class="form-control" rows="3" required><?php echo e($user['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">طريقة الدفع</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">كاش</option>
                            <option value="card">بطاقة</option>
                        </select>
                    </div>

                    <button class="btn btn-coffee" type="submit">تأكيد الطلب</button>
                    <a class="btn btn-outline-dark" href="<?php echo e(url_path('cart.php')); ?>">رجوع للسلة</a>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="mb-2">ملخص الطلب</h6>
                <?php if (!$items): ?>
                    <div class="text-muted">السلة فارغة.</div>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <div class="d-flex justify-content-between small mb-2">
                            <div><?php echo e($item['product']['name']); ?> × <?php echo (int)$item['quantity']; ?></div>
                            <div><?php echo number_format((float)$item['subtotal'], 2); ?>$</div>
                        </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <div>الإجمالي</div>
                        <div><?php echo number_format((float)$total, 2); ?>$</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

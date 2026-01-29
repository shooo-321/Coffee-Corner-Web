<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

$errors = [];
$success = '';

$allowedStatuses = ['pending','preparing','ready','delivered','cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)post('action', ''));

    if ($action === 'update_status') {
        $orderId = (int)post('order_id', 0);
        $status = trim((string)post('status', ''));
        if ($orderId <= 0 || !in_array($status, $allowedStatuses, true)) {
            $errors[] = 'بيانات غير صالحة.';
        } else {
            $stmt = db()->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $stmt->execute([$status, $orderId]);
            $success = 'تم تحديث حالة الطلب.';
        }
    }
}

$orderIdView = (int)get('view', 0);
$orderDetails = [];
$orderHeader = null;
if ($orderIdView > 0) {
    $stmt = db()->prepare('SELECT o.*, u.email, u.username FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?');
    $stmt->execute([$orderIdView]);
    $orderHeader = $stmt->fetch();

    if ($orderHeader) {
        $stmt = db()->prepare('SELECT od.quantity, od.price, od.subtotal, p.name FROM order_details od JOIN products p ON p.id = od.product_id WHERE od.order_id = ?');
        $stmt->execute([$orderIdView]);
        $orderDetails = $stmt->fetchAll();
    }
}

$stmt = db()->query('SELECT o.id, o.order_number, o.total_amount, o.status, o.payment_method, o.payment_status, o.created_at, u.email FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC');
$orders = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';

?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">إدارة الطلبات</h5>
    <a class="btn btn-outline-dark btn-sm" href="<?php echo e(url_path('admin/dashboard.php')); ?>">رجوع</a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo e($success); ?></div>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
            <div><?php echo e($e); ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($orderHeader): ?>
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold"><?php echo e($orderHeader['order_number']); ?></div>
                    <div class="text-muted small"><?php echo e($orderHeader['email']); ?> - <?php echo e($orderHeader['created_at']); ?></div>
                </div>
                <div class="fw-bold"><?php echo number_format((float)$orderHeader['total_amount'], 2); ?>$</div>
            </div>
            <hr>
            <?php foreach ($orderDetails as $d): ?>
                <div class="d-flex justify-content-between small mb-2">
                    <div><?php echo e($d['name']); ?> × <?php echo (int)$d['quantity']; ?></div>
                    <div><?php echo number_format((float)$d['subtotal'], 2); ?>$</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>العميل</th>
                    <th>الإجمالي</th>
                    <th>الحالة</th>
                    <th>الدفع</th>
                    <th>التاريخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td class="fw-semibold"><?php echo e($o['order_number']); ?></td>
                        <td><?php echo e($o['email']); ?></td>
                        <td><?php echo number_format((float)$o['total_amount'], 2); ?>$</td>
                        <td>
                            <form method="post" class="d-flex gap-2 align-items-center">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="order_id" value="<?php echo (int)$o['id']; ?>">
                                <select name="status" class="form-select form-select-sm" style="min-width:160px;">
                                    <?php foreach ($allowedStatuses as $st): ?>
                                        <option value="<?php echo e($st); ?>" <?php echo $o['status'] === $st ? 'selected' : ''; ?>><?php echo e($st); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-outline-dark" type="submit">حفظ</button>
                            </form>
                        </td>
                        <td><?php echo e($o['payment_method'] . ' / ' . $o['payment_status']); ?></td>
                        <td class="text-muted small"><?php echo e($o['created_at']); ?></td>
                        <td><a class="btn btn-sm btn-outline-dark" href="<?php echo e(url_path('admin/orders.php?view=' . (int)$o['id'])); ?>">تفاصيل</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_login();

$user = auth_user();
if ($user && $user['role'] === 'admin') {
    redirect_to('admin/dashboard.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)post('action', ''));

    if ($action === 'cancel_reservation') {
        $reservationId = (int)post('reservation_id', 0);
        if ($reservationId > 0) {
            $stmt = db()->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status IN ('pending','confirmed')");
            $stmt->execute([$reservationId, $user['id']]);
            if ($stmt->rowCount() > 0) {
                $success = 'تم إلغاء الحجز.';
            } else {
                $errors[] = 'لا يمكن إلغاء هذا الحجز.';
            }
        }
    }

    if ($action === 'update_profile') {
        $phone = trim((string)post('phone', ''));
        $address = trim((string)post('address', ''));

        if ($phone === '' || $address === '') {
            $errors[] = 'يرجى تعبئة رقم الهاتف والعنوان.';
        } else {
            $stmt = db()->prepare('UPDATE users SET phone = ?, address = ? WHERE id = ?');
            $stmt->execute([$phone, $address, $user['id']]);
            $success = 'تم تحديث البيانات.';
            $user = auth_user();
        }
    }
}

$stmt = db()->prepare('SELECT id, order_number, total_amount, status, payment_method, payment_status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

$stmt = db()->prepare('SELECT id, reservation_date, reservation_time, number_of_guests, status, created_at FROM reservations WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$reservations = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';

?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">لوحة العميل</h5>
    <a class="btn btn-outline-dark btn-sm" href="<?php echo e(url_path('menu.php')); ?>">طلب جديد</a>
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

<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">الملف الشخصي</h6>
                <div class="mb-2"><span class="text-muted">اسم المستخدم:</span> <?php echo e($user['username']); ?></div>
                <div class="mb-3"><span class="text-muted">البريد:</span> <?php echo e($user['email']); ?></div>

                <form method="post">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="mb-2">
                        <label class="form-label">الهاتف</label>
                        <input class="form-control" name="phone" value="<?php echo e($user['phone'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">العنوان</label>
                        <textarea class="form-control" name="address" rows="3" required><?php echo e($user['address'] ?? ''); ?></textarea>
                    </div>
                    <button class="btn btn-outline-dark w-100" type="submit">حفظ</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h6 class="mb-3">طلباتي</h6>
                <?php if (!$orders): ?>
                    <div class="text-muted">لا توجد طلبات بعد.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>رقم الطلب</th>
                                    <th>الإجمالي</th>
                                    <th>الحالة</th>
                                    <th>الدفع</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo e($o['order_number']); ?></td>
                                        <td><?php echo number_format((float)$o['total_amount'], 2); ?>$</td>
                                        <td><?php echo e($o['status']); ?></td>
                                        <td><?php echo e($o['payment_method'] . ' / ' . $o['payment_status']); ?></td>
                                        <td class="text-muted small"><?php echo e($o['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">حجوزاتي</h6>
                <?php if (!$reservations): ?>
                    <div class="text-muted">لا توجد حجوزات بعد.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>الوقت</th>
                                    <th>عدد الأشخاص</th>
                                    <th>الحالة</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservations as $r): ?>
                                    <tr>
                                        <td><?php echo e($r['reservation_date']); ?></td>
                                        <td><?php echo e($r['reservation_time']); ?></td>
                                        <td><?php echo (int)$r['number_of_guests']; ?></td>
                                        <td><?php echo e($r['status']); ?></td>
                                        <td>
                                            <?php if (in_array($r['status'], ['pending','confirmed'], true)): ?>
                                                <form method="post" class="m-0">
                                                    <input type="hidden" name="action" value="cancel_reservation">
                                                    <input type="hidden" name="reservation_id" value="<?php echo (int)$r['id']; ?>">
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">إلغاء</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

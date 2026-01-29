<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

$errors = [];
$success = '';

$allowedStatuses = ['pending','confirmed','cancelled','completed'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservationId = (int)post('reservation_id', 0);
    $status = trim((string)post('status', ''));

    if ($reservationId <= 0 || !in_array($status, $allowedStatuses, true)) {
        $errors[] = 'بيانات غير صالحة.';
    } else {
        $stmt = db()->prepare('UPDATE reservations SET status = ? WHERE id = ?');
        $stmt->execute([$status, $reservationId]);
        $success = 'تم تحديث حالة الحجز.';
    }
}

$stmt = db()->query('SELECT r.id, r.reservation_date, r.reservation_time, r.number_of_guests, r.special_requests, r.status, r.created_at, u.email FROM reservations r JOIN users u ON u.id = r.user_id ORDER BY r.created_at DESC');
$reservations = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';

?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">إدارة الحجوزات</h5>
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

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>العميل</th>
                    <th>التاريخ</th>
                    <th>الوقت</th>
                    <th>عدد الأشخاص</th>
                    <th>طلبات خاصة</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $r): ?>
                    <tr>
                        <td><?php echo e($r['email']); ?></td>
                        <td><?php echo e($r['reservation_date']); ?></td>
                        <td><?php echo e($r['reservation_time']); ?></td>
                        <td><?php echo (int)$r['number_of_guests']; ?></td>
                        <td class="text-muted small"><?php echo e($r['special_requests'] ?? ''); ?></td>
                        <td>
                            <form method="post" class="d-flex gap-2">
                                <input type="hidden" name="reservation_id" value="<?php echo (int)$r['id']; ?>">
                                <select name="status" class="form-select form-select-sm" style="min-width:160px;">
                                    <?php foreach ($allowedStatuses as $st): ?>
                                        <option value="<?php echo e($st); ?>" <?php echo $r['status'] === $st ? 'selected' : ''; ?>><?php echo e($st); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-outline-dark" type="submit">حفظ</button>
                            </form>
                        </td>
                        <td class="text-muted small"><?php echo e($r['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

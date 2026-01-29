<?php

require_once __DIR__ . '/includes/bootstrap.php';

require_login();

$user = auth_user();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservationDate = trim((string)post('reservation_date', ''));
    $reservationTime = trim((string)post('reservation_time', ''));
    $guests = (int)post('number_of_guests', 0);
    $requests = trim((string)post('special_requests', ''));

    if ($reservationDate === '') {
        $errors[] = 'يرجى اختيار التاريخ.';
    }
    if ($reservationTime === '') {
        $errors[] = 'يرجى اختيار الوقت.';
    }
    if ($guests <= 0) {
        $errors[] = 'يرجى إدخال عدد أشخاص صحيح.';
    }

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO reservations (user_id, reservation_date, reservation_time, number_of_guests, special_requests, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $user['id'],
            $reservationDate,
            $reservationTime,
            $guests,
            $requests === '' ? null : $requests,
            'pending'
        ]);
        $success = true;
    }
}

require_once __DIR__ . '/includes/header.php';

?>
<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">حجز طاولة</h5>

                <?php if ($success): ?>
                    <div class="alert alert-success">تم إرسال طلب الحجز بنجاح. سيتم التواصل معك لتأكيد الحجز.</div>
                <?php endif; ?>

                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $e): ?>
                            <div><?php echo e($e); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">التاريخ</label>
                        <input type="date" name="reservation_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوقت</label>
                        <input type="time" name="reservation_time" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">عدد الأشخاص</label>
                        <input type="number" name="number_of_guests" class="form-control" min="1" value="2" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">طلبات خاصة (اختياري)</label>
                        <textarea name="special_requests" class="form-control" rows="3"></textarea>
                    </div>
                    <button class="btn btn-coffee" type="submit">تأكيد الحجز</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="mb-2">معلومات مهمة</h6>
                <div class="text-muted small">سيظهر الحجز في لوحة حسابك ويمكنك إلغاءه من هناك قبل التأكيد.</div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

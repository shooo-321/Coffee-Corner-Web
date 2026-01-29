<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

$users = db()->query('SELECT id, username, email, phone, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';

?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">إدارة المستخدمين</h5>
    <a class="btn btn-outline-dark btn-sm" href="<?php echo e(url_path('admin/dashboard.php')); ?>">رجوع</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>اسم المستخدم</th>
                    <th>البريد</th>
                    <th>الهاتف</th>
                    <th>الدور</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="fw-semibold"><?php echo e($u['username']); ?></td>
                        <td><?php echo e($u['email']); ?></td>
                        <td><?php echo e($u['phone'] ?? ''); ?></td>
                        <td><?php echo e($u['role']); ?></td>
                        <td class="text-muted small"><?php echo e($u['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

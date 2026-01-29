<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)post('action', ''));

    if ($action === 'create') {
        $name = trim((string)post('name', ''));
        $description = trim((string)post('description', ''));
        $isActive = (int)post('is_active', 1) === 1 ? 1 : 0;

        if ($name === '') {
            $errors[] = 'يرجى إدخال اسم التصنيف.';
        } else {
            $stmt = db()->prepare('INSERT INTO categories (name, description, is_active) VALUES (?, ?, ?)');
            $stmt->execute([$name, $description === '' ? null : $description, $isActive]);
            $success = 'تم إضافة التصنيف.';
        }
    }

    if ($action === 'update') {
        $id = (int)post('id', 0);
        $name = trim((string)post('name', ''));
        $description = trim((string)post('description', ''));
        $isActive = (int)post('is_active', 1) === 1 ? 1 : 0;

        if ($id <= 0 || $name === '') {
            $errors[] = 'بيانات غير صالحة.';
        } else {
            $stmt = db()->prepare('UPDATE categories SET name = ?, description = ?, is_active = ? WHERE id = ?');
            $stmt->execute([$name, $description === '' ? null : $description, $isActive, $id]);
            $success = 'تم تحديث التصنيف.';
        }
    }

    if ($action === 'delete') {
        $id = (int)post('id', 0);
        if ($id > 0) {
            try {
                $stmt = db()->prepare('DELETE FROM categories WHERE id = ?');
                $stmt->execute([$id]);
                $success = 'تم حذف التصنيف.';
            } catch (Throwable $e) {
                $errors[] = 'لا يمكن حذف التصنيف (قد يكون مرتبطًا بمنتجات).';
            }
        }
    }
}

$categories = db()->query('SELECT * FROM categories ORDER BY created_at DESC')->fetchAll();

$editId = (int)get('edit', 0);
$edit = null;
if ($editId > 0) {
    $stmt = db()->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$editId]);
    $edit = $stmt->fetch();
}

require_once __DIR__ . '/../includes/header.php';

?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">إدارة التصنيفات</h5>
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

<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="mb-3"><?php echo $edit ? 'تعديل تصنيف' : 'إضافة تصنيف'; ?></h6>
                <form method="post">
                    <input type="hidden" name="action" value="<?php echo $edit ? 'update' : 'create'; ?>">
                    <?php if ($edit): ?>
                        <input type="hidden" name="id" value="<?php echo (int)$edit['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-2">
                        <label class="form-label">الاسم</label>
                        <input class="form-control" name="name" value="<?php echo e($edit['name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">الوصف</label>
                        <textarea class="form-control" name="description" rows="3"><?php echo e($edit['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">الحالة</label>
                        <select class="form-select" name="is_active">
                            <option value="1" <?php echo ($edit && (int)$edit['is_active'] === 1) ? 'selected' : ''; ?>>مفعل</option>
                            <option value="0" <?php echo ($edit && (int)$edit['is_active'] === 0) ? 'selected' : ''; ?>>غير مفعل</option>
                        </select>
                    </div>
                    <button class="btn btn-coffee w-100" type="submit">حفظ</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $c): ?>
                            <tr>
                                <td class="fw-semibold"><?php echo e($c['name']); ?></td>
                                <td><?php echo ((int)$c['is_active'] === 1) ? 'مفعل' : 'غير مفعل'; ?></td>
                                <td class="text-muted small"><?php echo e($c['created_at']); ?></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-dark" href="<?php echo e(url_path('admin/categories.php?edit=' . (int)$c['id'])); ?>">تعديل</a>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

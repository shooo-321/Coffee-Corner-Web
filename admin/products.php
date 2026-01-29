<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

$errors = [];
$success = '';

$categories = db()->query('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)post('action', ''));

    if ($action === 'create' || $action === 'update') {
        $id = (int)post('id', 0);
        $categoryId = (int)post('category_id', 0);
        $name = trim((string)post('name', ''));
        $description = trim((string)post('description', ''));
        $price = (float)post('price', 0);
        $isAvailable = (int)post('is_available', 1) === 1 ? 1 : 0;

        if ($categoryId <= 0 || $name === '' || $price <= 0) {
            $errors[] = 'يرجى إدخال بيانات صحيحة.';
        } else {
            if ($action === 'create') {
                $stmt = db()->prepare('INSERT INTO products (category_id, name, description, price, is_available) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$categoryId, $name, $description === '' ? null : $description, $price, $isAvailable]);
                $success = 'تم إضافة المنتج.';
            } else {
                if ($id <= 0) {
                    $errors[] = 'بيانات غير صالحة.';
                } else {
                    $stmt = db()->prepare('UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, is_available = ? WHERE id = ?');
                    $stmt->execute([$categoryId, $name, $description === '' ? null : $description, $price, $isAvailable, $id]);
                    $success = 'تم تحديث المنتج.';
                }
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)post('id', 0);
        if ($id > 0) {
            try {
                $stmt = db()->prepare('DELETE FROM products WHERE id = ?');
                $stmt->execute([$id]);
                $success = 'تم حذف المنتج.';
            } catch (Throwable $e) {
                $errors[] = 'لا يمكن حذف المنتج.';
            }
        }
    }
}

$products = db()->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC')->fetchAll();

$editId = (int)get('edit', 0);
$edit = null;
if ($editId > 0) {
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$editId]);
    $edit = $stmt->fetch();
}

require_once __DIR__ . '/../includes/header.php';

?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">إدارة المنتجات</h5>
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
                <h6 class="mb-3"><?php echo $edit ? 'تعديل منتج' : 'إضافة منتج'; ?></h6>
                <form method="post">
                    <input type="hidden" name="action" value="<?php echo $edit ? 'update' : 'create'; ?>">
                    <?php if ($edit): ?>
                        <input type="hidden" name="id" value="<?php echo (int)$edit['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-2">
                        <label class="form-label">التصنيف</label>
                        <select class="form-select" name="category_id" required>
                            <option value="">اختر...</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?php echo (int)$c['id']; ?>" <?php echo ($edit && (int)$edit['category_id'] === (int)$c['id']) ? 'selected' : ''; ?>><?php echo e($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">الاسم</label>
                        <input class="form-control" name="name" value="<?php echo e($edit['name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">الوصف</label>
                        <textarea class="form-control" name="description" rows="3"><?php echo e($edit['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">السعر</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="price" value="<?php echo e($edit ? (string)$edit['price'] : ''); ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">التوفر</label>
                        <select class="form-select" name="is_available">
                            <option value="1" <?php echo ($edit && (int)$edit['is_available'] === 1) ? 'selected' : ''; ?>>متاح</option>
                            <option value="0" <?php echo ($edit && (int)$edit['is_available'] === 0) ? 'selected' : ''; ?>>غير متاح</option>
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
                            <th>المنتج</th>
                            <th>التصنيف</th>
                            <th>السعر</th>
                            <th>التوفر</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td class="fw-semibold"><?php echo e($p['name']); ?></td>
                                <td><?php echo e($p['category_name']); ?></td>
                                <td><?php echo number_format((float)$p['price'], 2); ?>$</td>
                                <td><?php echo ((int)$p['is_available'] === 1) ? 'متاح' : 'غير متاح'; ?></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-dark" href="<?php echo e(url_path('admin/products.php?edit=' . (int)$p['id'])); ?>">تعديل</a>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
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

<?php

require_once __DIR__ . '/../includes/bootstrap.php';

if (is_logged_in()) {
    redirect_to('index.php');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)post('username', ''));
    $email = trim((string)post('email', ''));
    $password = (string)post('password', '');

    if ($username === '') {
        $errors[] = 'يرجى إدخال اسم المستخدم.';
    }
    if ($email === '') {
        $errors[] = 'يرجى إدخال البريد الإلكتروني.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.';
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'اسم المستخدم أو البريد الإلكتروني مستخدم بالفعل.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$username, $email, $hash, 'customer']);

            $id = (int)db()->lastInsertId();
            login_user(['id' => $id, 'role' => 'customer']);
            redirect_to('customer/dashboard.php');
        }
    }
}

require_once __DIR__ . '/../includes/header.php';

?>
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">إنشاء حساب</h5>

                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $e): ?>
                            <div><?php echo e($e); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">اسم المستخدم</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">كلمة المرور</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <button class="btn btn-coffee w-100" type="submit">تسجيل</button>
                    <a class="btn btn-outline-dark w-100 mt-2" href="<?php echo e(url_path('auth/login.php')); ?>">لدي حساب</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

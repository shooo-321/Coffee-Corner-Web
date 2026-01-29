<?php

require_once __DIR__ . '/../includes/bootstrap.php';

if (is_logged_in()) {
    redirect_to('index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)post('email', ''));
    $password = (string)post('password', '');

    if ($email === '' || $password === '') {
        $errors[] = 'يرجى إدخال البريد الإلكتروني وكلمة المرور.';
    } else {
        $stmt = db()->prepare('SELECT id, username, email, password, role FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'بيانات الدخول غير صحيحة.';
        } else {
            login_user($user);
            if ($user['role'] === 'admin') {
                redirect_to('admin/dashboard.php');
            }
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
                <h5 class="mb-3">تسجيل الدخول</h5>

                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $e): ?>
                            <div><?php echo e($e); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">كلمة المرور</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <button class="btn btn-coffee w-100" type="submit">دخول</button>
                    <a class="btn btn-outline-dark w-100 mt-2" href="<?php echo e(url_path('auth/register.php')); ?>">حساب جديد</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

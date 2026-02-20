<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/captcha.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        redirectTo('admin/dashboard.php');
    } else {
        redirectTo('dashboard.php');
    }
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $captcha = trim($_POST['captcha'] ?? '');

    if (!validateCaptcha($captcha)) {
        $error = 'Неверно решена капча.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ((int)($user['is_banned'] ?? 0) === 1) {
                $error = 'Ваш аккаунт заблокирован администратором.';
            } else {
                $_SESSION['user'] = [
                    'id' => (int)$user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                ];

                if ($user['role'] === 'admin') {
                    redirectTo('admin/dashboard.php');
                } else {
                    redirectTo('dashboard.php');
                }
                exit;
            }
        }

        if (!$error) {
            $error = 'Неверный email или пароль.';
        }
    }
}

$captchaQuestion = getCaptchaQuestion();

require __DIR__ . '/includes/header.php';
?>
<h1 class="h3 mb-3">Вход в систему</h1>

<?php if (isset($_GET['registered'])): ?>
    <div class="alert alert-success">Регистрация прошла успешно. Теперь войдите в аккаунт.</div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" class="card p-3 shadow-sm">
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Пароль</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Капча: сколько будет <?= htmlspecialchars($captchaQuestion) ?> ?</label>
        <input type="number" name="captcha" class="form-control" required>
    </div>
    <button class="btn btn-primary">Войти</button>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>

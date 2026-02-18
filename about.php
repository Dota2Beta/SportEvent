<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

$pdo->exec('CREATE TABLE IF NOT EXISTS feedback_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(120) NOT NULL,
    client_email VARCHAR(190) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)');

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_feedback'])) {
    $clientName = trim($_POST['client_name'] ?? '');
    $clientEmail = trim($_POST['client_email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($clientName === '' || $clientEmail === '' || $message === '') {
        $errors[] = 'Заполните все поля формы обратной связи.';
    }

    if ($clientEmail !== '' && !filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Укажите корректный email.';
    }

    if (mb_strlen($message) > 2000) {
        $errors[] = 'Сообщение слишком длинное. Максимум 2000 символов.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO feedback_messages (client_name, client_email, message) VALUES (?, ?, ?)');
        $stmt->execute([$clientName, $clientEmail, $message]);
        $success = 'Спасибо! Ваше сообщение отправлено.';
        $_POST = [];
    }
}

require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3">О нас</h1>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <p><strong>SportEvent</strong> — учебный проект для курсовой работы на тему организации спортивных мероприятий.</p>
        <p>Цель сайта — сделать удобную платформу, где участники могут:</p>
        <ul>
            <li>видеть актуальные спортивные события;</li>
            <li>быстро регистрироваться и входить в личный кабинет;</li>
            <li>записываться на выбранные мероприятия.</li>
        </ul>
        <p class="mb-0">Администратор управляет расписанием, добавляет новые события и контролирует общую статистику по заявкам.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h2 class="h5">Наша миссия</h2>
                <p class="mb-0">Повысить вовлеченность студентов и жителей города в активный и здоровый образ жизни через доступные спортивные события.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h2 class="h5">Для участников</h2>
                <p class="mb-0">Простая регистрация и понятный личный кабинет без лишних сложностей.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h2 class="h5">Для организаторов</h2>
                <p class="mb-0">Быстрое управление событиями и централизованный учет заявок участников.</p>
            </div>
        </div>
    </div>
</div>

<section class="card shadow-sm">
    <div class="card-header">Обратная связь</div>
    <div class="card-body">
        <p class="text-muted">Если у вас есть вопросы или предложения по работе сайта, отправьте сообщение через форму ниже.</p>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="send_feedback" value="1">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Ваше имя</label>
                    <input type="text" name="client_name" class="form-control" maxlength="120" required value="<?= htmlspecialchars($_POST['client_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="client_email" class="form-control" maxlength="190" required value="<?= htmlspecialchars($_POST['client_email'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Сообщение</label>
                    <textarea name="message" rows="5" class="form-control" maxlength="2000" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>
            </div>
            <button class="btn btn-primary mt-3">Отправить сообщение</button>
        </form>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

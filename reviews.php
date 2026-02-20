<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

$pdo->exec('CREATE TABLE IF NOT EXISTS reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    author_name VARCHAR(120) NOT NULL,
    content TEXT NOT NULL,
    rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)');

$errors = [];
$success = null;
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_review'])) {
    if (!$user || ($user['role'] ?? '') !== 'user') {
        $errors[] = 'Оставлять отзывы могут только авторизованные пользователи.';
    } else {
        $content = trim($_POST['content'] ?? '');
        $rating = (int)($_POST['rating'] ?? 0);

        if ($content === '') {
            $errors[] = 'Введите текст отзыва.';
        }

        if (mb_strlen($content) > 1000) {
            $errors[] = 'Отзыв слишком длинный. Максимум 1000 символов.';
        }

        if ($rating < 1 || $rating > 5) {
            $errors[] = 'Оценка должна быть от 1 до 5.';
        }

        if (!$errors) {
            $stmt = $pdo->prepare('INSERT INTO reviews (user_id, author_name, content, rating) VALUES (?, ?, ?, ?)');
            $stmt->execute([(int)$user['id'], $user['name'], $content, $rating]);
            $success = 'Спасибо! Ваш отзыв добавлен.';
            $_POST = [];
        }
    }
}

$reviews = $pdo->query('SELECT id, author_name, content, rating, created_at FROM reviews ORDER BY created_at DESC LIMIT 50')->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3">Отзывы участников</h1>
<p class="text-muted">Мнения участников о мероприятиях SportEvent.</p>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endforeach; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($user && ($user['role'] ?? '') === 'user'): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header">Оставить отзыв</div>
        <div class="card-body">
            <form method="post">
                <input type="hidden" name="add_review" value="1">
                <div class="mb-3">
                    <label class="form-label">Оценка</label>
                    <select name="rating" class="form-select" required>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= (int)($_POST['rating'] ?? 5) === $i ? 'selected' : '' ?>><?= $i ?> / 5</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ваш отзыв</label>
                    <textarea name="content" rows="4" class="form-control" maxlength="1000" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                </div>
                <button class="btn btn-primary">Отправить</button>
            </form>
        </div>
    </div>
<?php elseif (!$user): ?>
    <div class="alert alert-info">Чтобы оставить отзыв, войдите в аккаунт обычного пользователя.</div>
<?php endif; ?>

<?php if (!$reviews): ?>
    <div class="alert alert-info">Пока отзывов нет. Будьте первым!</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($reviews as $review): ?>
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="mb-2 text-warning">
                            <?= str_repeat('★', (int)$review['rating']) . str_repeat('☆', 5 - (int)$review['rating']) ?>
                        </div>
                        <p class="mb-2"><?= nl2br(htmlspecialchars($review['content'])) ?></p>
                        <div class="small text-muted">
                            <?= htmlspecialchars($review['author_name']) ?> · <?= date('d.m.Y', strtotime($review['created_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>

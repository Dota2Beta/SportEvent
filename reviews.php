<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/header.php';

$pdo->exec('CREATE TABLE IF NOT EXISTS reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_name VARCHAR(120) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)');

$reviews = $pdo->query('SELECT author_name, content, created_at FROM reviews ORDER BY created_at DESC LIMIT 20')->fetchAll();
?>

<h1 class="h3 mb-3">Отзывы участников</h1>
<p class="text-muted">Мнения участников о мероприятиях SportEvent.</p>

<?php if (!$reviews): ?>
    <div class="alert alert-info">Пока отзывов нет. Будьте первым!</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($reviews as $review): ?>
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
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

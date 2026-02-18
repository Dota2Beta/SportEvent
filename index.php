<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/header.php';

$stmt = $pdo->query('SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 6');
$events = $stmt->fetchAll();
?>

<section class="hero mb-4">
    <h1 class="display-6 fw-bold">Организация спортивных мероприятий</h1>
    <p class="mb-0">SportEvent помогает участникам быстро находить турниры, марафоны и городские спортивные события.</p>
</section>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Ближайшие мероприятия</h2>
    <?php if (!isLoggedIn()): ?>
        <a href="/register.php" class="btn btn-primary btn-sm">Присоединиться</a>
    <?php endif; ?>
</div>

<?php if (!$events): ?>
    <div class="alert alert-info">Пока нет запланированных мероприятий. Администратор добавит их в ближайшее время.</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($events as $event): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-event shadow-sm">
                    <div class="card-body">
                        <h3 class="h5"><?= htmlspecialchars($event['title']) ?></h3>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($event['location']) ?></p>
                        <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <span><?= date('d.m.Y', strtotime($event['event_date'])) ?></span>
                        <span>Мест: <?= (int)$event['capacity'] ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>

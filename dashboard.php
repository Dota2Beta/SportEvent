<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';
requireLogin();

$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $eventId = (int)$_POST['event_id'];

    $check = $pdo->prepare('SELECT id FROM registrations WHERE user_id = ? AND event_id = ?');
    $check->execute([$user['id'], $eventId]);

    if (!$check->fetch()) {
        $insert = $pdo->prepare('INSERT INTO registrations (user_id, event_id) VALUES (?, ?)');
        $insert->execute([$user['id'], $eventId]);
    }

    header('Location: /dashboard.php');
    exit;
}

$eventsStmt = $pdo->query('SELECT * FROM events ORDER BY event_date ASC');
$events = $eventsStmt->fetchAll();

$myEventsStmt = $pdo->prepare('SELECT e.title, e.event_date, e.location
    FROM registrations r
    JOIN events e ON e.id = r.event_id
    WHERE r.user_id = ?
    ORDER BY e.event_date ASC');
$myEventsStmt->execute([$user['id']]);
$myEvents = $myEventsStmt->fetchAll();

$registeredIdsStmt = $pdo->prepare('SELECT event_id FROM registrations WHERE user_id = ?');
$registeredIdsStmt->execute([$user['id']]);
$registeredIds = array_map('intval', array_column($registeredIdsStmt->fetchAll(), 'event_id'));

require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3">Личный кабинет</h1>
<p class="text-muted">Здесь можно записаться на мероприятие и отслеживать свои регистрации.</p>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header">Доступные мероприятия</div>
            <div class="card-body">
                <?php if (!$events): ?>
                    <p class="mb-0">Пока мероприятий нет.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($events as $event): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <h2 class="h6 mb-1"><?= htmlspecialchars($event['title']) ?></h2>
                                        <div class="small text-muted">
                                            <?= date('d.m.Y', strtotime($event['event_date'])) ?>, <?= htmlspecialchars($event['location']) ?>
                                        </div>
                                    </div>
                                    <?php if (in_array((int)$event['id'], $registeredIds, true)): ?>
                                        <span class="badge text-bg-success">Вы записаны</span>
                                    <?php else: ?>
                                        <form method="post">
                                            <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
                                            <button class="btn btn-sm btn-primary">Записаться</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header">Мои записи</div>
            <div class="card-body">
                <?php if (!$myEvents): ?>
                    <p class="mb-0">Вы пока не записаны ни на одно мероприятие.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($myEvents as $myEvent): ?>
                            <li class="list-group-item px-0">
                                <strong><?= htmlspecialchars($myEvent['title']) ?></strong><br>
                                <span class="small text-muted"><?= date('d.m.Y', strtotime($myEvent['event_date'])) ?>, <?= htmlspecialchars($myEvent['location']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

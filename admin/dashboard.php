<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
requireAdmin();

$success = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_event'])) {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $eventDate = $_POST['event_date'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 0);

        if ($title === '' || $description === '' || $eventDate === '' || $location === '' || $capacity <= 0) {
            $errors[] = 'Заполните все поля для создания мероприятия.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO events (title, description, event_date, location, capacity) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$title, $description, $eventDate, $location, $capacity]);
            $success = 'Мероприятие успешно добавлено.';
        }
    }

    if (isset($_POST['delete_event'])) {
        $eventId = (int)$_POST['event_id'];
        $pdo->prepare('DELETE FROM events WHERE id = ?')->execute([$eventId]);
        $success = 'Мероприятие удалено.';
    }
}

$statsUsers = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE role = "user"')->fetchColumn();
$statsEvents = (int)$pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
$statsRegs = (int)$pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn();

$events = $pdo->query('SELECT * FROM events ORDER BY event_date DESC')->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-3">Админ-панель</h1>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card text-bg-primary shadow-sm"><div class="card-body">Пользователей: <strong><?= $statsUsers ?></strong></div></div></div>
    <div class="col-md-4"><div class="card text-bg-success shadow-sm"><div class="card-body">Мероприятий: <strong><?= $statsEvents ?></strong></div></div></div>
    <div class="col-md-4"><div class="card text-bg-dark shadow-sm"><div class="card-body">Записей: <strong><?= $statsRegs ?></strong></div></div></div>
</div>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endforeach; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header">Добавить мероприятие</div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="create_event" value="1">
                    <div class="mb-2"><label class="form-label">Название</label><input class="form-control" name="title" required></div>
                    <div class="mb-2"><label class="form-label">Описание</label><textarea class="form-control" name="description" rows="3" required></textarea></div>
                    <div class="mb-2"><label class="form-label">Дата</label><input class="form-control" type="date" name="event_date" required></div>
                    <div class="mb-2"><label class="form-label">Место</label><input class="form-control" name="location" required></div>
                    <div class="mb-3"><label class="form-label">Лимит мест</label><input class="form-control" type="number" min="1" name="capacity" required></div>
                    <button class="btn btn-primary">Сохранить</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header">Список мероприятий</div>
            <div class="card-body">
                <?php if (!$events): ?>
                    <p class="mb-0">Список пуст.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Название</th>
                                <th>Дата</th>
                                <th>Место</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?= (int)$event['id'] ?></td>
                                    <td><?= htmlspecialchars($event['title']) ?></td>
                                    <td><?= date('d.m.Y', strtotime($event['event_date'])) ?></td>
                                    <td><?= htmlspecialchars($event['location']) ?></td>
                                    <td>
                                        <form method="post" onsubmit="return confirm('Удалить мероприятие?');">
                                            <input type="hidden" name="delete_event" value="1">
                                            <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
                                            <button class="btn btn-outline-danger btn-sm">Удалить</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>

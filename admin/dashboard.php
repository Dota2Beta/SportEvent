<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo->exec('CREATE TABLE IF NOT EXISTS reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    author_name VARCHAR(120) NOT NULL,
    content TEXT NOT NULL,
    rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)');

$success = null;
$errors = [];
$currentAdmin = currentUser();

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

    if (isset($_POST['update_event'])) {
        $eventId = (int)($_POST['event_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $eventDate = $_POST['event_date'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 0);

        if ($eventId <= 0 || $title === '' || $description === '' || $eventDate === '' || $location === '' || $capacity <= 0) {
            $errors[] = 'Заполните все поля для редактирования мероприятия.';
        } else {
            $stmt = $pdo->prepare('UPDATE events SET title = ?, description = ?, event_date = ?, location = ?, capacity = ? WHERE id = ?');
            $stmt->execute([$title, $description, $eventDate, $location, $capacity, $eventId]);
            $success = 'Мероприятие обновлено.';
        }
    }

    if (isset($_POST['delete_event'])) {
        $eventId = (int)($_POST['event_id'] ?? 0);
        $pdo->prepare('DELETE FROM events WHERE id = ?')->execute([$eventId]);
        $success = 'Мероприятие удалено.';
    }

    if (isset($_POST['delete_review'])) {
        $reviewId = (int)($_POST['review_id'] ?? 0);
        $pdo->prepare('DELETE FROM reviews WHERE id = ?')->execute([$reviewId]);
        $success = 'Отзыв удален.';
    }

    if (isset($_POST['toggle_ban_user'])) {
        $userId = (int)($_POST['user_id'] ?? 0);
        $newState = (int)($_POST['new_state'] ?? 0);

        if ($userId > 0) {
            $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $target = $stmt->fetch();

            if ($target && $target['role'] === 'admin') {
                $errors[] = 'Нельзя блокировать администратора.';
            } else {
                $upd = $pdo->prepare('UPDATE users SET is_banned = ? WHERE id = ?');
                $upd->execute([$newState === 1 ? 1 : 0, $userId]);
                $success = $newState === 1 ? 'Пользователь заблокирован.' : 'Пользователь разблокирован.';
            }
        }
    }

    if (isset($_POST['change_user_role'])) {
        $userId = (int)($_POST['user_id'] ?? 0);
        $newRole = $_POST['new_role'] ?? 'user';

        if (!in_array($newRole, ['user', 'admin'], true)) {
            $errors[] = 'Некорректная роль.';
        } elseif ($userId <= 0) {
            $errors[] = 'Пользователь не найден.';
        } elseif ($currentAdmin && $userId === (int)$currentAdmin['id'] && $newRole !== 'admin') {
            $errors[] = 'Нельзя понизить свою роль администратора.';
        } else {
            $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
            $stmt->execute([$newRole, $userId]);
            $success = 'Роль пользователя обновлена.';
        }
    }
}

$statsUsers = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE role = "user"')->fetchColumn();
$statsEvents = (int)$pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
$statsRegs = (int)$pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
$statsReviews = (int)$pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();

$events = $pdo->query('SELECT * FROM events ORDER BY event_date DESC')->fetchAll();
$reviews = $pdo->query('SELECT id, author_name, content, rating, created_at FROM reviews ORDER BY created_at DESC LIMIT 30')->fetchAll();
$users = $pdo->query('SELECT id, name, email, role, is_banned, created_at FROM users ORDER BY created_at DESC')->fetchAll();

$editingEvent = null;
if (isset($_GET['edit_event'])) {
    $editEventId = (int)$_GET['edit_event'];
    foreach ($events as $event) {
        if ((int)$event['id'] === $editEventId) {
            $editingEvent = $event;
            break;
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-3">Админ-панель</h1>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card text-bg-primary shadow-sm"><div class="card-body">Пользователей: <strong><?= $statsUsers ?></strong></div></div></div>
    <div class="col-md-3"><div class="card text-bg-success shadow-sm"><div class="card-body">Мероприятий: <strong><?= $statsEvents ?></strong></div></div></div>
    <div class="col-md-3"><div class="card text-bg-dark shadow-sm"><div class="card-body">Записей: <strong><?= $statsRegs ?></strong></div></div></div>
    <div class="col-md-3"><div class="card text-bg-secondary shadow-sm"><div class="card-body">Отзывов: <strong><?= $statsReviews ?></strong></div></div></div>
</div>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endforeach; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
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

    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header">Редактировать мероприятие</div>
            <div class="card-body">
                <?php if (!$editingEvent): ?>
                    <p class="mb-0 text-muted">Выберите мероприятие из таблицы ниже, нажав «Редактировать».</p>
                <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="update_event" value="1">
                        <input type="hidden" name="event_id" value="<?= (int)$editingEvent['id'] ?>">
                        <div class="mb-2"><label class="form-label">Название</label><input class="form-control" name="title" required value="<?= htmlspecialchars($editingEvent['title']) ?>"></div>
                        <div class="mb-2"><label class="form-label">Описание</label><textarea class="form-control" name="description" rows="3" required><?= htmlspecialchars($editingEvent['description']) ?></textarea></div>
                        <div class="mb-2"><label class="form-label">Дата</label><input class="form-control" type="date" name="event_date" required value="<?= htmlspecialchars($editingEvent['event_date']) ?>"></div>
                        <div class="mb-2"><label class="form-label">Место</label><input class="form-control" name="location" required value="<?= htmlspecialchars($editingEvent['location']) ?>"></div>
                        <div class="mb-3"><label class="form-label">Лимит мест</label><input class="form-control" type="number" min="1" name="capacity" required value="<?= (int)$editingEvent['capacity'] ?>"></div>
                        <button class="btn btn-warning">Обновить</button>
                        <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(siteUrl('admin/dashboard.php')) ?>">Отмена</a>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
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
                        <th>Лимит</th>
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
                            <td><?= (int)$event['capacity'] ?></td>
                            <td class="d-flex gap-2">
                                <a class="btn btn-outline-warning btn-sm" href="<?= htmlspecialchars(siteUrl('admin/dashboard.php')) ?>?edit_event=<?= (int)$event['id'] ?>">Редактировать</a>
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

<div class="card shadow-sm mb-4">
    <div class="card-header">Зарегистрированные пользователи (бан / разбан + роль)</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Роль</th>
                    <th>Статус</th>
                    <th>Дата</th>
                    <th>Блокировка</th>
                    <th>Смена роли</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $row): ?>
                    <tr>
                        <td><?= (int)$row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                        <td>
                            <?php if ((int)$row['is_banned'] === 1): ?>
                                <span class="badge text-bg-danger">Заблокирован</span>
                            <?php else: ?>
                                <span class="badge text-bg-success">Активен</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d.m.Y', strtotime($row['created_at'])) ?></td>
                        <td>
                            <?php if ($row['role'] === 'user'): ?>
                                <form method="post">
                                    <input type="hidden" name="toggle_ban_user" value="1">
                                    <input type="hidden" name="user_id" value="<?= (int)$row['id'] ?>">
                                    <input type="hidden" name="new_state" value="<?= (int)$row['is_banned'] === 1 ? 0 : 1 ?>">
                                    <button class="btn btn-outline-<?= (int)$row['is_banned'] === 1 ? 'success' : 'danger' ?> btn-sm">
                                        <?= (int)$row['is_banned'] === 1 ? 'Разбанить' : 'Забанить' ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">Недоступно</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" class="d-flex gap-2 align-items-center">
                                <input type="hidden" name="change_user_role" value="1">
                                <input type="hidden" name="user_id" value="<?= (int)$row['id'] ?>">
                                <select name="new_role" class="form-select form-select-sm" style="min-width: 120px;">
                                    <option value="user" <?= $row['role'] === 'user' ? 'selected' : '' ?>>user</option>
                                    <option value="admin" <?= $row['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                                </select>
                                <button class="btn btn-outline-primary btn-sm">Сменить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">Модерация отзывов</div>
    <div class="card-body">
        <?php if (!$reviews): ?>
            <p class="mb-0">Отзывов пока нет.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Автор</th>
                        <th>Оценка</th>
                        <th>Отзыв</th>
                        <th>Дата</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td><?= (int)$review['id'] ?></td>
                            <td><?= htmlspecialchars($review['author_name']) ?></td>
                            <td><?= (int)$review['rating'] ?>/5</td>
                            <td><?= htmlspecialchars($review['content']) ?></td>
                            <td><?= date('d.m.Y', strtotime($review['created_at'])) ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Удалить этот отзыв?');">
                                    <input type="hidden" name="delete_review" value="1">
                                    <input type="hidden" name="review_id" value="<?= (int)$review['id'] ?>">
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

<?php require __DIR__ . '/../includes/footer.php'; ?>

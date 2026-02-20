CREATE DATABASE IF NOT EXISTS sportevent CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sportevent;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    is_banned TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    event_date DATE NOT NULL,
    location VARCHAR(180) NOT NULL,
    capacity INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    event_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_event (user_id, event_id),
    CONSTRAINT fk_reg_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_reg_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS feedback_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(120) NOT NULL,
    client_email VARCHAR(190) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    author_name VARCHAR(120) NOT NULL,
    content TEXT NOT NULL,
    rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO users (name, email, password_hash, role, is_banned)
VALUES ('Admin', 'admin@sportevent.local', '$2y$12$zcAfGE515Pd2PQ7JU4u8GOJ9HBmupqGzZCKjBvvqx8GhlpaZMHVhu', 'admin', 0)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    password_hash = VALUES(password_hash),
    role = VALUES(role),
    is_banned = 0;

INSERT INTO events (title, description, event_date, location, capacity)
VALUES
('Городской марафон 5 км', 'Любительский забег для студентов и жителей города.', DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'Центральный парк', 250),
('Турнир по мини-футболу', 'Командный турнир между факультетами.', DATE_ADD(CURDATE(), INTERVAL 20 DAY), 'Стадион №2', 120),
('Открытая тренировка по волейболу', 'Тренировка с тренером и отбор в сборную.', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Спортзал А', 40)
ON DUPLICATE KEY UPDATE title = VALUES(title);

INSERT INTO reviews (user_id, author_name, content, rating)
VALUES
(NULL, 'Алексей П.', 'Очень удобный сайт: быстро нашел турнир и записался за пару минут.', 5),
(NULL, 'Марина К.', 'Понравилось, что в личном кабинете сразу видно все мои регистрации.', 4),
(NULL, 'Илья С.', 'Админ-панель простая, но для учебного проекта этого более чем достаточно.', 5);

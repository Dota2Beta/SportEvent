CREATE DATABASE IF NOT EXISTS sportevent CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sportevent;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
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

INSERT INTO users (name, email, password_hash, role)
VALUES ('Admin', 'admin@sportevent.local', '$2y$10$QROlRZ2cmPj59mCXmEyY1.6XnPv9x.2UN1Y.6nNso0B8v3X6flAPW', 'admin')
ON DUPLICATE KEY UPDATE email = email;

INSERT INTO events (title, description, event_date, location, capacity)
VALUES
('Городской марафон 5 км', 'Любительский забег для студентов и жителей города.', DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'Центральный парк', 250),
('Турнир по мини-футболу', 'Командный турнир между факультетами.', DATE_ADD(CURDATE(), INTERVAL 20 DAY), 'Стадион №2', 120),
('Открытая тренировка по волейболу', 'Тренировка с тренером и отбор в сборную.', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Спортзал А', 40)
ON DUPLICATE KEY UPDATE title = VALUES(title);

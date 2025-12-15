CREATE DATABASE mantiqiy;

USE mantiqiy;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50),
    lastname VARCHAR(50),
    username VARCHAR(50) UNIQUE,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer VARCHAR(255) NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE user_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    user_answer VARCHAR(255),
    is_correct TINYINT(1),
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (question_id) REFERENCES questions(id)
);
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- Users jadvaliga 'role' ustunini qo'shamiz
ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user';

-- Savollar jadvaliga javob varianti va rasm yo'lini aniq qilib olaylik (agar yo'q bo'lsa)
-- Masalan: question_text, image_path, answer, hint (yordam)
ALTER TABLE questions ADD COLUMN hint TEXT NULL; 

-- O'zingizni admin qilib qo'ying (ID raqamingizni bilsangiz o'rniga yozing, odatda 1)
UPDATE users SET role = 'admin' WHERE id = 1;
ALTER TABLE users ADD COLUMN bio TEXT NULL;
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN theme_color VARCHAR(20) DEFAULT '#00f2ff';
ALTER TABLE user_answers ADD COLUMN arena_id INT NOT NULL AFTER user_id;
CREATE TABLE IF NOT EXISTS arena_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    arena_id INT NOT NULL,
    question_id INT NOT NULL,
    points INT DEFAULT 10,      -- Shu arena uchun ball
    display_order INT DEFAULT 0, -- Savol chiqish tartibi
    FOREIGN KEY (arena_id) REFERENCES arenas(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);
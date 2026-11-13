
CREATE DATABASE IF NOT EXISTS f6561837_lab8;
USE f6561837_lab8;

-- Users and their roles
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL
);

-- Tasks assigned to users
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Sample users
-- Passwords will be set using PHP's password_hash().
INSERT INTO users (username, password_hash, role) VALUES
('alex', '', 'employee'),
('jordan', '', 'manager');

-- Sample tasks
INSERT INTO tasks (title, description, user_id) VALUES
('Update homepage', 'Improve the homepage layout', 1),
('Fix navigation', 'Resolve the mobile menu issue', 2);


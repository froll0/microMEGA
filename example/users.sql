CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL
);
INSERT INTO users (name, email) VALUES
('Mario Rossi', 'mario.rossi@example.com'),
('Luigi Bianchi', 'luigi.bianchi@example.com');
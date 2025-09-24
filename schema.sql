CREATE DATABASE IF NOT EXISTS reservation_system;
USE reservation_system;

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_name VARCHAR(255),
    people INT,
    date DATE,
    time VARCHAR(20),
    fname VARCHAR(100),
    lname VARCHAR(100),
    email VARCHAR(150),
    phone VARCHAR(50),
    purpose VARCHAR(100),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

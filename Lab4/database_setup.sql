-- Database Setup Script for Registration System

-- Create Database
CREATE DATABASE IF NOT EXISTS registration_db;
USE registration_db;

-- Drop tables if they exist to prevent conflicts
DROP TABLE IF EXISTS user_registration;
DROP TABLE IF EXISTS registration_details;

-- Create Registration Details Table
CREATE TABLE registration_details (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create User Registration Table
CREATE TABLE user_registration (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Others') NOT NULL,
    street VARCHAR(255),
    city VARCHAR(100),
    state ENUM('Sarawak', 'Sabah', 'Selangor', 'Johor', 'Penang') NOT NULL,
    country VARCHAR(100),
    postcode VARCHAR(20) NOT NULL,
    terms_accepted BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (registration_id) REFERENCES registration_details(registration_id)
);

-- Create unique index on email for faster lookups
CREATE UNIQUE INDEX idx_user_email ON user_registration(email);

-- Optional: Create a trigger to automatically create a registration_details entry
DELIMITER //
CREATE TRIGGER before_user_registration 
BEFORE INSERT ON user_registration
FOR EACH ROW
BEGIN
    INSERT INTO registration_details () VALUES ();
    SET NEW.registration_id = LAST_INSERT_ID();
END;//
DELIMITER ;

-- Grant privileges (adjust as needed)
GRANT SELECT, INSERT, UPDATE, DELETE ON registration_db.* TO 'registration_user'@'localhost' IDENTIFIED BY 'password';
FLUSH PRIVILEGES;
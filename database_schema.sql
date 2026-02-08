-- Database Schema for Maratech 2026 - Plateforme Solidaire

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'moderator', 'association', 'partner', 'donor', 'user') DEFAULT 'user',
    phone VARCHAR(20),
    address TEXT,
    bio TEXT,
    profile_image VARCHAR(255),
    total_donated DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Associations Table
CREATE TABLE IF NOT EXISTS associations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, 
    name VARCHAR(150),
    description TEXT,
    email VARCHAR(100), 
    phone VARCHAR(20),
    address TEXT,
    registration_number VARCHAR(100), -- New
    website_url VARCHAR(255),
    logo_url VARCHAR(255),
    verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. Cases Table
CREATE TABLE IF NOT EXISTS cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    association_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(50), 
    goal_amount DECIMAL(10, 2) NOT NULL,
    progress_amount DECIMAL(10, 2) DEFAULT 0.00,
    image_url VARCHAR(255),
    photos_urls JSON, -- New: For gallery
    beneficiary_name VARCHAR(100),
    beneficiary_story TEXT,
    cha9a9a_link VARCHAR(255) NOT NULL,
    status ENUM('pending', 'active', 'completed', 'resolved', 'closed') DEFAULT 'pending', -- Added pending
    is_urgent BOOLEAN DEFAULT FALSE,
    deadline DATETIME, -- New: Deadline
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (association_id) REFERENCES associations(id)
);

-- 4. Donations Table
CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT,
    user_id INT, 
    amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50),
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    is_anonymous BOOLEAN DEFAULT FALSE, -- New
    message TEXT, -- New
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 5. Events Table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    association_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATETIME NOT NULL,
    location VARCHAR(150),
    max_attendees INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (association_id) REFERENCES associations(id)
);

-- 6. Contact Messages Table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100), -- Matches 'name' in Model
    email VARCHAR(100),
    subject VARCHAR(150),
    message TEXT NOT NULL,
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'read', 'replied') DEFAULT 'pending', -- Matches 'status' usage
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. Saved Cases (Favorites)
CREATE TABLE IF NOT EXISTS saved_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    case_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE
);

-- 8. Volunteers (Benevoles)
CREATE TABLE IF NOT EXISTS volunteers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skills TEXT,
    availability TEXT,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 9. Event Attendees
CREATE TABLE IF NOT EXISTS event_attendees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 10. Daily Statistics (New)
CREATE TABLE IF NOT EXISTS daily_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stat_date DATE NOT NULL UNIQUE,
    total_donations DECIMAL(10, 2) DEFAULT 0.00,
    new_cases INT DEFAULT 0,
    new_donors INT DEFAULT 0,
    most_active_category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

COMMIT;

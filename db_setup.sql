DROP DATABASE IF EXISTS techfest_db2;
CREATE DATABASE techfest_db2;
USE techfest_db2;

-- 1. Unified Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('participant', 'organizer', 'judge') DEFAULT 'participant',
    phone VARCHAR(15),
    college VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Clubs
CREATE TABLE clubs (
    club_id INT AUTO_INCREMENT PRIMARY KEY,
    club_name VARCHAR(100) NOT NULL
);

-- 3. Events (With Date for Countdown)
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100) NOT NULL,
    description TEXT,
    event_date DATETIME, 
    venue VARCHAR(100),
    entry_fee DECIMAL(10,2) DEFAULT 0.00,
    image_path VARCHAR(255),
    club_id INT,
    status ENUM('upcoming', 'live', 'completed') DEFAULT 'upcoming',
    FOREIGN KEY (club_id) REFERENCES clubs(club_id)
);

-- 4. Teams (The New Logic)
CREATE TABLE teams (
    team_id INT AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL,
    leader_id INT,
    FOREIGN KEY (leader_id) REFERENCES users(user_id)
);

-- 5. Team Members (Linking Users to Teams)
CREATE TABLE team_members (
    team_id INT,
    user_id INT,
    PRIMARY KEY (team_id, user_id),
    FOREIGN KEY (team_id) REFERENCES teams(team_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 6. Registrations (Links TEAMS to Events)
CREATE TABLE registrations (
    reg_id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT,
    event_id INT,
    payment_status ENUM('pending', 'completed') DEFAULT 'pending',
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(team_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    UNIQUE(team_id, event_id)
);

-- 7. Accommodation
CREATE TABLE accommodation (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_type VARCHAR(50),
    capacity INT,
    cost_per_night DECIMAL(10,2),
    location VARCHAR(100)
);

-- 8. Bookings
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    room_id INT,
    check_in DATE,
    total_cost DECIMAL(10,2),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (room_id) REFERENCES accommodation(room_id)
);

-- 9. Results (For Judges)
CREATE TABLE results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT,
    winner_team_id INT,
    rank INT,
    comments TEXT,
    FOREIGN KEY (event_id) REFERENCES events(event_id),
    FOREIGN KEY (winner_team_id) REFERENCES teams(team_id)
);

-- 10. Sponsors
CREATE TABLE sponsors (
    sponsor_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100)
);

-- ================= SEED DATA FOR INVICTA =================

-- Clubs
INSERT INTO clubs (club_name) VALUES ('CodeGeeks'), ('RoboTech'), ('Literary Society'), ('Gaming Guild');

-- Events (Dates set to future for countdown)
INSERT INTO events (event_name, description, event_date, venue, entry_fee, club_id) VALUES 
('Hack-a-Thon 2025', 'The ultimate 24-hour coding marathon. Build, Deploy, Win.', '2025-12-10 09:00:00', 'Main Auditorium', 500.00, 1),
('RoboWar', 'Build your bot and destroy the competition in the arena.', '2025-12-12 14:00:00', 'Open Ground', 1000.00, 2),
('Valorant LAN', '5v5 Tactical Shooter Tournament.', '2025-12-11 10:00:00', 'Computer Lab 3', 250.00, 4),
('Debate: AI vs Human', 'The future of humanity discussed.', '2025-12-13 11:00:00', 'Seminar Hall', 100.00, 3);

-- Accommodation
INSERT INTO accommodation (room_type, capacity, cost_per_night, location) VALUES 
('Luxury Single', 1, 1200.00, 'Block A - VIP'),
('Double Shared', 2, 600.00, 'Block B'),
('Dormitory', 10, 200.00, 'Block C');

-- Sponsors
INSERT INTO sponsors (name) VALUES ('Google'), ('RedBull'), ('Nvidia'), ('GitHub');

-- Default Users (Password: 12345)
-- Hash: $2y$10$abcdefghijklmnopqrstuv (This is a dummy hash, for real use, register via the form)
INSERT INTO users (name, email, password, role) VALUES 
('Organizer Admin', 'admin@invicta.com', '$2y$10$abcdefghijklmnopqrstuv', 'organizer'),
('Dr. Judge', 'judge@invicta.com', '$2y$10$abcdefghijklmnopqrstuv', 'judge');
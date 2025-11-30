-- ====================================================
-- 1. DATABASE SETUP
-- ====================================================
CREATE DATABASE techfest_db;
USE techfest_db;

-- ====================================================
-- 2. USERS & ROLES (Independent Tables)
-- ====================================================

-- 1. Participants (Students) - NEEDS LOGIN
CREATE TABLE participants (
    participant_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Login Password
    phone VARCHAR(15) NOT NULL,
    college VARCHAR(150) NOT NULL,
    department VARCHAR(100) NOT NULL,
    year VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Mentors (Professors) - NEEDS LOGIN
CREATE TABLE mentors (
    mentor_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    
    department VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    designation VARCHAR(100) NOT NULL
);

-- 3. Coordinators (Student Heads) - NEEDS LOGIN
CREATE TABLE coordinators (
    coordinator_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Login Password
    phone VARCHAR(15) NOT NULL
);

-- 4. Clubs (Club Admin Accounts) - NEEDS LOGIN
CREATE TABLE clubs (
    club_id INT AUTO_INCREMENT PRIMARY KEY,
    club_name VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) UNIQUE, -- Club official email
    password VARCHAR(255) NOT NULL, -- Login Password
    description TEXT
);

-- 5. Judges (External - Usually No Login, just records)
CREATE TABLE judges (
    judge_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    affiliation VARCHAR(150) NOT NULL, 
    expertise VARCHAR(100), 
    email VARCHAR(150),
    phone VARCHAR(15)
);

-- 6. Volunteers (Helpers - Usually No Login)
CREATE TABLE volunteers (
    volunteer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. Accommodation (Rooms)
CREATE TABLE accommodation (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_type VARCHAR(50) NOT NULL, 
    cost DECIMAL(10,2) NOT NULL
);

-- 8. Sponsors (Companies)
CREATE TABLE sponsors (
    sponsor_id INT AUTO_INCREMENT PRIMARY KEY,
    organization_name VARCHAR(150) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ====================================================
-- 3. EVENTS & TEAMS (Dependent Tables)
-- ====================================================

-- 9. Events
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(100) NOT NULL,
    description TEXT,
    club_id INT, 
    coordinator_id INT, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(club_id) ON DELETE SET NULL,
    FOREIGN KEY (coordinator_id) REFERENCES coordinators(coordinator_id) ON DELETE SET NULL
);

-- 10. Teams
CREATE TABLE teams (
    team_id INT AUTO_INCREMENT PRIMARY KEY,
    tname VARCHAR(100) NOT NULL,
    leader INT, 
    mentor INT, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (leader) REFERENCES participants(participant_id) ON DELETE SET NULL,
    FOREIGN KEY (mentor) REFERENCES mentors(mentor_id) ON DELETE SET NULL
);

-- ====================================================
-- 4. RELATIONSHIPS & LOGIC (Linking Tables)
-- ====================================================

-- 11. Prizes (Linked to Event -> Winner can be Team OR Volunteer)
CREATE TABLE prizes (
    prize_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    prize_name VARCHAR(100) NOT NULL,
    prize_type VARCHAR(50) NOT NULL,
    value DECIMAL(10,2) DEFAULT 0.00,
    winning_team_id INT, 
    winning_volunteer_id INT,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (winning_team_id) REFERENCES teams(team_id) ON DELETE SET NULL,
    FOREIGN KEY (winning_volunteer_id) REFERENCES volunteers(volunteer_id) ON DELETE SET NULL
);

-- 12. Forms (Team Members: Participant <-> Team)
CREATE TABLE forms (
    p_id INT,
    t_id INT,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (p_id, t_id),
    FOREIGN KEY (p_id) REFERENCES participants(participant_id) ON DELETE CASCADE,
    FOREIGN KEY (t_id) REFERENCES teams(team_id) ON DELETE CASCADE
);

-- 13. Event_Judges (Event <-> Judge)
CREATE TABLE event_judges (
    event_id INT,
    judge_id INT,
    PRIMARY KEY (event_id, judge_id),
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (judge_id) REFERENCES judges(judge_id) ON DELETE CASCADE
);

-- 14. Event_Volunteers (Event <-> Volunteer)
CREATE TABLE event_volunteers (
    event_id INT,
    volunteer_id INT,
    assigned_role VARCHAR(100), 
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id, volunteer_id),
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (volunteer_id) REFERENCES volunteers(volunteer_id) ON DELETE CASCADE
);

-- 15. Bookings (Participant <-> Accommodation)
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    participant_id INT NOT NULL UNIQUE, 
    room_id INT NOT NULL,
    checkin_date DATE NOT NULL,
    checkout_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (participant_id) REFERENCES participants(participant_id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES accommodation(room_id) ON DELETE CASCADE
);

-- 16. Funds (Sponsor <-> Event)
CREATE TABLE funds (
    fund_id INT AUTO_INCREMENT PRIMARY KEY,
    sponsor_id INT,
    event_id INT,
    sponsorship_date DATE NOT NULL,
    sponsorship_type VARCHAR(50) NOT NULL, 
    amount_value DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sponsor_id) REFERENCES sponsors(sponsor_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);

-- 17. Registrations (Team <-> Event)
CREATE TABLE registrations (
    team_id INT,
    event_id INT,
    score INTEGER(3),
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (team_id, event_id),
    FOREIGN KEY (team_id) REFERENCES teams(team_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);
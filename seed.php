<?php
// setup.php - REBUILDS DATABASE STRUCTURE AND SEEDS DATA
// This fixes any "Table not found" or "Column missing" errors.

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'includes/db_connect.php';

echo "<style>
    body{background:#1a1a2e; color:#fff; font-family:monospace; padding:20px; line-height:1.4;} 
    .success{color:#4cd137;} 
    .error{color:#e84118;} 
    .info{color:#a2a8d3;}
    h2{border-bottom:1px solid #533483; color:#e94560; margin-top:30px;}
</style>";

echo "<h1>🛠️ Master Database Setup</h1>";

// ======================================================
// 1. DROP ALL TABLES (Reset Structure)
// ======================================================
echo "<h2>1. Resetting Database Structure...</h2>";
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

$tables_to_drop = [
    'registrations', 'event_judges', 'event_volunteers', 'funds', 'prizes', 
    'forms', 'teams', 'bookings', 'events', 'accommodation', 'room_types', 
    'volunteers', 'judges', 'coordinators', 'mentors', 'participants', 'sponsors', 'clubs'
];

foreach ($tables_to_drop as $t) {
    if ($conn->query("DROP TABLE IF EXISTS $t")) {
        echo "<div class='info'>Dropped table: $t</div>";
    }
}
$conn->query("SET FOREIGN_KEY_CHECKS = 1");


// ======================================================
// 2. CREATE TABLES (The Correct Schema)
// ======================================================
echo "<h2>2. Creating Tables...</h2>";

$schemas = [
    "CREATE TABLE participants (
        participant_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL DEFAULT '12345',
        phone VARCHAR(15) NOT NULL,
        college VARCHAR(150) NOT NULL,
        department VARCHAR(100) NOT NULL,
        year VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE mentors (
        mentor_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL DEFAULT '12345',
        department VARCHAR(100) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        designation VARCHAR(100) NOT NULL
    )",
    "CREATE TABLE coordinators (
        coordinator_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL DEFAULT '12345',
        phone VARCHAR(15) NOT NULL
    )",
    "CREATE TABLE clubs (
        club_id INT AUTO_INCREMENT PRIMARY KEY,
        club_name VARCHAR(100) NOT NULL UNIQUE,
        email VARCHAR(150) UNIQUE,
        password VARCHAR(255) NOT NULL DEFAULT '12345',
        description TEXT
    )",
    "CREATE TABLE judges (
        judge_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150),
        password VARCHAR(255) NOT NULL DEFAULT '12345',
        affiliation VARCHAR(150) NOT NULL,
        expertise VARCHAR(100),
        phone VARCHAR(15)
    )",
    "CREATE TABLE volunteers (
        volunteer_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) UNIQUE,
        password VARCHAR(255) DEFAULT '12345',
        phone VARCHAR(15) NOT NULL
    )",
    "CREATE TABLE sponsors (
        sponsor_id INT AUTO_INCREMENT PRIMARY KEY,
        organization_name VARCHAR(150) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        email VARCHAR(150)
    )",
    // ACCOMMODATION (Separate Tables Schema)
    "CREATE TABLE room_types (
        type_id INT AUTO_INCREMENT PRIMARY KEY,
        type_name VARCHAR(50) NOT NULL,
        cost DECIMAL(10,2) NOT NULL,
        capacity INT NOT NULL
    )",
    "CREATE TABLE accommodation (
        room_id INT AUTO_INCREMENT PRIMARY KEY,
        room_number VARCHAR(20) NOT NULL,
        type_id INT,
        current_occupancy INT DEFAULT 0,
        FOREIGN KEY (type_id) REFERENCES room_types(type_id) ON DELETE CASCADE
    )",
    "CREATE TABLE bookings (
        booking_id INT AUTO_INCREMENT PRIMARY KEY,
        participant_id INT NOT NULL UNIQUE,
        room_id INT NOT NULL,
        checkin_date DATE NOT NULL,
        checkout_date DATE NOT NULL,
        FOREIGN KEY (participant_id) REFERENCES participants(participant_id) ON DELETE CASCADE,
        FOREIGN KEY (room_id) REFERENCES accommodation(room_id) ON DELETE CASCADE
    )",
    // EVENTS & TEAMS
    "CREATE TABLE events (
        event_id INT AUTO_INCREMENT PRIMARY KEY,
        event_name VARCHAR(100) NOT NULL,
        event_date DATE NOT NULL,
        event_time TIME,
        venue VARCHAR(100) NOT NULL,
        description TEXT,
        image_path VARCHAR(255),
        club_id INT,
        coordinator_id INT,
        FOREIGN KEY (club_id) REFERENCES clubs(club_id) ON DELETE SET NULL,
        FOREIGN KEY (coordinator_id) REFERENCES coordinators(coordinator_id) ON DELETE SET NULL
    )",
    "CREATE TABLE teams (
        team_id INT AUTO_INCREMENT PRIMARY KEY,
        tname VARCHAR(100) NOT NULL,
        leader INT,
        mentor INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (leader) REFERENCES participants(participant_id) ON DELETE SET NULL,
        FOREIGN KEY (mentor) REFERENCES mentors(mentor_id) ON DELETE SET NULL
    )",
    "CREATE TABLE forms (
        p_id INT,
        t_id INT,
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (p_id, t_id),
        FOREIGN KEY (p_id) REFERENCES participants(participant_id) ON DELETE CASCADE,
        FOREIGN KEY (t_id) REFERENCES teams(team_id) ON DELETE CASCADE
    )",
    "CREATE TABLE registrations (
        team_id INT,
        event_id INT,
        score INT DEFAULT NULL,
        registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (team_id, event_id),
        FOREIGN KEY (team_id) REFERENCES teams(team_id) ON DELETE CASCADE,
        FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
    )",
    // LINKING TABLES
    "CREATE TABLE event_judges (
        event_id INT,
        judge_id INT,
        PRIMARY KEY (event_id, judge_id),
        FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
        FOREIGN KEY (judge_id) REFERENCES judges(judge_id) ON DELETE CASCADE
    )",
    "CREATE TABLE event_volunteers (
        event_id INT,
        volunteer_id INT,
        assigned_role VARCHAR(100),
        PRIMARY KEY (event_id, volunteer_id),
        FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
        FOREIGN KEY (volunteer_id) REFERENCES volunteers(volunteer_id) ON DELETE CASCADE
    )",
    "CREATE TABLE funds (
        fund_id INT AUTO_INCREMENT PRIMARY KEY,
        sponsor_id INT,
        event_id INT,
        sponsorship_date DATE,
        sponsorship_type VARCHAR(50),
        amount_value DECIMAL(10,2),
        FOREIGN KEY (sponsor_id) REFERENCES sponsors(sponsor_id) ON DELETE CASCADE,
        FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
    )",
    "CREATE TABLE prizes (
        prize_id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        prize_name VARCHAR(100) NOT NULL,
        prize_type VARCHAR(50) NOT NULL,
        value DECIMAL(10,2) DEFAULT 0.00,
        winning_team_id INT,
        FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
        FOREIGN KEY (winning_team_id) REFERENCES teams(team_id) ON DELETE SET NULL
    )"
];

foreach ($schemas as $sql) {
    if (!$conn->query($sql)) {
        die("<div class='error'>❌ SQL Error: " . $conn->error . "<br>Query: $sql</div>");
    }
}
echo "<div class='success'>✅ All Tables Created Successfully!</div>";


// ======================================================
// 3. SEED DATA (Insertions)
// ======================================================
echo "<h2>3. Seeding Data...</h2>";

// A. CLUBS
$clubs = [
    ['APS', 'Astronomy and Physics Society', 'aps@invicta.com'],
    ['ERS', 'Electronics and Robotic Club', 'ers@invicta.com'],
    ['TPC', 'The Programming Club', 'tpc@invicta.com'],
    ['BMC', 'Business and Management Club', 'bmc@invicta.com'],
    ['AFC', 'Aerofabrication Club', 'afc@invicta.com'],
    ['Racing', 'Racing Club', 'racing@invicta.com'],
    ['CAD', 'CAD and 3D Printing Club', 'cad@invicta.com']
];
$stmt = $conn->prepare("INSERT INTO clubs (club_name, description, email, password) VALUES (?, ?, ?, '12345')");
foreach ($clubs as $c) {
    $desc = "Official club for " . $c[1];
    $stmt->bind_param("sss", $c[1], $desc, $c[2]);
    $stmt->execute();
}
echo "<div class='success'>✅ Seeded 7 Clubs</div>";

// B. PARTICIPANTS
$conn->query("INSERT INTO participants (name, email, password, phone, college, department, year) VALUES 
('Rahul Sharma', 'rahul@test.com', '12345', '9876543210', 'IIT Bombay', 'CSE', '3rd'),
('Priya Verma', 'priya@test.com', '12345', '9123456780', 'NIT Trichy', 'ECE', '2nd'),
('Amit Kumar', 'amit@test.com', '12345', '9988776655', 'BITS Pilani', 'MECH', '4th'),
('Sara Ali', 'sara@test.com', '12345', '8877665544', 'VIT', 'CSE', '1st')");
echo "<div class='success'>✅ Seeded Participants</div>";

// C. MENTORS
$conn->query("INSERT INTO mentors (name, email, password, department, phone, designation) VALUES 
('Dr. Anjali Gupta', 'anjali@faculty.edu', '12345', 'CSE', '9876500001', 'Assistant Professor'),
('Prof. Rakesh Roshan', 'rakesh@faculty.edu', '12345', 'MECH', '9876500002', 'HOD')");
echo "<div class='success'>✅ Seeded Mentors</div>";

// D. COORDINATORS
$conn->query("INSERT INTO coordinators (name, email, password, phone) VALUES 
('Rohan Das', 'rohan@coord.com', '12345', '7778889990'),
('Sneha Roy', 'sneha@coord.com', '12345', '6665554440')");
echo "<div class='success'>✅ Seeded Coordinators</div>";

// E. JUDGES
$conn->query("INSERT INTO judges (name, affiliation, expertise, email, phone, password) VALUES 
('Sundar Pichai', 'Google', 'Tech Innovation', 'sundar@google.com', '1010101010', '12345'),
('Elon Musk', 'SpaceX', 'Rocket Science', 'elon@spacex.com', '2020202020', '12345')");
echo "<div class='success'>✅ Seeded Judges</div>";

// F. VOLUNTEERS
$conn->query("INSERT INTO volunteers (name, phone, email, password) VALUES 
('Volunteer 1', '1111111111', 'v1@vol.com', '12345'),
('Volunteer 2', '2222222222', 'v2@vol.com', '12345')");
echo "<div class='success'>✅ Seeded Volunteers</div>";

// G. SPONSORS
$conn->query("INSERT INTO sponsors (organization_name, phone, email) VALUES 
('Red Bull', '1112223333', 'marketing@redbull.com'),
('GitHub', '4445556666', 'community@github.com')");
echo "<div class='success'>✅ Seeded Sponsors</div>";

// H. ROOM TYPES (For Separate Tables)
$conn->query("INSERT INTO room_types (type_name, cost, capacity) VALUES 
('Triple Sharing', 500.00, 3),
('Double AC', 1200.00, 2),
('Single Luxury', 2500.00, 1)");
echo "<div class='success'>✅ Seeded Room Types</div>";

// I. ACCOMMODATION (Using Type IDs 1, 2, 3)
$conn->query("INSERT INTO accommodation (room_number, type_id, current_occupancy) VALUES 
('H-101', 1, 0), ('H-102', 1, 0),
('A-201', 2, 0), ('A-202', 2, 0),
('V-001', 3, 0)");
echo "<div class='success'>✅ Seeded Physical Rooms</div>";

// J. TEAMS & MEMBERS
$conn->query("INSERT INTO teams (tname, leader, mentor) VALUES 
('Code Warriors', 1, 1), ('Mecha Titans', 2, 2)");

$conn->query("INSERT INTO forms (p_id, t_id) VALUES (1, 1), (3, 1), (2, 2), (4, 2)");
echo "<div class='success'>✅ Seeded Teams & Members</div>";

echo "<hr><h2 style='color:#4cd137'>🎉 COMPLETE! Database Reset & Seeded.</h2>";
echo "<a href='index.php' style='display:inline-block; padding:10px 20px; background:#e94560; color:white; text-decoration:none; border-radius:5px;'>Go to Login</a>";
?>
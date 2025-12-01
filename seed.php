<?php
// seed.php - INITIALIZE DATABASE WITH DUMMY DATA (NO EVENTS)
require 'includes/db_connect.php';

// CSS for output
echo "<style>
    body{background:#1a1a2e; color:#fff; font-family:'Segoe UI', sans-serif; padding:40px; line-height:1.6;} 
    .success{color:#4cd137; border-left: 4px solid #4cd137; padding-left: 10px; margin-bottom: 5px;} 
    .error{color:#e84118; border-left: 4px solid #e84118; padding-left: 10px; margin-bottom: 5px;} 
    .info{color:#a2a8d3; font-size: 0.9rem;}
    h2 { border-bottom: 1px solid #533483; padding-bottom: 10px; margin-top: 30px; color: #e94560; }
</style>";

echo "<h1>🌱 Seeding Invicta Database</h1>";

// ======================================================
// 1. CLEANUP (WIPE EVERYTHING)
// ======================================================
echo "<h2>🧹 Cleaning Up Old Data...</h2>";
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

$tables = [
    'registrations', 'event_judges', 'event_volunteers', 'funds', 'prizes', 
    'forms', 'teams', 'events', 'bookings', 'accommodation', 
    'volunteers', 'judges', 'coordinators', 'mentors', 'participants', 'sponsors', 'clubs'
    // Note: 'room_types' is merged into accommodation in your new schema, so no need to drop it if it doesn't exist
];

foreach ($tables as $table) {
    // Check if table exists before truncating to avoid errors
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if($check->num_rows > 0) {
        if ($conn->query("TRUNCATE TABLE $table")) {
            echo "<div class='info'>Cleared: $table</div>";
        } else {
            echo "<div class='error'>Error clearing $table: " . $conn->error . "</div>";
        }
    }
}
$conn->query("SET FOREIGN_KEY_CHECKS = 1");


// ======================================================
// 2. INSERT CORE ENTITIES
// ======================================================
echo "<h2>🚀 Inserting Core Data...</h2>";

// --- A. CLUBS (Your Specific List) ---
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
echo "<div class='success'>✅ Added 7 Clubs (User: club_email / Pass: 12345)</div>";

// --- B. PARTICIPANTS ---
$sql = "INSERT INTO participants (name, email, password, phone, college, department, year) VALUES 
('Rahul Sharma', 'rahul@test.com', '12345', '9876543210', 'IIT Bombay', 'CSE', '3rd'),
('Priya Verma', 'priya@test.com', '12345', '9123456780', 'NIT Trichy', 'ECE', '2nd'),
('Amit Kumar', 'amit@test.com', '12345', '9988776655', 'BITS Pilani', 'MECH', '4th'),
('Sara Ali', 'sara@test.com', '12345', '8877665544', 'VIT', 'CSE', '1st'),
('Vikram Singh', 'vikram@test.com', '12345', '7777777777', 'IIIT Delhi', 'AI', '3rd')";

if($conn->query($sql)) echo "<div class='success'>✅ Added 5 Participants (User: rahul@test.com / Pass: 12345)</div>";
else echo "<div class='error'>❌ Participants Error: " . $conn->error . "</div>";

// --- C. MENTORS ---
$sql = "INSERT INTO mentors (name, email, password, department, phone, designation) VALUES 
('Dr. Anjali Gupta', 'anjali@faculty.edu', '12345', 'CSE', '9876500001', 'Assistant Professor'),
('Prof. Rakesh Roshan', 'rakesh@faculty.edu', '12345', 'MECH', '9876500002', 'HOD')";

if($conn->query($sql)) echo "<div class='success'>✅ Added Mentors</div>";

// --- D. COORDINATORS ---
$sql = "INSERT INTO coordinators (name, email, password, phone) VALUES 
('Rohan Das', 'rohan@coord.com', '12345', '7778889990'),
('Sneha Roy', 'sneha@coord.com', '12345', '6665554440'),
('Arjun Mehta', 'arjun@coord.com', '12345', '5554443330')";

if($conn->query($sql)) echo "<div class='success'>✅ Added Coordinators (User: rohan@coord.com / 12345)</div>";

// --- E. JUDGES ---
$sql = "INSERT INTO judges (name, affiliation, expertise, email, phone, password) VALUES 
('Sundar Pichai', 'Google', 'Tech Innovation', 'sundar@google.com', '1010101010', '12345'),
('Elon Musk', 'SpaceX', 'Rocket Science', 'elon@spacex.com', '2020202020', '12345'),
('Sam Altman', 'OpenAI', 'Artificial Intelligence', 'sam@openai.com', '3030303030', '12345')";

if($conn->query($sql)) echo "<div class='success'>✅ Added Judges (User: sundar@google.com / 12345)</div>";

// --- F. VOLUNTEERS ---
$sql = "INSERT INTO volunteers (name, phone, email, password) VALUES 
('Volunteer 1', '1111111111', 'v1@vol.com', '12345'),
('Volunteer 2', '2222222222', 'v2@vol.com', '12345'),
('Volunteer 3', '3333333333', 'v3@vol.com', '12345')";

if($conn->query($sql)) echo "<div class='success'>✅ Added Volunteers (User: v1@vol.com / 12345)</div>";

// --- G. SPONSORS ---
$sql = "INSERT INTO sponsors (organization_name, phone, email) VALUES 
('Red Bull', '1112223333', 'marketing@redbull.com'),
('GitHub', '4445556666', 'community@github.com'),
('Nvidia', '9999999999', 'gpu@nvidia.com')";

if($conn->query($sql)) echo "<div class='success'>✅ Added Sponsors</div>";


// ======================================================
// 3. INSERT INVENTORY & TEAMS
// ======================================================
echo "<h2>🏨 Setting Up Inventory & Teams...</h2>";

// --- H. ACCOMMODATION (Mixed Type & Inventory) ---
// We create specific rooms with Types and Capacities
$sql = "INSERT INTO accommodation (room_number, room_type, cost, capacity, current_occupancy) VALUES 
('H-101', 'Triple Sharing', 500.00, 3, 0),
('H-102', 'Triple Sharing', 500.00, 3, 0),
('H-103', 'Triple Sharing', 500.00, 3, 0),
('A-201', 'Double AC', 1200.00, 2, 0),
('A-202', 'Double AC', 1200.00, 2, 0),
('V-001', 'Single Luxury', 2500.00, 1, 0)";

if($conn->query($sql)) echo "<div class='success'>✅ Added Accommodation Rooms</div>";

// --- I. TEAMS (Independent of Events) ---
// Team 1: 'Code Warriors' led by Rahul (ID 1)
// Team 2: 'Mecha Titans' led by Priya (ID 2)
$sql = "INSERT INTO teams (tname, leader, mentor) VALUES 
('Code Warriors', 1, 1), 
('Mecha Titans', 2, 2)";

if($conn->query($sql)) echo "<div class='success'>✅ Added Teams</div>";

// --- J. FORMS (Team Members) ---
// Code Warriors: Rahul(1), Amit(3)
// Mecha Titans: Priya(2), Sara(4)
$sql = "INSERT INTO forms (p_id, t_id) VALUES 
(1, 1), (3, 1), 
(2, 2), (4, 2)";

if($conn->query($sql)) echo "<div class='success'>✅ Added Team Members</div>";

// --- K. BOOKINGS (Accommodation) ---
// Rahul(1) books H-101
$sql = "INSERT INTO bookings (participant_id, room_id, checkin_date, checkout_date) VALUES 
(1, 1, '2025-12-10', '2025-12-12')";
// Update Occupancy
$conn->query("UPDATE accommodation SET current_occupancy = 1 WHERE room_id = 1");

if($conn->query($sql)) echo "<div class='success'>✅ Created Booking for Rahul</div>";

echo "<hr><h2 style='color:#4cd137'>🎉 Database Seeded Successfully!</h2>";
echo "<p style='font-size:1.2rem'>You can now log in using any email above with password: <b>12345</b></p>";
echo "<a href='index.php' style='display:inline-block; padding:10px 20px; background:#e94560; color:white; text-decoration:none; border-radius:5px;'>Go to Login</a>";
?>
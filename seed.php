<?php
require 'includes/db.php';

echo "<html><body style='font-family: sans-serif; background: #1a1a2e; color: #fff; padding: 2rem; line-height: 1.6;'>";
echo "<div style='max-width: 800px; margin: 0 auto; background: #16213e; padding: 20px; border-radius: 10px; border: 1px solid #e94560;'>";
echo "<h2 style='color: #e94560; border-bottom: 1px solid #e94560; padding-bottom: 10px;'>🚀 Seeding Invicta Database...</h2>";

// 1. DISABLE FOREIGN KEYS TO ALLOW TRUNCATION
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$tables = [
    'users', 'clubs', 'events', 'accommodation', 
    'teams', 'team_members', 'registrations', 
    'bookings', 'sponsors', 'results'
];

foreach($tables as $t) {
    if($conn->query("TRUNCATE TABLE $t")) {
        echo "<span style='color: #009FFD;'>✔ Cleared table: $t</span><br>";
    } else {
        echo "<span style='color: red;'>✘ Error clearing $t: " . $conn->error . "</span><br>";
    }
}
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "<hr style='border-color: #0f3460;'>";

// 2. COMMON PASSWORD ('12345')
$pass = password_hash("12345", PASSWORD_DEFAULT);

// 3. INSERT CLUBS
$sql_clubs = "INSERT INTO clubs (club_id, club_name) VALUES 
(1, 'CodeGeeks'), 
(2, 'RoboTech'), 
(3, 'Literary Society'), 
(4, 'Gaming Guild')";

if($conn->query($sql_clubs)) echo "✅ Clubs Created<br>";

// 4. INSERT USERS (Participants, Organizers, Judges)
// Organizer & Judge
$sql_users = "INSERT INTO users (user_id, name, email, password, role, phone, college) VALUES 
(1, 'Admin Organizer', 'admin@invicta.com', '$pass', 'organizer', '9999999999', 'IIT Bombay'),
(2, 'Dr. Alan Turing', 'judge@invicta.com', '$pass', 'judge', '8888888888', 'Research Inst')";

// Participants
$sql_users .= ", (3, 'Alice Leader', 'alice@test.com', '$pass', 'participant', '7777777777', 'NIT Trichy')";
$sql_users .= ", (4, 'Bob Member', 'bob@test.com', '$pass', 'participant', '6666666666', 'VIT Vellore')";
$sql_users .= ", (5, 'Charlie Solo', 'charlie@test.com', '$pass', 'participant', '5555555555', 'SRM University')";

if($conn->query($sql_users)) echo "✅ Users Created (Login Password: 12345)<br>";

// 5. INSERT ACCOMMODATION
$sql_accom = "INSERT INTO accommodation (room_type, capacity, cost_per_night, location) VALUES 
('Luxury Single', 1, 1200.00, 'Block A - VIP'),
('Double Shared', 2, 600.00, 'Block B'),
('Dormitory', 10, 200.00, 'Block C')";

if($conn->query($sql_accom)) echo "✅ Accommodation Options Added<br>";

// 6. INSERT EVENTS (Dates set to Dec 2025 for Countdown)
// Added image_path column and values
$sql_events = "INSERT INTO events (event_id, event_name, description, event_date, venue, entry_fee, club_id, status, image_path) VALUES 
(1, 'Hack-a-Thon 2025', 'The ultimate 24-hour coding marathon. Build, Deploy, Win.', '2025-12-10 09:00:00', 'Main Auditorium', 500.00, 1, 'upcoming', 'hackathon.jpg'),
(2, 'RoboWar', 'Build your bot and destroy the competition in the arena.', '2025-12-12 14:00:00', 'Open Ground', 1000.00, 2, 'upcoming', 'robowar.jpg'),
(3, 'Valorant LAN', '5v5 Tactical Shooter Tournament with huge prize pool.', '2025-12-11 10:00:00', 'Computer Lab 3', 250.00, 4, 'upcoming', 'valorant.jpg'),
(4, 'Debate: AI vs Human', 'The future of humanity discussed by top minds.', '2025-12-13 11:00:00', 'Seminar Hall', 100.00, 3, 'live', 'debate.jpg')";

if($conn->query($sql_events)) echo "✅ Events Published with Future Dates & Image Paths<br>";

// 7. INSERT SPONSORS
$sql_sponsors = "INSERT INTO sponsors (name) VALUES ('Google'), ('RedBull'), ('Nvidia'), ('GitHub')";
if($conn->query($sql_sponsors)) echo "✅ Sponsors Added<br>";

// 8. INSERT TEAMS & MEMBERS
// Alice creates a team "Invincibles"
$conn->query("INSERT INTO teams (team_id, team_name, leader_id) VALUES (1, 'Invincibles', 3)");

// Add Alice (Leader) and Bob (Member) to the team
$conn->query("INSERT INTO team_members (team_id, user_id) VALUES (1, 3), (1, 4)");

echo "✅ Team 'Invincibles' created (Leader: Alice, Member: Bob)<br>";

// 9. INSERT REGISTRATIONS
// 'Invincibles' registers for 'Hack-a-Thon'
$conn->query("INSERT INTO registrations (team_id, event_id, payment_status) VALUES (1, 1, 'completed')");

echo "✅ Team Registered for Hack-a-Thon<br>";

echo "<hr style='border-color: #0f3460;'>";
echo "<h3 style='color: #009FFD;'>🎉 Database Fully Seeded!</h3>";
echo "<p><b>Test Credentials (Password: 12345):</b></p>";
echo "<ul style='background: #0f3460; padding: 20px; border-radius: 5px;'>
        <li><b>Participant (Leader):</b> alice@test.com</li>
        <li><b>Participant (Member):</b> bob@test.com</li>
        <li><b>Organizer:</b> admin@invicta.com</li>
        <li><b>Judge:</b> judge@invicta.com</li>
      </ul>";
echo "<a href='auth.php' style='display: inline-block; background: #e94560; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Go to Login</a>";
echo "</div></body></html>";
?>
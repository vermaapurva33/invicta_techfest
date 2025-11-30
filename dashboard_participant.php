<?php 
require 'includes/db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'participant') { header("Location: auth.php"); exit(); }
$uid = $_SESSION['user_id'];

// 1. Create Team
if (isset($_POST['create_team'])) {
    $tname = $_POST['team_name'];
    $stmt = $conn->prepare("INSERT INTO teams (team_name, leader_id) VALUES (?, ?)");
    $stmt->bind_param("si", $tname, $uid);
    if($stmt->execute()) {
        $tid = $stmt->insert_id;
        // Add Leader to Team Members
        $conn->query("INSERT INTO team_members (team_id, user_id) VALUES ($tid, $uid)");
        echo "<script>alert('Team Created Successfully!');</script>";
    }
}

// 2. Add Member to Team
if (isset($_POST['add_member'])) {
    $email = $_POST['email'];
    $tid = $_POST['team_id'];
    
    // Find User
    $u = $conn->query("SELECT user_id FROM users WHERE email='$email' AND role='participant'");
    if($row = $u->fetch_assoc()) {
        $new_uid = $row['user_id'];
        $conn->query("INSERT INTO team_members (team_id, user_id) VALUES ($tid, $new_uid)");
        echo "<script>alert('Member Added!');</script>";
    } else {
        echo "<script>alert('User not found!');</script>";
    }
}

// 3. Register Team for Event
if (isset($_POST['reg_team'])) {
    $tid = $_POST['team_id'];
    $eid = $_POST['event_id'];
    
    // Verify Leader
    $chk = $conn->query("SELECT leader_id FROM teams WHERE team_id=$tid");
    $team = $chk->fetch_assoc();
    
    if($team['leader_id'] == $uid) {
        $stmt = $conn->prepare("INSERT INTO registrations (team_id, event_id, payment_status) VALUES (?, ?, 'completed')");
        $stmt->bind_param("ii", $tid, $eid);
        if($stmt->execute()) echo "<script>alert('Team Registered!');</script>";
        else echo "<script>alert('Already Registered!');</script>";
    } else {
        echo "<script>alert('Only Team Leader can register!');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Dashboard</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
    <div class="sidebar">
        <div class="logo">🚀 INVICTA</div>
        <div class="nav-links">
            <a href="#teams">My Teams</a>
            <a href="#events">Register Event</a>
            <a href="logout.php" style="color:#e94560;">Logout</a>
        </div>
    </div>

    <div class="main-content">
        <h1>Welcome, <?php echo $_SESSION['name']; ?></h1>

        <h3 id="teams" style="margin-top:30px;">Team Management</h3>
        <div class="card" style="padding:20px;">
            <form method="POST" style="display:flex; gap:10px; margin-bottom:20px;">
                <input type="text" name="team_name" class="form-control" placeholder="Create New Team Name" required style="margin-bottom:0;">
                <button type="submit" name="create_team" class="btn" style="width:200px; margin-top:0;">Create Team</button>
            </form>
            
            <table>
                <tr><th>Team Name</th><th>Role</th><th>Members</th><th>Action</th></tr>
                <?php
                // Get teams I am in
                $sql = "SELECT t.team_id, t.team_name, t.leader_id FROM teams t 
                        JOIN team_members tm ON t.team_id = tm.team_id 
                        WHERE tm.user_id = $uid";
                $res = $conn->query($sql);
                while($row = $res->fetch_assoc()) {
                    $isLeader = ($row['leader_id'] == $uid);
                    echo "<tr>
                        <td><b style='color:var(--accent)'>".$row['team_name']."</b></td>
                        <td>".($isLeader ? 'Leader' : 'Member')."</td>
                        <td>";
                        // Get Member Names
                        $mems = $conn->query("SELECT u.name FROM users u JOIN team_members tm ON u.user_id=tm.user_id WHERE tm.team_id=".$row['team_id']);
                        while($m=$mems->fetch_assoc()) echo $m['name'].", ";
                    echo "</td>
                        <td>";
                        if($isLeader) {
                            echo "<form method='POST' style='display:flex; gap:5px;'>
                                <input type='hidden' name='team_id' value='".$row['team_id']."'>
                                <input type='email' name='email' placeholder='Member Email' class='form-control' style='padding:5px; margin:0; height:35px;' required>
                                <button type='submit' name='add_member' class='btn' style='padding:5px; margin:0; width:40px;'>+</button>
                            </form>";
                        }
                    echo "</td></tr>";
                }
                ?>
            </table>
        </div>

        <h3 id="events" style="margin-top:40px;">Register Team for Events</h3>
        <div class="grid-container">
            <?php
            $events = $conn->query("SELECT * FROM events WHERE status='upcoming'");
            while($e = $events->fetch_assoc()): ?>
            <div class="card">
                <div class="card-body">
                    <h4><?php echo $e['event_name']; ?></h4>
                    <p style="color:#a2a8d3;"><?php echo date('M d', strtotime($e['event_date'])); ?></p>
                    <form method="POST" style="margin-top:15px;">
                        <input type="hidden" name="event_id" value="<?php echo $e['event_id']; ?>">
                        <select name="team_id" class="form-control" required>
                            <option value="">Select Your Team</option>
                            <?php
                            $myTeams = $conn->query("SELECT team_id, team_name FROM teams WHERE leader_id=$uid");
                            while($t=$myTeams->fetch_assoc()) echo "<option value='".$t['team_id']."'>".$t['team_name']."</option>";
                            ?>
                        </select>
                        <button type="submit" name="reg_team" class="btn">Confirm Registration</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
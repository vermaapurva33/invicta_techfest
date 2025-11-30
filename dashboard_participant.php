<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'participant') {
    header("Location: index.php");
    exit();
}

$p_id = $_SESSION['user_id'];
$message = "";

if (isset($_POST['create_team'])) {
    $tname = trim($_POST['team_name']);
    
    $stmt = $conn->prepare("INSERT INTO teams (tname, leader) VALUES (?, ?)");
    $stmt->bind_param("si", $tname, $p_id);
    
    if($stmt->execute()) {
        $new_tid = $stmt->insert_id;
        
        $stmt2 = $conn->prepare("INSERT INTO forms (p_id, t_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $p_id, $new_tid);
        $stmt2->execute();
        
        $message = "<script>alert('Team Created Successfully!');</script>";
    } else {
        $message = "<script>alert('Error: Could not create team.');</script>";
    }
}

if (isset($_POST['add_member'])) {
    $email = trim($_POST['email']);
    $tid = $_POST['team_id'];
    
    $stmt = $conn->prepare("SELECT participant_id FROM participants WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()) {
        $new_pid = $row['participant_id'];
        
        $check = $conn->query("SELECT * FROM forms WHERE p_id = $new_pid AND t_id = $tid");
        if($check->num_rows == 0) {
            $stmt2 = $conn->prepare("INSERT INTO forms (p_id, t_id) VALUES (?, ?)");
            $stmt2->bind_param("ii", $new_pid, $tid);
            if($stmt2->execute()){
                $message = "<script>alert('Member Added Successfully!');</script>";
            }
        } else {
            $message = "<script>alert('User is already in this team!');</script>";
        }
    } else {
        $message = "<script>alert('User email not found!');</script>";
    }
}

if (isset($_POST['reg_team'])) {
    $tid = $_POST['team_id'];
    $eid = $_POST['event_id'];
    
    $chk = $conn->query("SELECT leader FROM teams WHERE team_id = $tid");
    $team_data = $chk->fetch_assoc();
    
    if($team_data && $team_data['leader'] == $p_id) {
        $dup = $conn->query("SELECT * FROM registrations WHERE team_id=$tid AND event_id=$eid");
        if($dup->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO registrations (team_id, event_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $tid, $eid);
            if($stmt->execute()) {
                $message = "<script>alert('Team Registered for Event!');</script>";
            }
        } else {
            $message = "<script>alert('Team already registered for this event!');</script>";
        }
    } else {
        $message = "<script>alert('Only the Team Leader can register the team!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Invicta</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { display: block; background: #1a1a2e; }
        .main-content { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .sidebar { display: none; }
        
        .navbar {
            background: #16213e;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #533483;
        }
        
        .card {
            background: #16213e;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #533483;
            margin-bottom: 30px;
        }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #533483; color: #fff; }
        th { background: #0f3460; color: #e94560; }
        
        .btn { padding: 10px 20px; background: #e94560; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #c72c41; }
        .form-control { width: auto; display: inline-block; }
    </style>
</head>
<body>
    
    <?php if($message) echo $message; ?>

    <nav class="navbar">
        <div style="font-size: 1.5rem; font-weight: bold; color: #e94560;">🚀 INVICTA DASHBOARD</div>
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="logout.php" style="background: #e94560; padding: 8px 15px; border-radius: 5px;">Logout</a>
        </div>
    </nav>

    <div class="main-content">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>

        <h2 style="color: #a2a8d3; margin-top: 40px; border-bottom: 1px solid #533483; padding-bottom: 10px;">My Teams</h2>
        <div class="card">
            <form method="POST" style="margin-bottom: 20px; display: flex; gap: 10px;">
                <input type="text" name="team_name" class="form-control" placeholder="New Team Name" required>
                <button type="submit" name="create_team" class="btn">Create Team</button>
            </form>
            
            <table>
                <tr>
                    <th>Team Name</th>
                    <th>My Role</th>
                    <th>Members</th>
                    <th>Add Member</th>
                </tr>
                <?php
                $sql = "SELECT t.team_id, t.tname, t.leader 
                        FROM teams t 
                        JOIN forms f ON t.team_id = f.t_id 
                        WHERE f.p_id = $p_id";
                $res = $conn->query($sql);
                
                if($res->num_rows > 0) {
                    while($row = $res->fetch_assoc()) {
                        $isLeader = ($row['leader'] == $p_id);
                        echo "<tr>";
                        echo "<td><b style='color:#e94560'>" . htmlspecialchars($row['tname']) . "</b></td>";
                        echo "<td>" . ($isLeader ? '👑 Leader' : 'Member') . "</td>";
                        
                        echo "<td>";
                        $mem_sql = "SELECT p.name FROM participants p 
                                    JOIN forms f ON p.participant_id = f.p_id 
                                    WHERE f.t_id = " . $row['team_id'];
                        $mems = $conn->query($mem_sql);
                        while($m = $mems->fetch_assoc()) {
                            echo htmlspecialchars($m['name']) . ", ";
                        }
                        echo "</td>";
                        
                        echo "<td>";
                        if($isLeader) {
                            echo "<form method='POST' style='display:flex; gap:5px;'>
                                    <input type='hidden' name='team_id' value='".$row['team_id']."'>
                                    <input type='email' name='email' placeholder='Student Email' class='form-control' style='padding:5px;' required>
                                    <button type='submit' name='add_member' class='btn' style='padding:5px 10px;'>+</button>
                                </form>";
                        } else {
                            echo "<span style='color:#a2a8d3; font-size:0.9rem;'>Leader Only</span>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>You haven't joined any teams yet.</td></tr>";
                }
                ?>
            </table>
        </div>

        <h2 style="color: #a2a8d3; margin-top: 40px; border-bottom: 1px solid #533483; padding-bottom: 10px;">Register for Events</h2>
        <div class="card">
            <p style="color: #a2a8d3; margin-bottom: 15px;">Select an event and pick which team you want to register.</p>
            
            <form method="POST" style="display: flex; gap: 20px; align-items: flex-end;">
                <div style="flex: 1;">
                    <label style="display:block; color:#a2a8d3; margin-bottom:5px;">Select Event</label>
                    <select name="event_id" class="form-control" style="width:100%;" required>
                        <option value="">-- Choose Event --</option>
                        <?php
                        $evts = $conn->query("SELECT * FROM events");
                        while($e = $evts->fetch_assoc()) {
                            echo "<option value='".$e['event_id']."'>".$e['event_name']." (" . $e['event_date'] . ")</option>";
                        }
                        ?>
                    </select>
                </div>

                <div style="flex: 1;">
                    <label style="display:block; color:#a2a8d3; margin-bottom:5px;">Select Your Team</label>
                    <select name="team_id" class="form-control" style="width:100%;" required>
                        <option value="">-- Choose Team --</option>
                        <?php
                        $myTeams = $conn->query("SELECT team_id, tname FROM teams WHERE leader = $p_id");
                        if($myTeams->num_rows > 0) {
                            while($t = $myTeams->fetch_assoc()) {
                                echo "<option value='".$t['team_id']."'>".$t['tname']."</option>";
                            }
                        } else {
                            echo "<option disabled>You are not leading any teams</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <button type="submit" name="reg_team" class="btn" style="height: 42px;">Confirm Registration</button>
            </form>
        </div>
        
        <h2 style="color: #a2a8d3; margin-top: 40px; border-bottom: 1px solid #533483; padding-bottom: 10px;">Registered Events</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Event Name</th>
                    <th>Team</th>
                    <th>Date</th>
                </tr>
                <?php
                $reg_sql = "SELECT e.event_name, e.event_date, t.tname 
                            FROM registrations r
                            JOIN events e ON r.event_id = e.event_id
                            JOIN teams t ON r.team_id = t.team_id
                            JOIN forms f ON t.team_id = f.t_id
                            WHERE f.p_id = $p_id
                            ORDER BY e.event_date";
                $regs = $conn->query($reg_sql);
                
                if($regs->num_rows > 0) {
                    while($r = $regs->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($r['event_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($r['tname']) . "</td>";
                        echo "<td>" . htmlspecialchars($r['event_date']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No upcoming registrations.</td></tr>";
                }
                ?>
            </table>
        </div>

    </div>
</body>
</html>
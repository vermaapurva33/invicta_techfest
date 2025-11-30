<?php
session_start();
require 'includes/db_connect.php';

// 1. SECURITY: Ensure user is a Judge
// Note: You must add 'judge' to your login_process.php logic for this to work
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'judge') {
    // Temporary fallback: If you haven't set up judge login yet, un-comment next line to test:
    // $_SESSION['user_id'] = 1; $j_id = 1;
    header("Location: index.php");
    exit();
}

$j_id = $_SESSION['user_id'];
$message = "";

// 2. HANDLE SCORE SUBMISSION
if (isset($_POST['update_score'])) {
    $team_id = $_POST['team_id'];
    $event_id = $_POST['event_id'];
    $score = intval($_POST['score']);

    // Update the 'registrations' table
    $stmt = $conn->prepare("UPDATE registrations SET score = ? WHERE team_id = ? AND event_id = ?");
    $stmt->bind_param("iii", $score, $team_id, $event_id);
    
    if ($stmt->execute()) {
        $message = "<script>alert('Score updated successfully!');</script>";
    } else {
        $message = "<script>alert('Error updating score.');</script>";
    }
}

// 3. GET SELECTED EVENT ID (Persist selection after update)
$selected_event_id = isset($_POST['event_id']) ? $_POST['event_id'] : (isset($_GET['event_id']) ? $_GET['event_id'] : 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Judge Panel | Invicta</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { display: block; background: #1a1a2e; }
        .main-content { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
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
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #533483; color: #fff; vertical-align: middle; }
        th { background: #0f3460; color: #e94560; }

        .score-input {
            width: 80px;
            padding: 8px;
            background: #0f3460;
            border: 1px solid #533483;
            color: #fff;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
        }
        
        .btn-update {
            padding: 8px 15px;
            background: #e94560;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-update:hover { background: #c72c41; }

        .event-selector {
            width: 100%;
            padding: 15px;
            background: #16213e;
            color: white;
            border: 1px solid #533483;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
        }
        option { background: #16213e; }
    </style>
</head>
<body>
    
    <?php if($message) echo $message; ?>

    <nav class="navbar">
        <div style="font-size: 1.5rem; font-weight: bold; color: #e94560;">⚖️ JUDGE DASHBOARD</div>
        <div class="nav-links">
            <a href="home.php">View Site</a>
            <a href="logout.php" style="background: #e94560; padding: 8px 15px; border-radius: 5px;">Logout</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
            <p style="color: #a2a8d3;">Select an event below to start grading teams.</p>
        </div>

        <!-- SECTION 1: SELECT EVENT -->
        <div class="card">
            <h3 style="color:#a2a8d3; margin-bottom: 15px;">Select Event to Judge</h3>
            <form method="GET">
                <select name="event_id" class="event-selector" onchange="this.form.submit()">
                    <option value="0">-- Select Assigned Event --</option>
                    <?php
                    // Fetch only events assigned to THIS judge
                    $evt_sql = "SELECT e.event_id, e.event_name, e.event_date 
                                FROM events e 
                                JOIN event_judges ej ON e.event_id = ej.event_id 
                                WHERE ej.judge_id = $j_id";
                    $evts = $conn->query($evt_sql);
                    
                    while($e = $evts->fetch_assoc()) {
                        $selected = ($e['event_id'] == $selected_event_id) ? 'selected' : '';
                        echo "<option value='".$e['event_id']."' $selected>".$e['event_name']." (".$e['event_date'].")</option>";
                    }
                    ?>
                </select>
            </form>
        </div>

        <!-- SECTION 2: GRADING TABLE -->
        <?php if($selected_event_id > 0): ?>
            
            <?php
            // Fetch Event Name for display
            $evt_name_q = $conn->query("SELECT event_name FROM events WHERE event_id = $selected_event_id");
            $evt_name = $evt_name_q->fetch_assoc()['event_name'];
            ?>

            <div class="card">
                <h2 style="color: #e94560; border-bottom: 1px solid #533483; padding-bottom: 10px;">
                    Grading Sheet: <?php echo htmlspecialchars($evt_name); ?>
                </h2>
                
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Team ID</th>
                            <th>Team Name</th>
                            <th>Current Score</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch Teams registered for this event + their current score
                        $team_sql = "SELECT t.team_id, t.tname, r.score 
                                    FROM teams t 
                                    JOIN registrations r ON t.team_id = r.team_id 
                                    WHERE r.event_id = $selected_event_id 
                                    ORDER BY t.tname ASC";
                        $teams = $conn->query($team_sql);
                        
                        if($teams->num_rows > 0) {
                            while($t = $teams->fetch_assoc()):
                            ?>
                            <tr>
                                <td>#<?php echo $t['team_id']; ?></td>
                                <td style="font-weight:bold; font-size:1.1rem;"><?php echo htmlspecialchars($t['tname']); ?></td>
                                
                                <!-- FORM PER ROW to update score individually -->
                                <form method="POST">
                                    <td>
                                        <input type="number" name="score" class="score-input" 
                                            value="<?php echo $t['score'] ? $t['score'] : 0; ?>" 
                                            min="0" max="100" required>
                                        <input type="hidden" name="team_id" value="<?php echo $t['team_id']; ?>">
                                        <input type="hidden" name="event_id" value="<?php echo $selected_event_id; ?>">
                                    </td>
                                    <td>
                                        <button type="submit" name="update_score" class="btn-update">Save Score</button>
                                    </td>
                                </form>
                            </tr>
                            <?php endwhile; 
                        } else {
                            echo "<tr><td colspan='4'>No teams have registered for this event yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
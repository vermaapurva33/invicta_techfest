<?php 
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'includes/db_connect.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'club') {
    header("Location: index.php");
    exit();
}

$my_club_id = $_SESSION['user_id'];
$message = "";

// ====================================================
// LOGIC 1: CREATE EVENT
// ====================================================
if (isset($_POST['add_event'])) {
    
    $name = trim($_POST['name']);
    $coord_id = $_POST['coordinator_id']; 
    $judge_id = $_POST['judge_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $venue = trim($_POST['venue']);
    $desc = trim($_POST['desc']);

    // Coordinator Conflict Check
    $sql_coord = "SELECT event_name FROM events WHERE coordinator_id = ? AND event_date = ? AND ABS(TIMESTAMPDIFF(MINUTE, event_time, ?)) < 120";
    $stmt = $conn->prepare($sql_coord);
    $stmt->bind_param("iss", $coord_id, $date, $time);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $message = "<script>alert('❌ Error: Coordinator is busy at this time!');</script>";
    } 
    // Judge Conflict Check
    elseif (!empty($judge_id)) {
        $sql_judge = "SELECT e.event_name FROM event_judges ej JOIN events e ON ej.event_id = e.event_id WHERE ej.judge_id = ? AND e.event_date = ? AND ABS(TIMESTAMPDIFF(MINUTE, e.event_time, ?)) < 120";
        $stmt = $conn->prepare($sql_judge);
        $stmt->bind_param("iss", $judge_id, $date, $time);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $message = "<script>alert('❌ Error: Judge is busy at this time!');</script>";
        }
    }

    if (empty($message)) {
        // Image Upload
        $image_path = "";
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $target_dir = __DIR__ . "/assets/images/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            if(in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $new_filename = time() . "_" . uniqid() . "." . $file_ext;
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $new_filename)) {
                    $image_path = $new_filename;
                }
            }
        }

        // Insert
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO events (event_name, event_date, event_time, venue, description, club_id, coordinator_id, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssiis", $name, $date, $time, $venue, $desc, $my_club_id, $coord_id, $image_path);
            $stmt->execute();
            $new_event_id = $conn->insert_id; 

            if (!empty($judge_id)) {
                $stmt2 = $conn->prepare("INSERT INTO event_judges (event_id, judge_id) VALUES (?, ?)");
                $stmt2->bind_param("ii", $new_event_id, $judge_id);
                $stmt2->execute();
            }

            $conn->commit();
            $message = "<script>alert('✅ Event Created Successfully!'); window.location.href='dashboard_club.php';</script>";

        } catch (Exception $e) {
            $conn->rollback();
            $message = "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

// ====================================================
// LOGIC 2: ASSIGN VOLUNTEER
// ====================================================
if (isset($_POST['assign_volunteer'])) {
    $event_id = $_POST['event_id'];
    $vol_id = $_POST['volunteer_id'];
    $role = trim($_POST['vol_role']);

    // Get Event Details
    $evt_q = $conn->query("SELECT event_date, event_time, event_name FROM events WHERE event_id = $event_id");
    $target = $evt_q->fetch_assoc();

    // Check Conflict
    $sql_vol = "SELECT e.event_name FROM event_volunteers ev JOIN events e ON ev.event_id = e.event_id WHERE ev.volunteer_id = ? AND e.event_date = ? AND ABS(TIMESTAMPDIFF(MINUTE, e.event_time, ?)) < 120";
    $stmt = $conn->prepare($sql_vol);
    $stmt->bind_param("iss", $vol_id, $target['event_date'], $target['event_time']);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $message = "<script>alert('❌ CLASH: Volunteer is busy!');</script>";
    } else {
        $stmt2 = $conn->prepare("INSERT INTO event_volunteers (event_id, volunteer_id, assigned_role) VALUES (?, ?, ?)");
        $stmt2->bind_param("iis", $event_id, $vol_id, $role);
        if($stmt2->execute()) $message = "<script>alert('✅ Volunteer Assigned!');</script>";
        else $message = "<script>alert('⚠️ Already assigned.');</script>";
    }
}

// ====================================================
// LOGIC 3: SECURE SPONSORSHIP (NEW)
// ====================================================
if (isset($_POST['add_sponsor'])) {
    $event_id = $_POST['event_id'];
    $sponsor_id = $_POST['sponsor_id'];
    $type = trim($_POST['sponsor_type']); // e.g. Cash, Goodies
    $amount = $_POST['amount'];
    $date = date('Y-m-d'); // Today's date

    $stmt = $conn->prepare("INSERT INTO funds (sponsor_id, event_id, sponsorship_date, sponsorship_type, amount_value) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $sponsor_id, $event_id, $date, $type, $amount);
    
    if($stmt->execute()) {
        $message = "<script>alert('💰 Sponsorship Added Successfully!');</script>";
    } else {
        $message = "<script>alert('Error adding sponsor: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Club Admin | Invicta</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { display: block; background: #1a1a2e; }
        .main-content { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .navbar { background: #16213e; padding: 15px 30px; display: flex; justify-content: space-between; border-bottom: 2px solid #533483; }
        .card { background: #16213e; padding: 30px; border-radius: 12px; border: 1px solid #533483; margin-bottom: 40px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #533483; color: #fff; vertical-align: middle; }
        th { background: #0f3460; color: #e94560; }

        .form-control { width: 100%; margin-bottom: 15px; }
        .btn { width: auto; padding: 12px 25px; background: #e94560; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }
        input[type="file"] { background: #0f3460; padding: 10px; border: 1px solid #533483; border-radius: 5px; color: #a2a8d3; }
        h3 { margin-bottom: 20px; color: #e94560; }
        
        /* Sponsor Badge */
        .sponsor-tag { display: inline-block; background: #0f3460; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; margin-right: 5px; margin-bottom: 5px; border: 1px solid #4cd137; color: #4cd137; }
    </style>
</head>
<body>
    
    <?php if($message) echo $message; ?>

    <nav class="navbar">
        <div style="font-size: 1.5rem; font-weight: bold; color: #e94560;">⚡ CLUB DASHBOARD</div>
        <div class="nav-links">
            <a href="home.php">View Site</a>
            <a href="logout.php" style="background: #e94560; padding: 8px 15px; border-radius: 5px;">Logout</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
        </div>

        <!-- 1. CREATE EVENT -->
        <div class="card">
            <h3>Create New Event</h3>
            <form method="POST" class="grid-form" enctype="multipart/form-data">
                <div class="full-width">
                    <label style="color:#a2a8d3">Event Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <!-- Coordinators -->
                <div>
                    <label style="color:#a2a8d3">Assign Coordinator</label>
                    <select name="coordinator_id" class="form-control" required>
                        <option value="">-- Select Student Head --</option>
                        <?php
                        $coords = $conn->query("SELECT coordinator_id, name FROM coordinators");
                        while($c = $coords->fetch_assoc()) echo "<option value='".$c['coordinator_id']."'>".$c['name']."</option>";
                        ?>
                    </select>
                </div>

                <!-- Judges -->
                <div>
                    <label style="color:#a2a8d3">Assign Judge</label>
                    <select name="judge_id" class="form-control">
                        <option value="">-- Select Judge --</option>
                        <?php
                        $judges = $conn->query("SELECT judge_id, name FROM judges");
                        while($j = $judges->fetch_assoc()) echo "<option value='".$j['judge_id']."'>".$j['name']."</option>";
                        ?>
                    </select>
                </div>

                <div>
                    <label style="color:#a2a8d3">Date</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div>
                    <label style="color:#a2a8d3">Time</label>
                    <input type="time" name="time" class="form-control" required>
                </div>

                <div class="full-width">
                    <label style="color:#a2a8d3">Venue</label>
                    <input type="text" name="venue" class="form-control" required>
                </div>

                <div class="full-width">
                    <label style="color:#a2a8d3">Event Banner</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                
                <div class="full-width">
                    <label style="color:#a2a8d3">Description</label>
                    <textarea name="desc" class="form-control" style="height: 100px;"></textarea>
                </div>
                
                <div class="full-width">
                    <button type="submit" name="add_event" class="btn">Create Event</button>
                </div>
            </form>
        </div>

        <!-- 2. SECURE SPONSORSHIP (NEW) -->
        <div class="card">
            <h3>Add Sponsor to Event</h3>
            <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 15px; align-items: flex-end;">
                
                <div>
                    <label style="color:#a2a8d3">Select Event</label>
                    <select name="event_id" class="form-control" required>
                        <?php
                        $my_evts = $conn->query("SELECT event_id, event_name FROM events WHERE club_id = $my_club_id");
                        while($e = $my_evts->fetch_assoc()) echo "<option value='".$e['event_id']."'>".$e['event_name']."</option>";
                        ?>
                    </select>
                </div>

                <div>
                    <label style="color:#a2a8d3">Select Sponsor</label>
                    <select name="sponsor_id" class="form-control" required>
                        <?php
                        $spons = $conn->query("SELECT sponsor_id, organization_name FROM sponsors");
                        while($s = $spons->fetch_assoc()) echo "<option value='".$s['sponsor_id']."'>".$s['organization_name']."</option>";
                        ?>
                    </select>
                </div>

                <div>
                    <label style="color:#a2a8d3">Type</label>
                    <input type="text" name="sponsor_type" class="form-control" placeholder="Ex: Cash, Merchandise" required>
                </div>

                <div>
                    <label style="color:#a2a8d3">Value (₹)</label>
                    <input type="number" name="amount" class="form-control" placeholder="Ex: 5000" required>
                </div>

                <button type="submit" name="add_sponsor" class="btn" style="margin-bottom: 15px; background: #4cd137;">Add</button>
            </form>
        </div>

        <!-- 3. ASSIGN VOLUNTEERS -->
        <div class="card">
            <h3>Assign Volunteers</h3>
            <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px; align-items: flex-end;">
                <div>
                    <label style="color:#a2a8d3">Select Event</label>
                    <select name="event_id" class="form-control" required>
                        <?php
                        $my_evts->data_seek(0); // Reset pointer
                        while($e = $my_evts->fetch_assoc()) echo "<option value='".$e['event_id']."'>".$e['event_name']."</option>";
                        ?>
                    </select>
                </div>

                <div>
                    <label style="color:#a2a8d3">Select Volunteer</label>
                    <select name="volunteer_id" class="form-control" required>
                        <?php
                        $vols = $conn->query("SELECT volunteer_id, name FROM volunteers");
                        while($v = $vols->fetch_assoc()) echo "<option value='".$v['volunteer_id']."'>".$v['name']."</option>";
                        ?>
                    </select>
                </div>

                <div>
                    <label style="color:#a2a8d3">Role</label>
                    <input type="text" name="vol_role" class="form-control" placeholder="Ex: Logistics" required>
                </div>

                <button type="submit" name="assign_volunteer" class="btn" style="margin-bottom: 15px;">Assign</button>
            </form>
        </div>

        <!-- 4. EVENT LIST & SPONSORS -->
        <h3>My Events Overview</h3>
        <table>
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Coordinator</th>
                    <th>Date & Time</th>
                    <th>Sponsors & Funding</th> <!-- New Column -->
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT e.*, c.name as coord_name 
                        FROM events e 
                        LEFT JOIN coordinators c ON e.coordinator_id = c.coordinator_id 
                        WHERE e.club_id = $my_club_id 
                        ORDER BY e.event_date DESC";
                $res = $conn->query($sql);
                
                if($res->num_rows > 0) {
                    while($row = $res->fetch_assoc()): 
                        // Fetch Sponsors for this event
                        $spon_sql = "SELECT s.organization_name, f.sponsorship_type, f.amount_value 
                                     FROM funds f 
                                     JOIN sponsors s ON f.sponsor_id = s.sponsor_id 
                                     WHERE f.event_id = " . $row['event_id'];
                        $spon_res = $conn->query($spon_sql);
                    ?>
                    <tr>
                        <td><b style="color: #e94560;"><?php echo htmlspecialchars($row['event_name']); ?></b></td>
                        <td><?php echo htmlspecialchars($row['coord_name']); ?></td>
                        <td>
                            <?php echo date("M d", strtotime($row['event_date'])); ?> <br>
                            <small style="color:#4cd137"><?php echo date("h:i A", strtotime($row['event_time'])); ?></small>
                        </td>
                        <td>
                            <?php 
                            if ($spon_res->num_rows > 0) {
                                while($sp = $spon_res->fetch_assoc()) {
                                    echo "<div class='sponsor-tag'>" . htmlspecialchars($sp['organization_name']) . 
                                         " (" . $sp['sponsorship_type'] . ": ₹" . number_format($sp['amount_value']) . ")</div>";
                                }
                            } else {
                                echo "<span style='color:#666'>No sponsors yet</span>";
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endwhile; 
                } else {
                    echo "<tr><td colspan='4'>No events found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
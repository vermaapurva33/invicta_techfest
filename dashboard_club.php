<?php 
session_start();
require 'includes/db_connect.php';

// 1. SECURITY: Ensure user is a Club Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'club') {
    header("Location: index.php");
    exit();
}

$my_club_id = $_SESSION['user_id'];
$message = "";

// 2. HANDLE ADD EVENT
if (isset($_POST['add_event'])) {
    $name = trim($_POST['name']);
    $coord_id = $_POST['coordinator_id']; // Assign a student coordinator
    $date = $_POST['date'];
    $venue = trim($_POST['venue']);
    $desc = trim($_POST['desc']);

    // --- IMAGE UPLOAD ---
    $image_path = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "assets/images/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = time() . "_" . uniqid() . "." . $file_ext;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $new_filename)) {
            $image_path = $new_filename;
        }
    }

    // --- INSERT (Club ID is automatic) ---
    $stmt = $conn->prepare("INSERT INTO events (event_name, event_date, venue, description, club_id, coordinator_id, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiis", $name, $date, $venue, $desc, $my_club_id, $coord_id, $image_path);
    
    if($stmt->execute()) {
        $message = "<script>alert('Event Created Successfully!');</script>";
    } else {
        $message = "<script>alert('Error: " . $conn->error . "');</script>";
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
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #533483;
            margin-bottom: 40px;
        }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #533483; color: #fff; vertical-align: middle; }
        th { background: #0f3460; color: #e94560; }

        .form-control { width: 100%; margin-bottom: 15px; }
        .btn { width: auto; padding: 12px 25px; background: #e94560; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #c72c41; }
        
        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }

        input[type="file"] {
            background: #0f3460;
            padding: 10px;
            border: 1px solid #533483;
            border-radius: 5px;
            color: #a2a8d3;
        }
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
            <!-- Session Name will display the Club Name (e.g., "Coding Club") -->
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
            <p style="color: #a2a8d3;">Create and manage your club's events</p>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 20px; color: #e94560;">Create New Event</h3>
            
            <form method="POST" class="grid-form" enctype="multipart/form-data">
                
                <div class="full-width">
                    <label style="color:#a2a8d3">Event Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Ex: Hackathon 2025" required>
                </div>
                
                <!-- SELECT COORDINATOR -->
                <div>
                    <label style="color:#a2a8d3">Assign Coordinator</label>
                    <select name="coordinator_id" class="form-control" required>
                        <option value="">-- Select Student Head --</option>
                        <?php
                        $coords = $conn->query("SELECT coordinator_id, name FROM coordinators");
                        while($c = $coords->fetch_assoc()) {
                            echo "<option value='".$c['coordinator_id']."'>".$c['name']."</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label style="color:#a2a8d3">Event Date</label>
                    <input type="date" name="date" class="form-control" required>
                </div>

                <div>
                    <label style="color:#a2a8d3">Venue</label>
                    <input type="text" name="venue" class="form-control" placeholder="Ex: Lab 3, Block B" required>
                </div>

                <div>
                    <label style="color:#a2a8d3">Event Banner</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                
                <div class="full-width">
                    <label style="color:#a2a8d3">Description</label>
                    <textarea name="desc" class="form-control" placeholder="Event details..." style="height: 100px;"></textarea>
                </div>
                
                <div class="full-width">
                    <button type="submit" name="add_event" class="btn">Create Event</button>
                </div>
            </form>
        </div>

        <h3 style="color: #a2a8d3; border-bottom: 1px solid #533483; padding-bottom: 10px;">Events by <?php echo htmlspecialchars($_SESSION['name']); ?></h3>
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Event Name</th>
                    <th>Coordinator</th>
                    <th>Date</th>
                    <th>Venue</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Show events belonging to THIS CLUB
                $sql = "SELECT e.*, coord.name as coord_name 
                        FROM events e 
                        LEFT JOIN coordinators coord ON e.coordinator_id = coord.coordinator_id 
                        WHERE e.club_id = $my_club_id 
                        ORDER BY e.event_date DESC";
                $res = $conn->query($sql);
                
                if($res->num_rows > 0) {
                    while($row = $res->fetch_assoc()):
                        $img_src = "assets/images/" . $row['image_path'];
                        if (empty($row['image_path']) || !file_exists($img_src)) $img_src = "";
                    ?>
                    <tr>
                        <td>
                            <?php if($img_src): ?>
                                <img src="<?php echo $img_src; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <span style="color:#a2a8d3; font-size: 0.8rem;">No Img</span>
                            <?php endif; ?>
                        </td>
                        <td><b style="color: #e94560;"><?php echo htmlspecialchars($row['event_name']); ?></b></td>
                        <td><?php echo htmlspecialchars($row['coord_name']); ?></td>
                        <td><?php echo date("M d, Y", strtotime($row['event_date'])); ?></td>
                        <td><?php echo htmlspecialchars($row['venue']); ?></td>
                    </tr>
                    <?php endwhile; 
                } else {
                    echo "<tr><td colspan='5'>No events created yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
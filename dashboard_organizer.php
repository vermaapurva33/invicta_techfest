<?php 
require 'includes/db.php';
// Role Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header("Location: auth.php"); exit();
}

// HANDLE ADD EVENT
if (isset($_POST['add_event'])) {
    $name = $_POST['name'];
    $club_id = $_POST['club_id'];
    $date = $_POST['date'];
    $venue = $_POST['venue'];
    $fee = $_POST['fee'];
    $desc = $_POST['desc'];

    // Handle Image Upload
    $imgName = "";
    if(!empty($_FILES["image"]["name"])) {
        $targetDir = "assets/images/";
        $imgName = time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $targetDir . $imgName);
    }

    $stmt = $conn->prepare("INSERT INTO events (event_name, description, event_date, venue, entry_fee, image_path, club_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'upcoming')");
    $stmt->bind_param("ssssdsi", $name, $desc, $date, $venue, $fee, $imgName, $club_id);
    
    if($stmt->execute()) {
        echo "<script>alert('Event Published Successfully!');</script>";
    } else {
        echo "<script>alert('Error creating event.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Organizer Panel</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">⚡ Organizer</div>
        <div class="nav-links">
            <a href="#" class="active">Manage Events</a>
            <a href="index.php">View Public Site</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Organizer Dashboard</h1>
            <p>Welcome, <?php echo $_SESSION['name']; ?></p>
        </div>

        <div class="card" style="padding: 30px; margin-bottom: 40px;">
            <h3 style="margin-bottom: 20px;">Create New Event</h3>
            <form method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                
                <input type="text" name="name" class="form-control" placeholder="Event Name" required>
                
                <select name="club_id" class="form-control" required>
                    <option value="">Select Club</option>
                    <?php
                    $clubs = $conn->query("SELECT * FROM clubs");
                    while($c = $clubs->fetch_assoc()) {
                        echo "<option value='".$c['club_id']."'>".$c['club_name']."</option>";
                    }
                    ?>
                </select>

                <input type="date" name="date" class="form-control" required>
                <input type="text" name="venue" class="form-control" placeholder="Venue Location" required>
                <input type="number" name="fee" class="form-control" placeholder="Entry Fee (₹)" required>
                <input type="file" name="image" class="form-control" required>
                
                <textarea name="desc" class="form-control" placeholder="Event Description" style="grid-column: span 2; height: 100px;"></textarea>
                
                <button type="submit" name="add_event" class="btn">Publish Event</button>
            </form>
        </div>

        <h3>Published Events</h3>
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Club</th>
                    <th>Date</th>
                    <th>Fee</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT e.*, c.club_name FROM events e LEFT JOIN clubs c ON e.club_id = c.club_id ORDER BY e.event_date DESC";
                $res = $conn->query($sql);
                while($row = $res->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['club_name']); ?></td>
                    <td><?php echo $row['event_date']; ?></td>
                    <td>₹<?php echo $row['entry_fee']; ?></td>
                    <td><span class="badge"><?php echo $row['status']; ?></span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
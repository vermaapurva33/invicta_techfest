<?php 
require 'includes/db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'judge') {
    header("Location: auth.php"); exit();
}

// SUBMIT RESULTS
if (isset($_POST['submit_result'])) {
    $event_id = $_POST['event_id'];
    $winner_id = $_POST['winner_id'];
    $rank = $_POST['rank'];
    $comments = $_POST['comments'];

    $stmt = $conn->prepare("INSERT INTO results (event_id, winner_user_id, rank, comments) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $event_id, $winner_id, $rank, $comments);
    
    if($stmt->execute()) {
        echo "<script>alert('Result Submitted Successfully!');</script>";
    } else {
        echo "<script>alert('Error submitting result.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Judge Panel</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">⚡ Judge Panel</div>
        <div class="nav-links">
            <a href="#" class="active">Evaluate</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="main-content">
        <h1>Evaluation Dashboard</h1>

        <div class="card" style="padding: 30px; max-width: 600px;">
            <h3>Submit Event Results</h3>
            
            <form method="POST">
                
                <label style="display:block; margin-top:15px; font-weight:bold;">Select Event</label>
                <select name="event_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Select Event --</option>
                    <?php
                    $events = $conn->query("SELECT * FROM events");
                    while($e = $events->fetch_assoc()) {
                        $sel = (isset($_POST['event_id']) && $_POST['event_id'] == $e['event_id']) ? 'selected' : '';
                        echo "<option value='".$e['event_id']."' $sel>".$e['event_name']."</option>";
                    }
                    ?>
                </select>

                </form>

            <?php if(isset($_POST['event_id']) && !empty($_POST['event_id'])): ?>
            
            <form method="POST">
                <input type="hidden" name="event_id" value="<?php echo $_POST['event_id']; ?>">
                
                <label style="display:block; margin-top:15px; font-weight:bold;">Select Winner</label>
                <select name="winner_id" class="form-control" required>
                    <option value="">-- Select Participant --</option>
                    <?php
                    // Fetch only participants registered for this specific event
                    $eid = $_POST['event_id'];
                    $parts = $conn->query("SELECT u.user_id, u.name FROM users u JOIN registrations r ON u.user_id = r.user_id WHERE r.event_id = $eid");
                    
                    if($parts->num_rows > 0) {
                        while($p = $parts->fetch_assoc()) {
                            echo "<option value='".$p['user_id']."'>".$p['name']." (ID: ".$p['user_id'].")</option>";
                        }
                    } else {
                        echo "<option disabled>No participants registered yet</option>";
                    }
                    ?>
                </select>

                <label style="display:block; margin-top:15px; font-weight:bold;">Rank</label>
                <input type="number" name="rank" class="form-control" placeholder="Enter Rank (e.g. 1)" required>

                <label style="display:block; margin-top:15px; font-weight:bold;">Comments</label>
                <textarea name="comments" class="form-control" placeholder="Judge's Remarks"></textarea>

                <button type="submit" name="submit_result" class="btn" style="margin-top: 15px;">Submit Score</button>
            </form>
            
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
session_start();
require 'includes/db_connect.php';

// 1. SECURITY: Only Participants can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'participant') {
    header("Location: index.php");
    exit();
}

$p_id = $_SESSION['user_id'];
$message = "";

// 2. CHECK EXISTING BOOKING
// We join with 'accommodation' to get room details like 'room_type'
$sql_check = "SELECT b.*, a.room_type, a.cost 
              FROM bookings b 
              JOIN accommodation a ON b.room_id = a.room_id 
              WHERE b.participant_id = $p_id";
$res_check = $conn->query($sql_check);
$existing_booking = $res_check->fetch_assoc();

// 3. FETCH AVAILABLE ROOMS (For the form)
$rooms_sql = "SELECT * FROM accommodation";
$rooms_res = $conn->query($rooms_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Accommodation | Invicta</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #1a1a2e; display: block; }
        .main-content { max-width: 800px; margin: 40px auto; padding: 20px; }
        
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
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        /* Success Ticket Style */
        .ticket {
            border-left: 5px solid #4cd137;
            background: rgba(76, 209, 55, 0.1);
        }
        .ticket h2 { color: #4cd137; margin-top: 0; }
        .ticket-info { font-size: 1.1rem; line-height: 1.8; color: #fff; }
        .ticket-label { color: #a2a8d3; font-weight: bold; }

        /* Form Styles */
        .form-group { margin-bottom: 20px; }
        .form-control { width: 100%; padding: 12px; background: #0f3460; border: 1px solid #533483; color: white; border-radius: 5px; }
        .btn { width: 100%; padding: 15px; background: #e94560; color: white; border: none; font-size: 1.1rem; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #c72c41; }
        
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div style="font-size: 1.5rem; font-weight: bold; color: #e94560;">🏠 ACCOMMODATION</div>
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="dashboard_participant.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="main-content">

        <!-- SCENARIO A: ALREADY BOOKED -->
        <?php if ($existing_booking): ?>
            
            <div class="card ticket">
                <h2>✅ Booking Confirmed</h2>
                <p style="color: #a2a8d3; border-bottom: 1px solid #533483; padding-bottom: 10px;">
                    You have successfully secured accommodation.
                </p>
                
                <div class="ticket-info">
                    <span class="ticket-label">Room Type:</span> <?php echo htmlspecialchars($existing_booking['room_type']); ?><br>
                    <span class="ticket-label">Cost per Night:</span> ₹<?php echo $existing_booking['cost']; ?><br>
                    <span class="ticket-label">Check-in:</span> <?php echo date("F d, Y", strtotime($existing_booking['checkin_date'])); ?><br>
                    <span class="ticket-label">Check-out:</span> <?php echo date("F d, Y", strtotime($existing_booking['checkout_date'])); ?><br>
                    <span class="ticket-label">Booking ID:</span> #INV-ACC-<?php echo $existing_booking['booking_id']; ?>
                </div>

                <button class="btn" style="margin-top: 20px; background: #533483;" onclick="window.print()">Print Ticket</button>
            </div>

        <!-- SCENARIO B: NOT BOOKED YET -->
        <?php else: ?>

            <div class="card">
                <h2 style="color: #e94560; margin-top: 0;">Book Your Stay</h2>
                <p style="color: #a2a8d3;">Select a room type and dates to confirm your lodging.</p>
                
                <form action="book_room.php" method="POST">
                    
                    <div class="form-group">
                        <label style="color:#a2a8d3;">Select Room Type</label>
                        <select name="room_id" class="form-control" required>
                            <option value="">-- Choose Room --</option>
                            <?php while($r = $rooms_res->fetch_assoc()): ?>
                                <option value="<?php echo $r['room_id']; ?>">
                                    <?php echo $r['room_type']; ?> - ₹<?php echo $r['cost']; ?> / night
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label style="color:#a2a8d3;">Check-in Date</label>
                            <input type="date" name="checkin" class="form-control" min="2025-12-10" required>
                        </div>
                        <div class="form-group">
                            <label style="color:#a2a8d3;">Check-out Date</label>
                            <input type="date" name="checkout" class="form-control" min="2025-12-10" required>
                        </div>
                    </div>

                    <button type="submit" name="book_room" class="btn">Confirm Booking</button>
                </form>
            </div>

        <?php endif; ?>

    </div>

</body>
</html>
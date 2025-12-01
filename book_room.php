<?php
session_start();
require 'includes/db_connect.php';

// 1. SECURITY
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'participant') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['book_room'])) {
    $p_id = $_SESSION['user_id'];
    $room_id = $_POST['room_id'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];

    // 2. DOUBLE CHECK: Does user already have a booking?
    // (Prevents resubmission hacks)
    $check_sql = "SELECT booking_id FROM bookings WHERE participant_id = $p_id";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        // Already booked
        echo "<script>
            alert('⚠️ You have already booked a room! Multiple bookings are not allowed.');
            window.location.href='accommodation.php';
        </script>";
        exit();
    }

    // 3. VALIDATION: Check-out must be after Check-in
    if (strtotime($checkout) <= strtotime($checkin)) {
        echo "<script>
            alert('❌ Error: Check-out date must be after Check-in date.');
            window.location.href='accommodation.php';
        </script>";
        exit();
    }

    // 4. INSERT BOOKING
    $stmt = $conn->prepare("INSERT INTO bookings (participant_id, room_id, checkin_date, checkout_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $p_id, $room_id, $checkin, $checkout);

    if ($stmt->execute()) {
        echo "<script>
            alert('✅ Room Booked Successfully!');
            window.location.href='accommodation.php';
        </script>";
    } else {
        echo "<script>
            alert('Error: " . $conn->error . "');
            window.location.href='accommodation.php';
        </script>";
    }
} else {
    header("Location: accommodation.php");
}
?>
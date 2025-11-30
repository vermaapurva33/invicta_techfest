<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'includes/db_connect.php';

// 1. Check if button clicked
if (isset($_POST['btn_register'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $college = trim($_POST['college']);
    $dept = trim($_POST['dept']);
    $year = trim($_POST['year']);

    // 2. Check for Duplicate Email
    $checkStmt = $conn->prepare("SELECT email FROM participants WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo "<script>
                alert('This email is already registered! Please Login.');
                window.location.href='index.php';
              </script>";
    } else {
        // 3. Insert New User
        $insertStmt = $conn->prepare("INSERT INTO participants (name, email, password, phone, college, department, year) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertStmt->bind_param("sssssss", $name, $email, $password, $phone, $college, $dept, $year);

        if ($insertStmt->execute()) {
            
            // --- FIX: DESTROY SESSION TO PREVENT AUTO-LOGIN ---
            // This clears any old login data so the user MUST log in again
            session_unset();
            session_destroy();
            
            echo "<script>
                    alert('Registration Successful! Please Login.');
                    window.location.href='index.php';
                  </script>";
        } else {
            echo "Error: " . $conn->error;
        }
        $insertStmt->close();
    }

    $checkStmt->close();
    $conn->close();

} else {
    header("Location: register.php");
    exit();
}
?>
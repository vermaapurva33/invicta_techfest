<?php
session_start();
require 'includes/db_connect.php';

if (isset($_POST['btn_login'])) {
    $role = $_POST['role'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    
    $table = "";
    $id_column = "";
    
    switch ($role) {
        case 'participant':
            $table = "participants";
            $id_column = "participant_id";
            break;
        case 'coordinator':
            $table = "coordinators";
            $id_column = "coordinator_id";
            break;
        case 'mentor':
            $table = "mentors";
            $id_column = "mentor_id";
            break;
        case 'club':
            $table = "clubs";
            $id_column = "club_id";
            break;
        default:
            die("Invalid Role Selected");
    }

    
    $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // 3. Verify Password
        // Note: For now we compare directly. In production, use: password_verify($password, $row['password'])
        if ($password === $row['password']) {
            
            // --- LOGIN SUCCESS ---
            $_SESSION['user_id'] = $row[$id_column];
            $_SESSION['role'] = $role;
            
            // Handle different name columns (Clubs have 'club_name', others have 'name')
            $_SESSION['name'] = ($role == 'club') ? $row['club_name'] : $row['name'];

            // Redirect to the Dashboard based on role
            // Example: dashboard_participant.php
            header("Location: dashboard_" . $role . ".php");
            exit();

        } else {
            echo "<script>alert('Incorrect Password!'); window.location='index.php';</script>";
        }
    } else {
        echo "<script>alert('User not found. Please register first.'); window.location='register.php';</script>";
    }
}
?>
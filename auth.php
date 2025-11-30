<?php require 'includes/db.php'; 

// LOGIN
if(isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $role = $_POST['role'];
    
    $stmt = $conn->prepare("SELECT user_id, name, password, role FROM users WHERE email=? AND role=?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if($row = $res->fetch_assoc()) {
        if(password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = $row['role'];
            header("Location: dashboard_".$row['role'].".php");
            exit();
        } else echo "<script>alert('Invalid Password');</script>";
    } else echo "<script>alert('User Not Found');</script>";
}

// REGISTER (Participants)
if(isset($_POST['register'])) {
    $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, 'participant', ?)");
    $stmt->bind_param("ssss", $_POST['name'], $_POST['email'], $pass, $_POST['phone']);
    if($stmt->execute()) echo "<script>alert('Registered! Please Login.');</script>";
    else echo "<script>alert('Email Exists');</script>";
}
?>

<!DOCTYPE html>
<html>
<head><title>Login Invicta</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body style="align-items:center; justify-content:center;">
    <div class="card" style="width:400px; padding:30px;">
        <h2 style="text-align:center; margin-bottom:20px; color:var(--light);">INVICTA LOGIN</h2>
        
        <form method="POST" id="loginForm">
            <select name="role" class="form-control">
                <option value="participant">Participant</option>
                <option value="organizer">Organizer</option>
                <option value="judge">Judge</option>
            </select>
            <input type="email" name="email" class="form-control" placeholder="Email" required>
            <input type="password" name="pass" class="form-control" placeholder="Password" required>
            <button type="submit" name="login" class="btn">Login</button>
            <p style="text-align:center; margin-top:10px; cursor:pointer;" onclick="toggle()">New User? Register</p>
        </form>

        <form method="POST" id="regForm" style="display:none;">
            <input type="text" name="name" class="form-control" placeholder="Full Name" required>
            <input type="email" name="email" class="form-control" placeholder="Email" required>
            <input type="text" name="phone" class="form-control" placeholder="Phone" required>
            <input type="password" name="pass" class="form-control" placeholder="Password" required>
            <button type="submit" name="register" class="btn">Register</button>
            <p style="text-align:center; margin-top:10px; cursor:pointer;" onclick="toggle()">Back to Login</p>
        </form>
    </div>
    <script>
        function toggle() {
            var l = document.getElementById('loginForm');
            var r = document.getElementById('regForm');
            if(l.style.display==='none'){l.style.display='block';r.style.display='none';}
            else{l.style.display='none';r.style.display='block';}
        }
    </script>
</body>
</html>
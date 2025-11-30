<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Invicta 2025</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="auth-container">
        <div class="auth-card">
            <h1>Create Account</h1>
            <p>Join the Techfest Revolution</p>
            
            <form action="register_process.php" method="POST" class="register-form">
                
                <div class="form-group full-width">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>College Name</label>
                    <input type="text" name="college" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" name="dept" class="form-control" placeholder="Ex: CSE" required>
                </div>

                <div class="form-group">
                    <label>Year</label>
                    <input type="text" name="year" class="form-control" placeholder="Ex: 3rd" required>
                </div>

                <button type="submit" name="btn_register" class="btn-submit full-width">Sign Up</button>
            </form>

            <p class="login-link">
                Already have an account? <a href="index.php">Login here</a>
            </p>
        </div>
    </div>

</body>
</html>
</body>
</html>
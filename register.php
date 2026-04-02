<?php
session_start();
require_once 'php/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $check_email = "SELECT id FROM users WHERE email = '$email'";
    if (mysqli_num_rows(mysqli_query($conn, $check_email)) > 0) {
        $error = "Email already exists";
    } else {
        $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
        if (mysqli_query($conn, $sql)) {
            $success = "Registration successful. <a href='login.php' style='color: inherit; font-weight: 800;'>Login here</a>";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Campus Cravings</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 450px;
            padding: 40px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border: 1px solid var(--border-light);
        }
        .login-card h2 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 30px;
            color: var(--text-main);
            text-align: center;
        }
        .form-group { margin-bottom: 25px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 1px solid var(--border-light);
            border-radius: 12px;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--swiggy-orange);
            box-shadow: 0 0 0 4px rgba(252, 128, 25, 0.1);
        }
        .error-msg {
            background: #fff5f5;
            color: #ff4757;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 600;
            text-align: center;
            border: 1px solid #ffebeb;
        }
        .success-msg {
            background: #f0fff4;
            color: #38a169;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 600;
            text-align: center;
            border: 1px solid #c6f6d5;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo-desktop" style="justify-content: center; margin-bottom: 40px; font-size: 2rem;">
                🍴 Campus<span>Cravings</span>
            </div>
            <h2>Register</h2>
            <?php if ($error): ?>
                <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-msg"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>
            <form action="register.php" method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required placeholder="Enter your name">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="Enter your email">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Create a password">
                </div>
                <button type="submit" class="btn-primary" style="width: 100%; border: none; cursor: pointer; padding: 18px; font-size: 1.1rem; font-weight: 800; border-radius: 12px; box-shadow: 0 8px 20px rgba(252, 128, 25, 0.3);">Register Now</button>
            </form>
            <p style="margin-top: 30px; text-align: center; color: var(--text-muted); font-weight: 600;">
                Already have an account? <a href="login.php" style="color: var(--swiggy-orange); text-decoration: none; font-weight: 800;">Login here</a>
            </p>
        </div>
    </div>
</body>
</html>

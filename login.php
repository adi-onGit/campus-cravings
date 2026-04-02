<?php
session_start();
require_once 'php/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE (email = '$identifier' OR name = '$identifier') AND password = '$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        header("Location: home.php");
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Campus Cravings</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .bg-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            object-fit: cover;
            z-index: 1;
            opacity: 0.6;
        }
        .login-card {
            width: 100%;
            max-width: 450px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            border: 1px solid var(--border-light);
            position: relative;
            z-index: 2;
            backdrop-filter: blur(10px);
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
    </style>
</head>
<body>
    <div class="login-wrapper">
        <video autoplay loop muted playsinline class="bg-video" poster="assets/food/hero_bg.png">
            <source src="assets/video/burger.mp4" type="video/mp4">
            <!-- Fallback image if video is missing -->
            <img src="assets/food/hero_bg.png" style="width: 100vw; height: 100vh; object-fit: cover; position: absolute; z-index: -1;">
        </video>
        <div class="login-card">
            <div class="logo-desktop" style="justify-content: center; margin-bottom: 40px; font-size: 2rem;">
                🍴 Campus<span>Cravings</span>
            </div>
            <h2>Login</h2>
            <?php if ($error): ?>
                <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Email or Username</label>
                    <input type="text" name="email" required placeholder="Enter your email or username">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter your password">
                </div>
                <button type="submit" class="btn-primary" style="width: 100%; border: none; cursor: pointer; padding: 18px; font-size: 1.1rem; font-weight: 800; border-radius: 12px; box-shadow: 0 8px 20px rgba(252, 128, 25, 0.3);">Login Now</button>
            </form>
            <p style="margin-top: 30px; text-align: center; color: var(--text-muted); font-weight: 600;">
                New to Campus Cravings? <a href="register.php" style="color: var(--swiggy-orange); text-decoration: none; font-weight: 800;">Create Account</a>
            </p>
        </div>
    </div>
</body>
</html>

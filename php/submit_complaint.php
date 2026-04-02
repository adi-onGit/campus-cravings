<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $sql = "INSERT INTO complaints (user_id, subject, message) VALUES ($user_id, '$subject', '$message')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: ../profile.php?success=1");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

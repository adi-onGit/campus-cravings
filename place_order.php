<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $price = (float)$_POST['price'];
    $quantity = (int)$_POST['quantity'];
    $total_amount = $price * $quantity;

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Create order
        $sql_order = "INSERT INTO orders (user_id, total_amount) VALUES ($user_id, $total_amount)";
        mysqli_query($conn, $sql_order);
        $order_id = mysqli_insert_id($conn);

        // Add order item
        $sql_item = "INSERT INTO order_items (order_id, item_name, price, quantity) VALUES ($order_id, '$item_name', $price, $quantity)";
        mysqli_query($conn, $sql_item);

        mysqli_commit($conn);
        $_SESSION['order_success'] = "Order placed successfully for $item_name!";
        header("Location: profile.php");
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['order_error'] = "Error placing order: " . $e->getMessage();
        header("Location: menu.php?id=" . $_POST['restaurant_id']);
    }
} else {
    header("Location: home.php");
}
?>

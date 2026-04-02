<?php
require 'php/db.php';
$res = mysqli_query($conn, "SELECT id, name, role FROM users");
while($row = mysqli_fetch_assoc($res)) {
    echo "User: " . $row['name'] . " | Role: " . $row['role'] . "\n";
}
?>

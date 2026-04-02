<?php
require 'php/db.php';
mysqli_query($conn, "ALTER TABLE orders ADD COLUMN status VARCHAR(50) DEFAULT 'Processing'");
echo 'Table modified!';
?>

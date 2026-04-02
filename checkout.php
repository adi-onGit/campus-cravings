<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    echo "<script>alert('Admins cannot place orders.'); window.location.href='home.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Campus Cravings</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background: #fff; font-family: 'Outfit', sans-serif;">
    <div class="app-container">
        <header class="app-header">
            <a href="home.php" class="back-link-res" style="text-decoration: none; color: var(--text-main); font-weight: 700;">
                <i class="fas fa-chevron-left"></i> Back
            </a>
            <div class="logo-desktop">🍴 Campus<span>Cravings</span></div>
            <div style="width: 50px;"></div>
        </header>

        <main class="checkout-container" style="padding: 20px; max-width: 600px; margin: 0 auto;">
            <h2 class="checkout-h2" style="font-size: 1.5rem; font-weight: 800; margin-bottom: 30px; border-bottom: 2px solid var(--text-main); padding-bottom: 10px;">Order Summary</h2>

            <div id="cart-list" style="margin-bottom: 30px;">
                <!-- Loaded via JS -->
            </div>

            <div class="total-row" id="total-container" style="display:none; justify-content: space-between; font-weight: 800; font-size: 1.3rem; margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 12px;">
                <span>Total Amount</span>
                <span id="display-total" style="color: var(--swiggy-orange);">₹0</span>
            </div>

            <div id="empty-cart-msg" class="empty-cart-msg" style="text-align: center; padding: 50px;">
                <div style="font-size: 4rem; color: #eee; margin-bottom: 20px;"><i class="fas fa-shopping-basket"></i></div>
                <p style="font-size: 1.1rem; color: var(--text-muted);">Your cart is empty.</p>
                <a href="home.php" class="btn-primary" style="display: inline-block; margin-top: 20px; text-decoration: none;">Go add some food!</a>
            </div>

            <form action="payment.php" method="POST" id="checkout-form" class="checkout-form" style="display:none; margin-top: 40px;">
                <input type="hidden" name="total_amount" id="form-total" value="0">
                <input type="hidden" name="cart_data" id="cart-data" value="">
                <button type="submit" class="btn-primary" style="width: 100%; border: none; cursor: pointer; padding: 18px; font-size: 1.1rem; font-weight: 800; border-radius: 12px; box-shadow: 0 8px 20px rgba(252, 128, 25, 0.3);">Proceed to Pay</button>
            </form>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let cart = JSON.parse(localStorage.getItem("cart")) || [];
            if (cart.length > 0) {
                document.getElementById('empty-cart-msg').style.display = 'none';
                document.getElementById('total-container').style.display = 'flex';
                document.getElementById('checkout-form').style.display = 'block';

                let list = document.getElementById("cart-list");
                let total = 0;

                cart.forEach((item, index) => {
                    let div = document.createElement("div");
                    div.style.display = "flex";
                    div.style.justifyContent = "space-between";
                    div.style.alignItems = "center";
                    div.style.padding = "15px 0";
                    div.style.borderBottom = "1px solid #f0f0f0";
                    div.innerHTML = `
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span style="font-weight: 600; font-size: 1rem;">${item.name}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span style="font-weight: 700;">₹${item.price}</span>
                            <button onclick="removeItem(${index})" style="background: none; border: none; color: #ff4757; cursor: pointer;"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    `;
                    list.appendChild(div);
                    total += parseFloat(item.price);
                });

                document.getElementById("display-total").innerText = "₹" + total;
                document.getElementById("form-total").value = total;
                document.getElementById("cart-data").value = JSON.stringify(cart);
            }
        });

        function removeItem(index) {
            let cart = JSON.parse(localStorage.getItem("cart")) || [];
            cart.splice(index, 1);
            localStorage.setItem("cart", JSON.stringify(cart));
            location.reload();
        }
    </script>
</body>
</html>

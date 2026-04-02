<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";

if (isset($_POST['confirm_payment'])) {
    $user_id = $_SESSION['user_id'];
    $payment_method = $_POST['payment_method'];
    $final_amount = (float)$_POST['final_amount'];
    $cart_data = json_decode($_POST['cart_data'], true);

    mysqli_begin_transaction($conn);
    try {
        $sql_order = "INSERT INTO orders (user_id, total_amount) VALUES ($user_id, $final_amount)";
        mysqli_query($conn, $sql_order);
        $order_id = mysqli_insert_id($conn);

        foreach ($cart_data as $item) {
            $name = mysqli_real_escape_string($conn, $item['name']);
            $price = (float)$item['price'];
            $sql_item = "INSERT INTO order_items (order_id, item_name, price, quantity) VALUES ($order_id, '$name', $price, 1)";
            mysqli_query($conn, $sql_item);
        }

        mysqli_commit($conn);
        echo "<script>localStorage.removeItem('cart'); window.location.href='profile.php';</script>";
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Error placing order: " . $e->getMessage();
    }
}

if (!isset($_POST['cart_data']) || !isset($_POST['total_amount']) && !isset($_POST['confirm_payment'])) {
    header("Location: checkout.php");
    exit();
}

$base_amount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;
$cart_data_json = isset($_POST['cart_data']) ? $_POST['cart_data'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Campus Cravings</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .payment-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            border: 1px solid var(--border-light);
        }
        .pay-header {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-light);
            text-align: center;
        }
        .method-label {
            display: flex;
            align-items: center;
            padding: 20px;
            border: 2px solid var(--border-light);
            border-radius: 12px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .method-label:hover {
            border-color: #ffd0a8;
            background: #fffafa;
        }
        .method-label input[type="radio"] {
            margin-right: 15px;
            transform: scale(1.3);
        }
        .method-label i {
            font-size: 1.5rem;
            margin-right: 15px;
            color: var(--text-muted);
            width: 30px;
            text-align: center;
        }
        .method-label.active {
            border-color: var(--swiggy-orange);
            background: rgba(252, 128, 25, 0.05);
        }
        .method-label.active i {
            color: var(--swiggy-orange);
        }
        .upi-details {
            display: none;
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px dashed #ccc;
        }
        .upi-details.active {
            display: block;
        }
        .upi-details img {
            width: 150px;
            height: 150px;
            margin-bottom: 15px;
            border-radius: 10px;
        }
        .cod-details {
            display: none;
            padding: 15px;
            background: #fff5f5;
            color: #ff4757;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 700;
            text-align: center;
        }
        .cod-details.active {
            display: block;
        }
        .amount-summary {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        .amount-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 1rem;
            color: var(--text-muted);
        }
        .amount-row.total {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--text-main);
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px dashed #ddd;
        }
    </style>
</head>
<body style="background: var(--bg-main);">
    <div class="app-container">
        <header class="app-header">
            <a href="checkout.php" style="color: var(--text-main); text-decoration: none; font-weight: 700;">
                <i class="fas fa-chevron-left"></i> Back to Cart
            </a>
            <div class="logo-desktop">🍴 Campus<span>Cravings</span></div>
            <div style="width: 50px;"></div>
        </header>

        <main class="payment-container">
            <h2 class="pay-header">Select Payment Method</h2>
            
            <?php if(!empty($error)): ?>
                <div style="color: red; margin-bottom: 15px; text-align: center; font-weight: bold;"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="payment.php" method="POST" id="pay-form">
                <input type="hidden" name="cart_data" value="<?php echo htmlspecialchars($cart_data_json); ?>">
                <input type="hidden" name="base_amount" id="base_amount" value="<?php echo $base_amount; ?>">
                <input type="hidden" name="final_amount" id="final_amount" value="<?php echo $base_amount; ?>">
                <input type="hidden" name="confirm_payment" value="1">

                <label class="method-label" onclick="selectMethod('upi')" id="label-upi">
                    <input type="radio" name="payment_method" value="upi" id="radio-upi" required>
                    <i class="fab fa-google-pay"></i>
                    Pay via UPI (GPay/PhonePe)
                </label>

                <div class="upi-details" id="upi-details">
                    <h4 style="margin-bottom: 10px; color: var(--text-main);">Scan to Pay</h4>
                    <img src="assets/qr_code.png" alt="UPI QR Code">
                    <p style="font-size: 0.9rem; color: var(--text-muted);">Please scan this QR code using any UPI app to make the payment.</p>
                </div>

                <label class="method-label" onclick="selectMethod('cod')" id="label-cod">
                    <input type="radio" name="payment_method" value="cod" id="radio-cod">
                    <i class="fas fa-hand-holding-usd"></i>
                    Cash on Delivery (COD)
                </label>

                <div class="cod-details" id="cod-details">
                    <i class="fas fa-info-circle"></i> An additional ₹5 COD charge will be applied to your total.
                </div>

                <div class="amount-summary">
                    <div class="amount-row">
                        <span>Items Total</span>
                        <span>₹<?php echo number_format($base_amount, 2); ?></span>
                    </div>
                    <div class="amount-row" id="row-cod-fee" style="display: none; color: #ff4757; font-weight: 600;">
                        <span>COD Charge</span>
                        <span>+ ₹5.00</span>
                    </div>
                    <div class="amount-row total">
                        <span>Final Amount</span>
                        <span id="display-final">₹<?php echo number_format($base_amount, 2); ?></span>
                    </div>
                </div>

                <button type="submit" id="submit-btn" class="btn-primary" style="width: 100%; border: none; cursor: pointer; padding: 18px; font-size: 1.1rem; font-weight: 800; border-radius: 12px; box-shadow: 0 8px 20px rgba(252, 128, 25, 0.3);">Confirm Payment</button>
            </form>
        </main>
    </div>

    <script>
        const baseAmount = <?php echo $base_amount; ?>;
        const form = document.getElementById('pay-form');
        const submitBtn = document.getElementById('submit-btn');
        
        function selectMethod(method) {
            document.getElementById('radio-' + method).checked = true;
            
            // UI Toggle
            document.getElementById('label-upi').classList.toggle('active', method === 'upi');
            document.getElementById('label-cod').classList.toggle('active', method === 'cod');
            
            document.getElementById('upi-details').classList.toggle('active', method === 'upi');
            document.getElementById('cod-details').classList.toggle('active', method === 'cod');
            
            // Amount Logic
            let finalAmount = baseAmount;
            if (method === 'cod') {
                finalAmount += 5;
                document.getElementById('row-cod-fee').style.display = 'flex';
                submitBtn.innerText = 'Confirm Order (COD)';
            } else {
                document.getElementById('row-cod-fee').style.display = 'none';
                submitBtn.innerText = 'I have Paid via UPI';
            }
            
            document.getElementById('final_amount').value = finalAmount;
            document.getElementById('display-final').innerText = '₹' + finalAmount.toFixed(2);
        }

        form.addEventListener('submit', function(e) {
            const method = document.querySelector('input[name="payment_method"]:checked');
            
            if (method && method.value === 'upi') {
                e.preventDefault();
                
                // Show checking state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying Payment...';
                submitBtn.style.background = '#686b78';
                submitBtn.style.pointerEvents = 'none';

                // Simulate payment successful after 3 seconds
                setTimeout(() => {
                    submitBtn.innerHTML = '<i class="fas fa-check-circle"></i> Payment Received Successfully!';
                    submitBtn.style.background = '#1a7f37';
                    
                    setTimeout(() => {
                        form.submit();
                    }, 1500);
                }, 3000);
            }
        });
    </script>
</body>
</html>

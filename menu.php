<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$restaurant_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$sql_res = "SELECT * FROM restaurants WHERE id = $restaurant_id";
$res_result = mysqli_query($conn, $sql_res);
$restaurant = mysqli_fetch_assoc($res_result);

if (!$restaurant) {
    die("Restaurant not found.");
}

$sql_menu = "SELECT m.*, c.category_name 
             FROM menu_items m 
             JOIN menu_categories c ON m.category_id = c.id 
             WHERE m.restaurant_id = $restaurant_id 
             ORDER BY c.id ASC";
$menu_result = mysqli_query($conn, $sql_menu);

// Helper function to map items to local images
function getLocalImage($name, $current_url) {
    if (empty($name)) return $current_url;
    $base_name_lower = strtolower(trim($name));
    $extensions = ['jpg', 'jpeg', 'png', 'webp', 'avif'];
    
    // Common file name formats
    $formats = [
        $base_name_lower,
        str_replace(" ", "-", $base_name_lower),
        str_replace(" ", "_", $base_name_lower),
        str_replace("-", " ", $base_name_lower)
    ];

    foreach ($formats as $fmt) {
        foreach ($extensions as $ext) {
            $path = "assets/food/" . $fmt . "." . $ext;
            if (file_exists($path)) return $path;
        }
    }

    $mapping = [
        'Green Salad' => 'assets/restaurant/restaurant-1.jpg',
        'Shawarma Xpress' => 'assets/restaurant/restaurant-2.webp',
        'Adventures Cafe' => 'assets/restaurant/restaurant-3.jpg',
        'Pizza Point' => 'assets/restaurant/restaurant-1.jpg'
    ];
    
    foreach ($mapping as $key => $path) {
        if (stripos($name, $key) !== false) {
            return $path;
        }
    }

    return $current_url;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $restaurant['name']; ?> - Campus Cravings</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hidden { display: none !important; }
    </style>
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <a href="home.php" class="back-link"><?php echo $restaurant['name']; ?></a>
            <div class="logo-desktop">Campus<span>Cravings</span></div>
            <a href="checkout.php" class="account-btn"><i class="fas fa-shopping-cart"></i></a>
        </header>

        <main class="app-main">
            <div class="search-container">
                <input type="text" id="searchInput" class="search-bar" placeholder="Search for dishes..." onkeyup="searchMenu()">
            </div>

            <div id="menuContainer" class="products-grid">
                <?php 
                $current_cat = "";
                while($item = mysqli_fetch_assoc($menu_result)): 
                    if ($current_cat != $item['category_name']):
                        $current_cat = $item['category_name'];
                ?>
                    <div class="category-header" data-category="<?php echo $current_cat; ?>" style="grid-column: 1/-1; margin-top: 30px; border-bottom: 2px solid #282c3f;">
                        <h2 style="font-size: 1.4rem; padding-bottom: 5px;"><?php echo $current_cat; ?></h2>
                    </div>
                <?php endif; ?>

                <div class="product-card" data-name="<?php echo strtolower($item['item_name']); ?>">
                    <div class="card-image-wrap">
                        <img src="<?php echo getLocalImage($item['item_name'], $item['image_url']); ?>" alt="<?php echo $item['item_name']; ?>">
                    </div>
                    <div class="card-info">
                        <h4><?php echo htmlspecialchars($item['item_name']); ?></h4>
                        <div class="card-badge">FRESH</div>
                        <div class="price-row">
                            <span class="price">₹<?php echo number_format($item['price'], 0); ?></span>
                        </div>
                    </div>
                    <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
                        <button class="add-btn" onclick="addToCart('<?php echo addslashes($item['item_name']); ?>', <?php echo $item['price']; ?>)">+</button>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            </div>

            <div id="noResults" class="hidden" style="text-align: center; padding: 50px;">
                <h3>Dish not found</h3>
                <p>Try searching for something else!</p>
            </div>
        </main>

        <div id="floatingCart" class="cart-count" onclick="window.location.href='checkout.php'" style="display: none; position: fixed; bottom: 20px; left: 20px; right: 20px; background: #fc8019; color: white; padding: 15px; border-radius: 12px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; z-index: 1000;">
            <span><span id="cartNumber">0</span> ITEMS</span>
            <span>VIEW CART <i class="fas fa-shopping-bag"></i></span>
        </div>
    </div>

    <script src="js/script.js"></script>
    <script>
        function searchMenu() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const cards = document.getElementsByClassName('product-card');
            const headers = document.getElementsByClassName('category-header');
            let hasVisibleCards = false;

            for (let i = 0; i < cards.length; i++) {
                const name = cards[i].getAttribute('data-name');
                if (name.includes(filter)) {
                    cards[i].classList.remove('hidden');
                    hasVisibleCards = true;
                } else {
                    cards[i].classList.add('hidden');
                }
            }

            for (let j = 0; j < headers.length; j++) {
                let nextElement = headers[j].nextElementSibling;
                let foundMatch = false;
                while (nextElement && !nextElement.classList.contains('category-header')) {
                    if (!nextElement.classList.contains('hidden')) {
                        foundMatch = true;
                        break;
                    }
                    nextElement = nextElement.nextElementSibling;
                }
                if (foundMatch) headers[j].classList.remove('hidden');
                else headers[j].classList.add('hidden');
            }

            const noResults = document.getElementById('noResults');
            if (!hasVisibleCards && filter !== "") noResults.classList.remove('hidden');
            else noResults.classList.add('hidden');
        }

        function updateFloatingCart() {
            const cartItems = JSON.parse(localStorage.getItem("cart")) || [];
            const floatingCart = document.getElementById('floatingCart');
            const cartNumber = document.getElementById('cartNumber');
            if (cartItems.length > 0) {
                floatingCart.style.display = 'flex';
                cartNumber.innerText = cartItems.length;
            } else {
                floatingCart.style.display = 'none';
            }
        }

        const originalAddToCart = window.addToCart;
        window.addToCart = function(name, price) {
            originalAddToCart(name, price);
            updateFloatingCart();
        };

        document.addEventListener("DOMContentLoaded", updateFloatingCart);
    </script>
</body>
</html>

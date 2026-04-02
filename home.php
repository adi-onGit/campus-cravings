<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all restaurants
$res_query = "SELECT * FROM restaurants LIMIT 6";
$res_result = mysqli_query($conn, $res_query);
$restaurants = [];
if ($res_result) {
    while ($row = mysqli_fetch_assoc($res_result)) {
        $restaurants[] = $row;
    }
}

// Fetch categories for the radio button filter
$cat_query = "SELECT * FROM menu_categories LIMIT 10";
$cat_result = mysqli_query($conn, $cat_query);
$categories = [];
if ($cat_result) {
    while ($row = mysqli_fetch_assoc($cat_result)) {
        $categories[] = $row;
    }
}

// Handle category filter
$selected_category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Fetch menu items based on category filter
if ($selected_category === 'all') {
    $menu_query = "
        SELECT m.*, r.name as restaurant_name, c.category_name 
        FROM menu_items m 
        JOIN restaurants r ON m.restaurant_id = r.id 
        JOIN menu_categories c ON m.category_id = c.id
        LIMIT 8
    ";
} else {
    $cat_id = mysqli_real_escape_string($conn, $selected_category);
    $menu_query = "
        SELECT m.*, r.name as restaurant_name, c.category_name 
        FROM menu_items m 
        JOIN restaurants r ON m.restaurant_id = r.id 
        JOIN menu_categories c ON m.category_id = c.id
        WHERE m.category_id = '$cat_id'
        LIMIT 8
    ";
}
$menu_result = mysqli_query($conn, $menu_query);
$menu_items = [];
if ($menu_result) {
    while ($row = mysqli_fetch_assoc($menu_result)) {
        $menu_items[] = $row;
    }
}

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

// User details for profile picture
$uid = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT profile_picture FROM users WHERE id = '$uid'");
$user_data = mysqli_fetch_assoc($user_query);
$nav_pfp = !empty($user_data['profile_picture']) ? $user_data['profile_picture'] : 'default.jpeg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Campus Cravings</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Improvised CSS for layout fixes */
        .restaurant-card {
            text-decoration: none;
            color: inherit;
        }
        .hero-section {
            margin-bottom: 40px;
        }
        .section-header {
            margin-top: 30px;
            margin-bottom: 20px;
        }
        .categories-section {
            margin-top: 20px;
        }
        .product-card {
            height: 100%;
        }
        .card-from-text {
            font-size: 0.8rem;
            color: #686b78;
            margin-top: 5px;
        }
        .logo-desktop span {
            background: #fc8019;
            color: #fff;
            padding: 2px 8px;
            border-radius: 6px;
        }
        .pill-label input[type="radio"]:checked + .pill {
            background: #fc8019;
            border-color: #fc8019;
            color: #fff;
        }
        /* Fix for horizontal scroll on mobile */
        @media (max-width: 768px) {
            .products-grid {
                display: flex;
                overflow-x: auto;
                padding-bottom: 10px;
            }
            .product-card {
                min-width: 200px;
                margin-right: 15px;
            }
            .restaurant-card {
                min-width: 150px;
                height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <div class="logo-desktop">
                🍴 Campus<span>Cravings</span>
            </div>

            <div class="location-selector">
                <p class="label">Location</p>
                <div class="current-location">
                    <span>📍 XIM University</span>
                </div>
            </div>

            <nav class="desktop-nav">
                <a href="home.php" class="active">Home</a>
                <a href="checkout.php">Cart</a>
                <a href="profile.php">Account</a>
            </nav>

            <a href="profile.php" class="account-btn">
                <img src="assets/pfp/<?php echo htmlspecialchars($nav_pfp); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            </a>
        </header>

        <main class="app-main">
            <section class="hero-section">
                <div class="hero-image-container">
                    <div class="hero-img-bg" style="background-image: url('assets/food/hero_bg.png');"></div>
                    <div class="hero-overlay"></div>
                </div>
                <div class="hero-content">
                    <h1>Craving <span class="hero-highlight">Something?</span></h1>
                    <p>Delicious food from your favorite campus spots, delivered right to your hostel door.</p>
                    <div class="hero-buttons">
                        <a href="checkout.php" class="btn-primary">View Cart</a>
                    </div>
                </div>
            </section>

            <!-- Categories -->
            <div class="categories-section">
                <form action="home.php" method="GET" id="catFilter">
                    <div class="category-pills">
                        <label class="pill-label">
                            <input type="radio" name="category" value="all" <?php echo $selected_category === 'all' ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <span class="pill">All Dishes</span>
                        </label>
                        <?php foreach ($categories as $cat): ?>
                            <label class="pill-label">
                                <input type="radio" name="category" value="<?php echo $cat['id']; ?>" <?php echo (string)$selected_category === (string)$cat['id'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                                <span class="pill"><?php echo htmlspecialchars($cat['category_name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>

            <!-- Available Foods (Popular on Campus) -->
            <div class="section-header">
                <h3>Popular on Campus</h3>
            </div>

            <div class="products-grid">
                <?php foreach ($menu_items as $item): ?>
                    <div class="product-card">
                        <div class="card-image-wrap">
                            <img src="<?php echo getLocalImage($item['item_name'], $item['image_url']); ?>" alt="<?php echo $item['item_name']; ?>">
                        </div>
                        <div class="card-info">
                            <h4><?php echo htmlspecialchars($item['item_name']); ?></h4>
                            <div class="card-badge"><?php echo $item['category_name']; ?></div>
                            <p class="card-from-text">From <?php echo htmlspecialchars($item['restaurant_name']); ?></p>
                            <div class="price-row">
                                <span class="price">₹<?php echo number_format($item['price'], 0); ?></span>
                            </div>
                        </div>
                        <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
                            <button class="add-btn" onclick="addToCart('<?php echo addslashes($item['item_name']); ?>', <?php echo $item['price']; ?>)">+</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Restaurants Section -->
            <div class="section-header">
                <h3>Featured Restaurants</h3>
            </div>

            <div class="products-grid">
                <?php foreach ($restaurants as $res): ?>
                    <a href="menu.php?id=<?php echo $res['id']; ?>" class="restaurant-card">
                        <div class="card-image-wrap">
                            <img src="<?php echo getLocalImage($res['name'], $res['image_url']); ?>" alt="<?php echo $res['name']; ?>">
                        </div>
                        <div class="card-info">
                            <div class="card-badge"><?php echo $res['cuisine_type']; ?></div>
                            <h4><?php echo htmlspecialchars($res['name']); ?></h4>
                            <p class="card-desc"><?php echo $res['description']; ?></p>
                            <div class="price-row">
                                <span class="rating"><i class="fas fa-star" style="color: #ffb800;"></i> <?php echo $res['rating']; ?></span>
                                <span class="location"><i class="fas fa-map-marker-alt"></i> <?php echo $res['location']; ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </main>

        <nav class="bottom-nav">
            <a href="home.php" class="nav-item active">
                <div class="nav-icon-bg"><i class="fas fa-home"></i></div>
                <span>HOME</span>
            </a>
            <a href="checkout.php" class="nav-item">
                <i class="fas fa-shopping-cart"></i>
                <span>CART</span>
            </a>
            <a href="profile.php" class="nav-item">
                <i class="fas fa-user"></i>
                <span>PROFILE</span>
            </a>
        </nav>
    </div>
    <script src="js/script.js"></script>
</body>
</html>

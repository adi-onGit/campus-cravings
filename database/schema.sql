CREATE DATABASE IF NOT EXISTS `campus-cravings-db`;
USE `campus-cravings-db`;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'restaurant', 'admin') DEFAULT 'student',
    phone VARCHAR(20),
    profile_picture VARCHAR(255) DEFAULT 'default.jpeg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Restaurants table
CREATE TABLE IF NOT EXISTS restaurants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    cuisine_type VARCHAR(50),
    rating DECIMAL(2,1),
    location VARCHAR(100)
);

-- Menu Categories table
CREATE TABLE IF NOT EXISTS menu_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL
);

-- Menu Items table
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT,
    category_id INT,
    item_name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order Items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    item_name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT DEFAULT 1,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Complaints table
CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_id INT NULL,
    subject VARCHAR(255),
    message TEXT,
    status ENUM('Open', 'Resolved') DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sample Data
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@xim.edu.in', 'admin123', 'admin'),
('Student', 'student@xim.edu.in', 'student123', 'student');

INSERT INTO restaurants (id, name, description, image_url, cuisine_type, rating, location) VALUES 
(1, 'Green Salad', 'Fresh, healthy, and campus favorites.', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=400', 'Indian & Chinese', 4.7, 'Near Hostel Gate 1'),
(2, 'Shawarma Xpress', 'Amazing Shawarma and fast food', 'https://images.unsplash.com/photo-1529042410759-befb1204b468?q=80&w=400', 'Fast Food', 4.5, 'Near Gate 3'),
(3, 'Adventures Cafe', 'Best place to hang out', 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?q=80&w=400', 'Cafe', 4.6, 'Student Center'),
(4, 'Pizza Point', 'Hot and cheesy pizzas', 'https://images.unsplash.com/photo-1513104890138-7c749659a591?q=80&w=400', 'Italian', 4.4, 'Food Court');

INSERT INTO menu_categories (id, category_name) VALUES 
(1, 'Thali'), (2, 'Burgers'), (3, 'Beverages'), (4, 'Snacks'), (5, 'Pizza'), (6, 'Biryani');

INSERT INTO menu_items (restaurant_id, category_id, item_name, price, image_url) VALUES 
(1, 1, 'Veg Thali', 70.00, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=200'),
(2, 2, 'Chicken Burger', 120.00, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=200'),
(3, 3, 'Cold Coffee', 80.00, 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?q=80&w=200'),
(3, 4, 'French Fries', 60.00, 'https://images.unsplash.com/photo-1518013045958-e116a9d97571?q=80&w=200'),
(4, 5, 'Margherita Pizza', 150.00, 'https://images.unsplash.com/photo-1574071318508-1cdbad80ad38?q=80&w=200'),
(1, 6, 'Chicken Biryani', 150.00, 'https://images.unsplash.com/photo-1563379091339-03b1cbb89818?q=80&w=200'),
(2, 2, 'Zinger Burger', 140.00, 'https://images.unsplash.com/photo-1513185158878-8d8c196b896b?q=80&w=200'),
(3, 3, 'Hot Chocolate', 90.00, 'https://images.unsplash.com/photo-1544787210-2211d74fc282?q=80&w=200');

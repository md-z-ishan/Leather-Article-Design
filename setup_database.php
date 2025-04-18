<?php
require_once 'config/db.php';

try {
    // Drop existing tables in correct order
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS designer_messages");
    $pdo->exec("DROP TABLE IF EXISTS orders");
    $pdo->exec("DROP TABLE IF EXISTS products");
    $pdo->exec("DROP TABLE IF EXISTS categories");
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Create users table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        role ENUM('user', 'designer', 'admin') NOT NULL DEFAULT 'user',
        bio TEXT,
        profile_image VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create categories table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        image_url VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Create products table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        designer_id INT NOT NULL,
        category_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        image_url VARCHAR(255),
        status ENUM('available', 'sold', 'hidden') DEFAULT 'available',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (designer_id) REFERENCES users(id),
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )");
    
    // Create orders table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        status ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )");
    
    // Create designer_messages table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS designer_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        designer_id INT NOT NULL,
        sender_name VARCHAR(255) NOT NULL,
        sender_email VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (designer_id) REFERENCES users(id)
    )");
    
    // Insert sample categories if none exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO categories (name, description, image_url) VALUES
            ('Wallets', 'Handcrafted leather wallets for every style', 'wallet-category.jpg'),
            ('Bags', 'Premium leather bags and accessories', 'bag-category.jpg'),
            ('Belts', 'Classic and modern leather belts', 'belt-category.jpg'),
            ('Accessories', 'Unique leather accessories and more', 'accessories-category.jpg')
        ");
    }

    // Insert sample designer if none exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'designer'");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO users (username, email, password, full_name, role, profile_image) VALUES
            ('John Smith', 'john@example.com', '" . password_hash('designer123', PASSWORD_DEFAULT) . "', 'John Smith', 'designer', 'designer-1.jpg'),
            ('Sarah Johnson', 'sarah@example.com', '" . password_hash('designer123', PASSWORD_DEFAULT) . "', 'Sarah Johnson', 'designer', 'designer-2.jpg'),
            ('Michael Brown', 'michael@example.com', '" . password_hash('designer123', PASSWORD_DEFAULT) . "', 'Michael Brown', 'designer', 'designer-3.jpg'),
            ('Emma Wilson', 'emma@example.com', '" . password_hash('designer123', PASSWORD_DEFAULT) . "', 'Emma Wilson', 'designer', 'designer-4.jpg')
        ");
    }

    // Insert sample products if none exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    if ($stmt->fetchColumn() == 0) {
        $designers = $pdo->query("SELECT id FROM users WHERE role = 'designer' LIMIT 4")->fetchAll();
        $categories = $pdo->query("SELECT id, name FROM categories LIMIT 4")->fetchAll();
        
        if (!empty($designers) && !empty($categories)) {
            foreach ($designers as $designer) {
                foreach ($categories as $category) {
                    $pdo->exec("INSERT INTO products (designer_id, category_id, name, description, price, image_url) VALUES
                        (" . $designer['id'] . ", " . $category['id'] . ", 
                        'Handcrafted " . $category['name'] . "', 
                        'Beautiful handmade leather " . strtolower($category['name']) . " crafted with premium materials.',
                        " . rand(50, 300) . ",
                        'product-" . $designer['id'] . "-" . $category['id'] . ".jpg')
                    ");
                }
            }
        }
    }
    
    echo "Database setup completed successfully!";
    echo "<br>You can now go back to <a href='index.php'>home page</a>";
    
} catch(PDOException $e) {
    echo "Error setting up database: " . $e->getMessage();
}
?>

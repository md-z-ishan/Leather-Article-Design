<?php
require_once 'config/db.php';
session_start();

// Get featured categories
$stmt = $pdo->query("SELECT * FROM categories LIMIT 4");
$categories = $stmt->fetchAll();

// Get featured products
$stmt = $pdo->query("SELECT p.*, u.username as designer_name, c.name as category_name 
                     FROM products p 
                     JOIN users u ON p.designer_id = u.id 
                     JOIN categories c ON p.category_id = c.id 
                     WHERE p.status = 'available' 
                     LIMIT 4");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leather Design Hub - Handcrafted Leather Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Hero Section */
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('images/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 100px 0;
            min-height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Products Section */
        .products-section {
            padding: 80px 0;
            background-color: #f8f9fa;
        }

        .products-section h2 {
            text-align: center;
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 40px;
            position: relative;
        }

        .products-section h2:after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: #8B4513;
            margin: 15px auto;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            padding: 20px;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .product-image {
            width: 100%;
            height: 280px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-info {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: white;
            position: relative;
        }

        .product-info h3 {
            color: #2c3e50;
            font-size: 1.4rem;
            margin: 0 0 10px;
            font-weight: 600;
        }

        .product-info .designer {
            color: #8B4513;
            font-size: 0.95rem;
            margin: 5px 0;
            font-weight: 500;
        }

        .product-info .category {
            color: #666;
            font-size: 0.9rem;
            margin: 5px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .product-info .price {
            color: #2c3e50;
            font-size: 1.3rem;
            font-weight: bold;
            margin: 15px 0;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: #8B4513;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-align: center;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: auto;
            border: 2px solid #8B4513;
        }

        .btn:hover {
            background-color: #ffffff;
            color: #8B4513;
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                padding: 15px;
            }

            .product-info {
                padding: 20px;
            }

            .product-info h3 {
                font-size: 1.2rem;
            }

            .product-info .price {
                font-size: 1.1rem;
            }

            .btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
        }

        /* Category Section */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .category-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .category-card:hover {
            transform: translateY(-5px);
        }

        .category-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .category-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .custom-orders-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('images/custom-orders-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 80px 0;
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="index.php">Leather Design Hub</a>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="designers.php">Designers</a></li>
                    <li><a href="custom-orders.php">Custom Orders</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1>Handcrafted Leather Products</h1>
            <p>Discover unique, high-quality leather goods made by talented designers</p>
            <a href="products.php" class="btn">Shop Now</a>
        </div>
    </section>

    <!-- Featured Categories -->
    <section class="categories-section">
    <div class="container">
        <h2>Featured Categories</h2>
        <div class="categories-grid">

            <div class="category-card">
                <img src="data:image/jpeg;base64,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" alt="Wallets" class="category-image">
                <div class="category-info">
                    <h3>Wallets</h3>
                    <p>Premium handcrafted leather wallets for everyday use.</p>
                    <a href="products.php?category=1" class="btn">View Products</a>
                </div>
            </div>

            <div class="category-card">
                <img src="data:image/jpeg;base64,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" alt="Belts" class="category-image">
                <div class="category-info">
                    <h3>Belts</h3>
                    <p>Stylish and durable leather belts for all occasions.</p>
                    <a href="products.php?category=2" class="btn">View Products</a>
                </div>
            </div>

            <div class="category-card">
                <img src="data:image/jpeg;base64,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" alt="Accessories" class="category-image">
                <div class="category-info">
                    <h3>Accessories</h3>
                    <p>Elegant leather accessories to complete your look.</p>
                    <a href="products.php?category=3" class="btn">View Products</a>
                </div>
            </div>

            <div class="category-card">
                <img src="data:image/jpeg;base64,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" alt="Bags" class="category-image">
                <div class="category-info">
                    <h3>Bags</h3>
                    <p>Functional and fashionable leather bags.</p>
                    <a href="products.php?category=4" class="btn">View Products</a>
                </div>
            </div>

        </div>
    </div>
</section>


    <!-- Featured Products -->
<section class="products-section">
    <div class="container">
        <h2>Featured Products</h2>
        <div class="products-grid">
            <?php
            $featuredProducts = [
                [
                    'name' => 'Classic Leather Wallet',
                    'designer_name' => 'John Doe',
                    'category_name' => 'Wallets',
                    'price' => 39.99,
                    'image_url' => 'product-2-1.jpg',
                    'id' => 1
                ],
                [
                    'name' => 'Vintage Leather Bag',
                    'designer_name' => 'Emily Smith',
                    'category_name' => 'Bags',
                    'price' => 89.99,
                    'image_url' => 'product-3-3.jpg',
                    'id' => 2
                ],
                [
                    'name' => 'Stylish Belt',
                    'designer_name' => 'Alex Brown',
                    'category_name' => 'Belts',
                    'price' => 29.99,
                    'image_url' => 'product-1-1.jpg',
                    'id' => 3
                ],
                
            ];
            ?>

            <?php foreach($featuredProducts as $product): ?>
                <div class="product-card">
                    <img 
                        src="images/products/<?php echo htmlspecialchars($product['image_url']); ?>" 
                        alt="<?php echo htmlspecialchars($product['name']); ?>" 
                        class="product-image">
                    
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="designer">By <?php echo htmlspecialchars($product['designer_name']); ?></p>
                        <p class="category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                        <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                        
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


    <!-- Custom Orders Section -->
    <section class="custom-orders-section">
        <div class="container">
            <h2>Custom Leather Products</h2>
            <p>Have a specific design in mind? Our talented designers can create custom leather products just for you.</p>
            <a href="custom-orders.php" class="btn">Request Custom Order</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p>Leather Design Hub connects talented leather designers with customers who appreciate handcrafted quality.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="terms.php">Terms & Conditions</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p>Email: info@leatherdesignhub.com</p>
                    <p>Phone: +1 234 567 890</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Leather Design Hub. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
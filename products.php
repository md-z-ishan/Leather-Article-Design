<?php
require_once 'config/db.php';
session_start();

// Get category filter
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Get search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = "SELECT p.*, COALESCE(p.image_url, 'product-1-1.jpg') as image, u.username as designer_name, c.name as category_name 
          FROM products p 
          JOIN users u ON p.designer_id = u.id 
          JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'available'";

$params = [];

if($category_id) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_id;
}

if($search) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Add sorting
$query .= " ORDER BY p.created_at DESC";

// Get products
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// If no products found, use sample data
if (empty($products)) {
    $products = [
        [
            'id' => 1,
            'name' => 'Classic Leather Wallet',
            'price' => 49.99,
            'image' => 'product-1-1.jpg',
            'category_name' => 'Wallets',
            'designer_name' => 'John Smith'
        ],
        [
            'id' => 2,
            'name' => 'Handcrafted Leather Bag',
            'price' => 199.99,
            'image' => 'product-1-1.jpg',
            'category_name' => 'Bags',
            'designer_name' => 'Sarah Johnson'
        ],
        [
            'id' => 3,
            'name' => 'Leather Tote Bag',
            'price' => 179.99,
            'image' => 'product-1-1.jpg',
            'category_name' => 'Bags',
            'designer_name' => 'Emma Wilson'
        ],
        [
            'id' => 4,
            'name' => 'Bifold Leather Wallet',
            'price' => 45.99,
            'image' => 'product-1-1.jpg',
            'category_name' => 'Wallets',
            'designer_name' => 'David Lee'
        ],
        [
            'id' => 5,
            'name' => 'Leather Backpack',
            'price' => 249.99,
            'image' => 'product-1-1.jpg',
            'category_name' => 'Bags',
            'designer_name' => 'Lisa Chen'
        ],
        [
            'id' => 6,
            'name' => 'Slim Leather Wallet',
            'price' => 39.99,
            'image' => 'product-1-1.jpg',
            'category_name' => 'Wallets',
            'designer_name' => 'Robert Taylor'
        ],
        [
            'id' => 7,
            'name' => 'Leather Messenger Bag',
            'price' => 189.99,
            'image' => 'product-1-1.jpg',
            'category_name' => 'Bags',
            'designer_name' => 'Jennifer White'
        ],
        [
            'id' => 8,
            'name' => 'Leather Card Holder',
            'price' => 29.99,
            'image' => 'product-1-1.jpg',
            'category_name' => 'Wallets',
            'designer_name' => 'Thomas Clark'
        ],
        [
            'id' => 9,
            'name' => 'Leather Duffle Bag',
            'price' => 229.99,
            'image' => 'product-1-1.jpg',
            'category_name' => 'Bags',
            'designer_name' => 'Patricia Moore'
        ],
        [
            'id' => 10,
            'name' => 'Leather Coin Purse',
            'price' => 19.99,
            'image' => 'product-1-1.jpg',
            'category_name' => 'Wallets',
            'designer_name' => 'James Anderson'
        ],
        [
            'id' => 11,
            'name' => 'Leather Weekender Bag',
            'price' => 279.99,
            'image' => 'product-1-1.jpg',
            'category_name' => 'Bags',
            'designer_name' => 'Maria Garcia'
        ]
    ];
}

// Get categories for filter
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Leather Design Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .products-header {
            padding: 40px 0;
            background-color: #f5f5f5;
        }

        .filter-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .search-box {
            display: flex;
            gap: 10px;
        }

        .search-box input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            width: 300px;
        }

        .category-filter {
            display: flex;
            gap: 10px;
        }

        .category-filter select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
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

        .designer {
            color: #8B4513;
            font-size: 0.95rem;
            margin: 5px 0;
            font-weight: 500;
        }

        .category {
            color: #666;
            font-size: 0.9rem;
            margin: 5px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .price {
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

            .price {
                font-size: 1.1rem;
            }

            .btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
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
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Products Section -->
    <section class="products-header">
        <div class="container">
            <h1>Our Products</h1>
        </div>
    </section>

    <section class="products">
        <div class="container">
            <div class="product-grid products-grid">
                <?php foreach($products as $product): ?>
                <div class="product-card">
                    <img src="<?php echo file_exists('images/products/' . $product['image']) ? 'images/products/' . $product['image'] : 'https://via.placeholder.com/300x300?text=' . urlencode($product['name']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-image">
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="designer">By <?php echo htmlspecialchars($product['designer_name']); ?></p>
                        <p class="category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                        <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                        <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
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
<?php
require_once 'config/db.php';
session_start();

if(!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = (int)$_GET['id'];

// Get product details
$stmt = $pdo->prepare("SELECT p.*, u.username as designer_name, u.email as designer_email, c.name as category_name 
                      FROM products p 
                      JOIN users u ON p.designer_id = u.id 
                      JOIN categories c ON p.category_id = c.id 
                      WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if(!$product) {
    header('Location: products.php');
    exit();
}

// Handle add to cart
if(isset($_POST['add_to_cart']) && isset($_SESSION['user_id'])) {
    $quantity = (int)$_POST['quantity'];
    $user_id = $_SESSION['user_id'];
    
    // Check if product is already in cart
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $cart_item = $stmt->fetch();
    
    if($cart_item) {
        // Update quantity
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?");
        $stmt->execute([$quantity, $cart_item['id']]);
    } else {
        // Add new item
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }
    
    header('Location: cart.php');
    exit();
}

// Get related products
$stmt = $pdo->prepare("SELECT p.*, u.username as designer_name 
                      FROM products p 
                      JOIN users u ON p.designer_id = u.id 
                      WHERE p.category_id = ? AND p.id != ? AND p.status = 'available' 
                      LIMIT 4");
$stmt->execute([$product['category_id'], $product_id]);
$related_products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Leather Design Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .product-details {
            padding: 60px 0;
        }

        .product-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .product-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 5px;
        }

        .product-info h1 {
            margin-bottom: 20px;
        }

        .product-meta {
            margin-bottom: 20px;
        }

        .product-meta p {
            margin-bottom: 10px;
            color: #666;
        }

        .product-price {
            font-size: 24px;
            color: #8B4513;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .product-description {
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .quantity-selector {
            margin-bottom: 20px;
        }

        .quantity-selector input {
            width: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }

        .related-products {
            margin-top: 60px;
        }

        .related-products h2 {
            margin-bottom: 30px;
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

    <!-- Product Details -->
    <section class="product-details">
        <div class="container">
            <div class="product-container">
                <div class="product-image-container">
                    <img src="images/products/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                </div>
                <div class="product-info">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="product-meta">
                        <p>Category: <?php echo htmlspecialchars($product['category_name']); ?></p>
                        <p>Designer: <a href="designer.php?id=<?php echo $product['designer_id']; ?>"><?php echo htmlspecialchars($product['designer_name']); ?></a></p>
                    </div>
                    <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                    <div class="product-description">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <form method="POST" action="">
                            <div class="quantity-selector">
                                <label for="quantity">Quantity:</label>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" required>
                            </div>
                            <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
                        </form>
                    <?php else: ?>
                        <p>Please <a href="login.php">login</a> to add this product to your cart.</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($related_products): ?>
            <div class="related-products">
                <h2>Related Products</h2>
                <div class="product-grid">
                    <?php foreach($related_products as $related): ?>
                    <div class="product-card">
                        <img src="images/products/<?php echo $related['image']; ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
                        <h3><?php echo htmlspecialchars($related['name']); ?></h3>
                        <p class="price">$<?php echo number_format($related['price'], 2); ?></p>
                        <p class="designer">By <?php echo htmlspecialchars($related['designer_name']); ?></p>
                        <a href="product-details.php?id=<?php echo $related['id']; ?>" class="btn">View Details</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
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
<?php
require_once 'config/db.php';
session_start();

// Redirect if not logged in
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle quantity updates
if(isset($_POST['update_cart'])) {
    foreach($_POST['quantity'] as $cart_id => $quantity) {
        $quantity = (int)$quantity;
        if($quantity > 0) {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$quantity, $cart_id, $user_id]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->execute([$cart_id, $user_id]);
        }
    }
    header('Location: cart.php');
    exit();
}

// Handle item removal
if(isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['remove'], $user_id]);
    header('Location: cart.php');
    exit();
}

// Get cart items with product details
$stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.image, p.stock_quantity 
                      FROM cart c 
                      JOIN products p ON c.product_id = p.id 
                      WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Calculate total
$total = 0;
foreach($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Leather Design Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .cart-section {
            padding: 60px 0;
        }

        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .cart-header {
            margin-bottom: 30px;
        }

        .cart-items {
            margin-bottom: 30px;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto auto auto;
            gap: 20px;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #ddd;
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }

        .cart-item-details h3 {
            margin-bottom: 10px;
        }

        .cart-item-price {
            font-weight: bold;
            color: #8B4513;
        }

        .quantity-input {
            width: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }

        .remove-item {
            color: #ff0000;
            text-decoration: none;
        }

        .cart-summary {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin-top: 30px;
        }

        .cart-summary h2 {
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .total-row {
            font-size: 1.2em;
            font-weight: bold;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }

        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .empty-cart {
            text-align: center;
            padding: 40px 0;
        }

        .empty-cart i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 20px;
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

    <!-- Cart Section -->
    <section class="cart-section">
        <div class="container">
            <div class="cart-header">
                <h1>Shopping Cart</h1>
            </div>

            <?php if(empty($cart_items)): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added any items to your cart yet.</p>
                    <a href="products.php" class="btn">Continue Shopping</a>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="cart-items">
                        <?php foreach($cart_items as $item): ?>
                            <div class="cart-item">
                                <img src="images/products/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div class="cart-item-details">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="cart-item-price">$<?php echo number_format($item['price'], 2); ?></p>
                                </div>
                                <div class="quantity">
                                    <input type="number" name="quantity[<?php echo $item['id']; ?>]" 
                                           value="<?php echo $item['quantity']; ?>" min="1" 
                                           max="<?php echo $item['stock_quantity']; ?>" class="quantity-input">
                                </div>
                                <div class="subtotal">
                                    $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
                                <a href="?remove=<?php echo $item['id']; ?>" class="remove-item">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-summary">
                        <h2>Order Summary</h2>
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span>Calculated at checkout</span>
                        </div>
                        <div class="summary-row total-row">
                            <span>Total:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>

                    <div class="cart-actions">
                        <a href="products.php" class="btn">Continue Shopping</a>
                        <div>
                            <button type="submit" name="update_cart" class="btn">Update Cart</button>
                            <a href="checkout.php" class="btn">Proceed to Checkout</a>
                        </div>
                    </div>
                </form>
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
<?php
require_once 'config/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get cart items with product details
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.image_url, p.stock_quantity 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and process the order
    $shipping_address = $_POST['shipping_address'] ?? '';
    $shipping_city = $_POST['shipping_city'] ?? '';
    $shipping_state = $_POST['shipping_state'] ?? '';
    $shipping_zip = $_POST['shipping_zip'] ?? '';
    $shipping_country = $_POST['shipping_country'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Create order
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, total_amount, shipping_address, shipping_city, 
                              shipping_state, shipping_zip, shipping_country, payment_method, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $user_id,
            $total,
            $shipping_address,
            $shipping_city,
            $shipping_state,
            $shipping_zip,
            $shipping_country,
            $payment_method
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Add order items
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($cart_items as $item) {
            $stmt->execute([
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);
            
            // Update product stock
            $new_stock = $item['stock_quantity'] - $item['quantity'];
            $stmt2 = $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
            $stmt2->execute([$new_stock, $item['product_id']]);
        }
        
        // Clear cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Commit transaction
        $pdo->commit();
        
        // Redirect to order confirmation
        header("Location: order-confirmation.php?id=" . $order_id);
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $error = "An error occurred while processing your order. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Leather Design Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .checkout-section {
            padding: 60px 0;
        }

        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .checkout-header {
            margin-bottom: 30px;
        }

        .checkout-header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .checkout-form {
            background-color: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .order-summary {
            background-color: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .order-items {
            margin-bottom: 20px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .order-total {
            border-top: 2px solid #f0f0f0;
            padding-top: 20px;
            margin-top: 20px;
        }

        .order-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .order-total-row.total {
            font-weight: bold;
            font-size: 1.2em;
        }

        .place-order-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: #8B4513;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 3px;
            margin-top: 20px;
            border: none;
            cursor: pointer;
        }

        .place-order-btn:hover {
            background-color: #6B3513;
        }

        .error-message {
            color: #dc3545;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 3px;
        }

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 10px;
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
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="cart.php" class="active"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Checkout Section -->
    <section class="checkout-section">
        <div class="container">
            <div class="checkout-container">
                <div class="checkout-form">
                    <div class="checkout-header">
                        <h1>Checkout</h1>
                        <p>Please enter your shipping and payment information</p>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="error-message">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <h2>Shipping Information</h2>
                        <div class="form-group">
                            <label for="shipping_address">Address</label>
                            <input type="text" id="shipping_address" name="shipping_address" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_city">City</label>
                                <input type="text" id="shipping_city" name="shipping_city" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_state">State</label>
                                <input type="text" id="shipping_state" name="shipping_state" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_zip">ZIP Code</label>
                                <input type="text" id="shipping_zip" name="shipping_zip" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_country">Country</label>
                                <input type="text" id="shipping_country" name="shipping_country" required>
                            </div>
                        </div>

                        <h2>Payment Information</h2>
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="">Select Payment Method</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="paypal">PayPal</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <button type="submit" class="place-order-btn">Place Order</button>
                    </form>
                </div>

                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <div class="order-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="order-item">
                                <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                                <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="order-total">
                        <div class="order-total-row">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="order-total-row">
                            <span>Shipping:</span>
                            <span>Calculated at checkout</span>
                        </div>
                        <div class="order-total-row total">
                            <span>Total:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                </div>
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
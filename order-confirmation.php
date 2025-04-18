<?php
require_once 'config/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get order ID from URL
$order_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch order details
$stmt = $pdo->prepare("
    SELECT o.*, u.username, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

// Redirect if order not found or doesn't belong to user
if (!$order) {
    header('Location: dashboard.php');
    exit();
}

// Fetch order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image_url 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Leather Design Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .confirmation-section {
            padding: 60px 0;
        }

        .confirmation-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .confirmation-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .confirmation-header i {
            font-size: 3em;
            color: #28a745;
            margin-bottom: 20px;
        }

        .confirmation-header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .confirmation-header p {
            color: #666;
            font-size: 1.1em;
        }

        .order-details {
            margin-bottom: 30px;
        }

        .order-details h2 {
            font-size: 1.5em;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .order-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-group {
            margin-bottom: 15px;
        }

        .info-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #666;
        }

        .info-group span {
            display: block;
            font-size: 1.1em;
        }

        .order-items {
            margin-bottom: 30px;
        }

        .order-item {
            display: grid;
            grid-template-columns: 80px 1fr auto;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 3px;
        }

        .item-details {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .item-quantity {
            color: #666;
        }

        .item-price {
            text-align: right;
            font-weight: bold;
        }

        .order-total {
            text-align: right;
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #8B4513;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #6B3513;
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid #8B4513;
            color: #8B4513;
        }

        .btn-outline:hover {
            background-color: #8B4513;
            color: white;
        }

        @media (max-width: 768px) {
            .order-info {
                grid-template-columns: 1fr;
            }

            .order-item {
                grid-template-columns: 60px 1fr auto;
                gap: 10px;
            }

            .item-image {
                width: 60px;
                height: 60px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
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
                    <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Confirmation Section -->
    <section class="confirmation-section">
        <div class="container">
            <div class="confirmation-container">
                <div class="confirmation-header">
                    <i class="fas fa-check-circle"></i>
                    <h1>Thank You for Your Order!</h1>
                    <p>Your order has been successfully placed and is being processed.</p>
                </div>

                <div class="order-details">
                    <h2>Order Details</h2>
                    <div class="order-info">
                        <div>
                            <div class="info-group">
                                <label>Order Number</label>
                                <span>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                            </div>
                            <div class="info-group">
                                <label>Order Date</label>
                                <span><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                            </div>
                            <div class="info-group">
                                <label>Payment Method</label>
                                <span><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></span>
                            </div>
                        </div>
                        <div>
                            <div class="info-group">
                                <label>Shipping Address</label>
                                <span>
                                    <?php echo htmlspecialchars($order['shipping_address']); ?><br>
                                    <?php echo htmlspecialchars($order['shipping_city']); ?>, 
                                    <?php echo htmlspecialchars($order['shipping_state']); ?> 
                                    <?php echo htmlspecialchars($order['shipping_zip']); ?><br>
                                    <?php echo htmlspecialchars($order['shipping_country']); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <h2>Order Items</h2>
                    <div class="order-items">
                        <?php foreach ($order_items as $item): ?>
                            <div class="order-item">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="item-image">
                                <div class="item-details">
                                    <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                                </div>
                                <div class="item-price">
                                    $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-total">
                        Total: $<?php echo number_format($order['total_amount'], 2); ?>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="dashboard.php" class="btn">View Orders</a>
                    <a href="products.php" class="btn btn-outline">Continue Shopping</a>
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
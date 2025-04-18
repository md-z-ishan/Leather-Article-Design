<?php
require_once 'config/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Get user's orders with order items and product details
$stmt = $pdo->prepare("
    SELECT o.*, oi.quantity, oi.price, p.name as product_name, p.image_url 
    FROM orders o 
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id 
    WHERE o.user_id = ? 
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Get user's custom orders
$stmt = $pdo->prepare("SELECT * FROM custom_orders 
                       WHERE user_id = ? 
                       ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$custom_orders = $stmt->fetchAll();

// Get user's profile information
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// If user is a designer, get their products and custom order requests
if ($user_role === 'designer') {
    // Get designer's products
    $stmt = $pdo->prepare("SELECT * FROM products WHERE designer_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $designer_products = $stmt->fetchAll();

    // Get custom order requests for the designer
    $stmt = $pdo->prepare("
        SELECT co.*, u.username as customer_name 
        FROM custom_orders co 
        JOIN users u ON co.user_id = u.id 
        WHERE co.designer_id = ? 
        ORDER BY co.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $designer_custom_orders = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Leather Design Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
        }

        .dashboard-sidebar {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }

        .profile-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 15px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2em;
            color: #8B4513;
        }

        .profile-info {
            margin-bottom: 20px;
        }

        .profile-info p {
            margin: 5px 0;
        }

        .dashboard-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h2 {
            margin: 0;
        }

        .view-all {
            color: #8B4513;
            text-decoration: none;
            font-size: 0.9em;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        .order-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .order-items {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .order-item {
            display: flex;
            align-items: center;
            width: calc(50% - 15px);
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
        }

        .order-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }

        .order-details {
            flex-grow: 1;
        }

        .order-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .custom-order-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .custom-order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .custom-order-details {
            margin-top: 10px;
        }

        .custom-order-details p {
            margin: 5px 0;
        }

        .designer-section {
            margin-top: 30px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }

        .product-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .order-item {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-grid">
            <!-- Sidebar -->
            <div class="dashboard-sidebar">
                <div class="profile-section">
                    <div class="profile-image">
                        <i class="fas fa-user"></i>
                    </div>
                    <h2><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
                    <p class="role-badge"><?php echo ucfirst($user_role); ?></p>
                </div>

                <div class="profile-info">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
                    <p><strong>Member Since:</strong> <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                </div>

                <div class="quick-actions">
                    <a href="edit-profile.php" class="btn">Edit Profile</a>
                    <?php if ($user_role === 'designer'): ?>
                        <a href="add-product.php" class="btn">Add New Product</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Main Content -->
            <div class="dashboard-main">
                <!-- Regular Orders Section -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Your Orders</h2>
                        <a href="orders.php" class="view-all">View All</a>
                    </div>
                    <?php if (empty($orders)): ?>
                        <p>You haven't placed any orders yet.</p>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div>
                                        <h3>Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h3>
                                        <p>Order Date: <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                                    </div>
                                    <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </div>
                                </div>
                                <div class="order-items">
                                    <div class="order-item">
                                        <img src="images/products/<?php echo htmlspecialchars($order['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($order['product_name']); ?>" 
                                             class="order-image">
                                        <div class="order-details">
                                            <h4><?php echo htmlspecialchars($order['product_name']); ?></h4>
                                            <p>Quantity: <?php echo $order['quantity']; ?></p>
                                            <p>Price: $<?php echo number_format($order['price'], 2); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Custom Orders Section -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Your Custom Orders</h2>
                        <a href="custom-orders.php" class="view-all">View All</a>
                    </div>
                    <?php if (empty($custom_orders)): ?>
                        <p>You haven't placed any custom orders yet.</p>
                    <?php else: ?>
                        <?php foreach ($custom_orders as $order): ?>
                            <div class="custom-order-card">
                                <div class="custom-order-header">
                                    <h3><?php echo htmlspecialchars($order['product_type']); ?></h3>
                                    <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </div>
                                </div>
                                <div class="custom-order-details">
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($order['description']); ?></p>
                                    <?php if ($order['dimensions']): ?>
                                        <p><strong>Dimensions:</strong> <?php echo htmlspecialchars($order['dimensions']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($order['color']): ?>
                                        <p><strong>Color:</strong> <?php echo htmlspecialchars($order['color']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($order['material']): ?>
                                        <p><strong>Material:</strong> <?php echo htmlspecialchars($order['material']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($order['budget']): ?>
                                        <p><strong>Budget:</strong> <?php echo htmlspecialchars($order['budget']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($order['deadline']): ?>
                                        <p><strong>Deadline:</strong> <?php echo date('F j, Y', strtotime($order['deadline'])); ?></p>
                                    <?php endif; ?>
                                    <p><strong>Submitted:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if ($user_role === 'designer'): ?>
                    <!-- Designer Products Section -->
                    <div class="dashboard-section designer-section">
                        <div class="section-header">
                            <h2>Your Products</h2>
                            <a href="designer-products.php" class="view-all">View All</a>
                        </div>
                        <?php if (empty($designer_products)): ?>
                            <p>You haven't added any products yet.</p>
                        <?php else: ?>
                            <div class="product-grid">
                                <?php foreach ($designer_products as $product): ?>
                                    <div class="product-card">
                                        <img src="images/products/<?php echo htmlspecialchars($product['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             class="product-image">
                                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                        <p>$<?php echo number_format($product['price'], 2); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Designer Custom Orders Section -->
                    <div class="dashboard-section designer-section">
                        <div class="section-header">
                            <h2>Custom Order Requests</h2>
                            <a href="designer-orders.php" class="view-all">View All</a>
                        </div>
                        <?php if (empty($designer_custom_orders)): ?>
                            <p>You don't have any custom order requests yet.</p>
                        <?php else: ?>
                            <?php foreach ($designer_custom_orders as $order): ?>
                                <div class="custom-order-card">
                                    <div class="custom-order-header">
                                        <h3><?php echo htmlspecialchars($order['product_type']); ?></h3>
                                        <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </div>
                                    </div>
                                    <div class="custom-order-details">
                                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                        <p><strong>Description:</strong> <?php echo htmlspecialchars($order['description']); ?></p>
                                        <?php if ($order['budget']): ?>
                                            <p><strong>Budget:</strong> <?php echo htmlspecialchars($order['budget']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($order['deadline']): ?>
                                            <p><strong>Deadline:</strong> <?php echo date('F j, Y', strtotime($order['deadline'])); ?></p>
                                        <?php endif; ?>
                                        <p><strong>Submitted:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 
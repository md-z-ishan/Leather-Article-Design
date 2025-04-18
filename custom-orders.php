<?php
require_once 'config/db.php';
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Debug: Log POST data
        error_log("POST data: " . print_r($_POST, true));

        // Get form data
        $product_type = trim($_POST['product_type'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $dimensions = trim($_POST['dimensions'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $material = trim($_POST['material'] ?? '');
        $budget = trim($_POST['budget'] ?? '');
        $deadline = trim($_POST['deadline'] ?? '');
        $user_id = $_SESSION['user_id'];

        // Debug: Log processed data
        error_log("Processed data - User ID: $user_id, Product Type: $product_type");

        // Validate required fields
        if (empty($product_type)) {
            throw new Exception("Product type is required.");
        }
        if (empty($description)) {
            throw new Exception("Description is required.");
        }

        // Prepare the SQL statement
        $sql = "INSERT INTO custom_orders (user_id, product_type, description, dimensions, color, material, budget, deadline, status) 
                VALUES (:user_id, :product_type, :description, :dimensions, :color, :material, :budget, :deadline, 'pending')";
        
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_type', $product_type, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':dimensions', $dimensions, PDO::PARAM_STR);
        $stmt->bindParam(':color', $color, PDO::PARAM_STR);
        $stmt->bindParam(':material', $material, PDO::PARAM_STR);
        $stmt->bindParam(':budget', $budget, PDO::PARAM_STR);
        $stmt->bindParam(':deadline', $deadline, PDO::PARAM_STR);

        // Execute the statement
        if ($stmt->execute()) {
            $success = "Your custom order request has been submitted successfully!";
            // Clear form data after successful submission
            $_POST = array();
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Database error: " . $errorInfo[2]);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log("Error in custom-orders.php: " . $error);
    }
}

// Get user's existing custom orders
try {
    $stmt = $pdo->prepare("SELECT * FROM custom_orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $existing_orders = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching existing orders: " . $e->getMessage());
    $existing_orders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Orders - Leather Design Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .custom-order-section {
            padding: 60px 0;
        }

        .custom-order-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 40px;
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        .required-field::after {
            content: " *";
            color: red;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 3px;
            margin-bottom: 20px;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 3px;
            margin-bottom: 20px;
        }

        .submit-btn {
            background-color: #8B4513;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #6B3513;
        }

        .login-prompt {
            text-align: center;
            margin-top: 20px;
        }

        .custom-order-form {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .required::after {
            content: " *";
            color: red;
        }

        .existing-orders {
            margin-top: 40px;
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
            margin-bottom: 10px;
        }

        .order-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
        }

        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #cce5ff; color: #004085; }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
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

    <!-- Custom Order Section -->
    <section class="custom-order-section">
        <div class="container">
            <div class="custom-order-container">
                <h1>Request a Custom Order</h1>
                <p>Fill out the form below to request a custom leather product. Our designers will review your request and get back to you soon.</p>

                <?php if ($success): ?>
                    <div class="success-message">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error-message">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form class="custom-order-form" method="POST" action="">
                        <div class="form-group">
                            <label for="product_type" class="required">Product Type</label>
                            <select id="product_type" name="product_type" required>
                                <option value="">Select a product type</option>
                                <option value="Bag" <?php echo (isset($_POST['product_type']) && $_POST['product_type'] === 'Bag') ? 'selected' : ''; ?>>Bag</option>
                                <option value="Wallet" <?php echo (isset($_POST['product_type']) && $_POST['product_type'] === 'Wallet') ? 'selected' : ''; ?>>Wallet</option>
                                <option value="Belt" <?php echo (isset($_POST['product_type']) && $_POST['product_type'] === 'Belt') ? 'selected' : ''; ?>>Belt</option>
                                <option value="Accessory" <?php echo (isset($_POST['product_type']) && $_POST['product_type'] === 'Accessory') ? 'selected' : ''; ?>>Accessory</option>
                                <option value="Other" <?php echo (isset($_POST['product_type']) && $_POST['product_type'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="description" class="required">Description</label>
                            <textarea id="description" name="description" required placeholder="Describe your custom product in detail..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="dimensions">Dimensions (optional)</label>
                            <input type="text" id="dimensions" name="dimensions" placeholder="e.g., 30cm x 20cm x 10cm" value="<?php echo isset($_POST['dimensions']) ? htmlspecialchars($_POST['dimensions']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="color">Preferred Color (optional)</label>
                            <input type="text" id="color" name="color" placeholder="e.g., Brown, Black, etc." value="<?php echo isset($_POST['color']) ? htmlspecialchars($_POST['color']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="material">Preferred Material (optional)</label>
                            <input type="text" id="material" name="material" placeholder="e.g., Full-grain leather, Suede, etc." value="<?php echo isset($_POST['material']) ? htmlspecialchars($_POST['material']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="budget">Budget Range (optional)</label>
                            <input type="text" id="budget" name="budget" placeholder="e.g., $100-$200" value="<?php echo isset($_POST['budget']) ? htmlspecialchars($_POST['budget']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="deadline">Desired Completion Date (optional)</label>
                            <input type="date" id="deadline" name="deadline" value="<?php echo isset($_POST['deadline']) ? htmlspecialchars($_POST['deadline']) : ''; ?>">
                        </div>

                        <button type="submit" class="submit-btn">Submit Request</button>
                    </form>
                <?php else: ?>
                    <div class="login-prompt">
                        <p>Please <a href="login.php">login</a> or <a href="register.php">register</a> to submit a custom order request.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Existing Orders Section -->
    <section class="existing-orders">
        <div class="container">
            <h2>Your Previous Custom Orders</h2>
            <?php if (!empty($existing_orders)): ?>
                <?php foreach ($existing_orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <h3><?php echo htmlspecialchars($order['product_type']); ?></h3>
                            <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </div>
                        </div>
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
                <?php endforeach; ?>
            <?php else: ?>
                <p>You have not submitted any custom orders yet.</p>
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
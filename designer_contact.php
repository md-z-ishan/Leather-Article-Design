<?php
session_start();
include 'config/db.php';

// Get designer ID from URL
$designer_id = isset($_GET['id']) ? $_GET['id'] : null;

// Fetch designer details if ID is provided
$designer_name = '';
if ($designer_id) {
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ? AND role = 'designer'");
    $stmt->execute([$designer_id]);
    if ($designer = $stmt->fetch()) {
        $designer_name = $designer['username'];
    } else {
        header("Location: designers.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    
    // Add message to database
    $stmt = $pdo->prepare("INSERT INTO designer_messages (designer_id, sender_name, sender_email, message, created_at) VALUES (?, ?, ?, ?, NOW())");
    if ($stmt->execute([$designer_id, $name, $email, $message])) {
        $success_message = "Thank you for your message! We'll get back to you soon.";
        
        // Optional: Send email notification to designer
        // mail($designer['email'], "New Contact Message", "You have a new message from $name ($email):\n\n$message");
    } else {
        $error_message = "Sorry, there was an error sending your message. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact <?php echo $designer_name; ?> - Leather Design Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .contact-header {
            padding: 60px 0;
            background-color: #f5f5f5;
            text-align: center;
            margin-bottom: 40px;
        }

        .contact-header h1 {
            font-size: 2.5em;
            color: #8B4513;
            margin-bottom: 20px;
        }

        .contact-header p {
            font-size: 1.2em;
            color: #666;
            max-width: 800px;
            margin: 0 auto;
        }

        .contact-container {
            max-width: 800px;
            margin: 0 auto 50px;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .contact-form {
            display: grid;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: bold;
            color: #8B4513;
        }

        .form-group input,
        .form-group textarea {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #8B4513;
            outline: none;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        .submit-btn {
            background: #8B4513;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
            width: fit-content;
        }

        .submit-btn:hover {
            background: #6d3710;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .success-message {
            background: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }

        .error-message {
            background: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }

        @media (max-width: 768px) {
            .contact-container {
                margin: 20px;
                padding: 20px;
            }

            .contact-header {
                padding: 40px 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="contact-header">
        <div class="container">
            <h1>Contact <?php echo htmlspecialchars($designer_name); ?></h1>
            <p>Have a question about custom orders or want to discuss a potential project? Send a message directly to <?php echo htmlspecialchars($designer_name); ?>.</p>
        </div>
    </section>

    <div class="container">
        <div class="contact-container">
            <?php if (isset($success_message)): ?>
                <div class="message success-message">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="message error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="contact-form">
                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Your Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Send Message</button>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

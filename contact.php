<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Leather Design Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .contact-header {
            padding: 60px 0;
            background-color: #f5f5f5;
            text-align: center;
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

        .contact-form {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #8B4513;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        .submit-btn {
            background: #8B4513;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #6d3710;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="contact-header">
        <div class="container">
            <h1>Contact Us</h1>
            <p>Have questions about our products or services? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
        </div>
    </section>

    <section class="contact-form">
        <div class="container">
            <?php
            if (isset($_SESSION['contact_message'])) {
                $message_type = $_SESSION['contact_status'] ? 'success' : 'error';
                echo "<div class='alert alert-{$message_type}'>" . htmlspecialchars($_SESSION['contact_message']) . "</div>";
                unset($_SESSION['contact_message']);
                unset($_SESSION['contact_status']);
            }
            ?>
            <form action="process_contact.php" method="POST">
                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                <button type="submit" class="submit-btn">Send Message</button>
            </form>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

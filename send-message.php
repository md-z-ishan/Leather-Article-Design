<?php
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $designer_id = intval($_POST['designer_id']);
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $message = htmlspecialchars(trim($_POST['message']));

    if ($designer_id && $name && $email && $message) {
        // Example: Save message to DB (make sure to create a 'messages' table)
        $stmt = $pdo->prepare("INSERT INTO messages (designer_id, name, email, message, sent_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$designer_id, $name, $email, $message]);

        echo "<p>Message sent successfully! <a href='our-designers.php'>Go back</a></p>";
    } else {
        echo "All fields are required.";
    }
}
?>

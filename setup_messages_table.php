<?php
require_once 'config/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS designer_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        designer_id INT NOT NULL,
        sender_name VARCHAR(255) NOT NULL,
        sender_email VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME NOT NULL,
        FOREIGN KEY (designer_id) REFERENCES users(id)
    )";
    
    $pdo->exec($sql);
    echo "Designer messages table created successfully!";
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>

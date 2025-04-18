<?php
// Create the categories directory if it doesn't exist
if (!file_exists('images/categories')) {
    mkdir('images/categories', 0777, true);
}

// Connect to database to get category images
require_once 'config/db.php';

try {
    // Get all category image URLs
    $stmt = $pdo->query("SELECT image_url FROM categories WHERE image_url IS NOT NULL");
    $categories = $stmt->fetchAll();

    // Copy or create placeholder for each category image
    foreach ($categories as $category) {
        $image_url = $category['image_url'];
        $target_path = 'images/categories/' . $image_url;
        
        // Create a simple text file as placeholder if image doesn't exist
        if (!file_exists($target_path)) {
            file_put_contents($target_path, 'Placeholder for ' . $image_url);
            echo "Created placeholder for " . $image_url . "<br>";
        }
    }

    echo "<br>All category images have been processed.";
    echo "<br><a href='index.php'>Go to homepage</a>";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

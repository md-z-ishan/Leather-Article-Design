<?php
// Create the products directory if it doesn't exist
if (!file_exists('images/products')) {
    mkdir('images/products', 0777, true);
}

// Connect to database to get product images
require_once 'config/db.php';

try {
    // Get all product image URLs
    $stmt = $pdo->query("SELECT image_url FROM products WHERE image_url IS NOT NULL");
    $products = $stmt->fetchAll();

    // Copy or create placeholder for each product image
    foreach ($products as $product) {
        $image_url = $product['image_url'];
        $target_path = 'images/products/' . $image_url;
        
        // Create a simple text file as placeholder if image doesn't exist
        if (!file_exists($target_path)) {
            file_put_contents($target_path, 'Placeholder for ' . $image_url);
            echo "Created placeholder for " . $image_url . "<br>";
        }
    }

    echo "<br>All product images have been processed. You can now view them in the featured products section.";
    echo "<br><a href='index.php'>Go to homepage</a>";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

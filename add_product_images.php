<?php
// Create the products directory if it doesn't exist
$directory = 'images/products';
if (!file_exists($directory)) {
    mkdir($directory, 0777, true);
}

// List of product images we need
$images = [
    'product-1-1.jpg',  // Classic Leather Wallet
    'product-1-2.jpg',  // Handcrafted Leather Bag
    'product-1-3.jpg',  // Leather Belt
    'product-1-4.jpg',  // Accessories
    'product-2-1.jpg',  // Wallets by designer 2
    'product-2-2.jpg',  // Bags by designer 2
    'product-2-3.jpg',  // Belts by designer 2
    'product-2-4.jpg',  // Accessories by designer 2
    'product-3-1.jpg',  // Wallets by designer 3
    'product-3-2.jpg',  // Bags by designer 3
    'product-3-3.jpg',  // Belts by designer 3
    'product-3-4.jpg'   // Accessories by designer 3
];

// Create placeholder files for each product image
foreach ($images as $image) {
    $filepath = $directory . '/' . $image;
    if (!file_exists($filepath)) {
        // Create a simple text file as placeholder
        file_put_contents($filepath, 'Placeholder for ' . $image);
        echo "Created placeholder for " . $image . "<br>";
    }
}

echo "<br>All product images have been set up.";
echo "<br><a href='products.php'>Go to products page</a>";
?>

<?php
// Create directories if they don't exist
$directories = [
    'images',
    'images/categories',
    'images/products'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Category images
$categories = [
    'Bags' => '#8B4513',
    'Wallets' => '#654321',
    'Belts' => '#A0522D',
    'Accessories' => '#D2691E'
];

// Product images
$products = [
    'leather-bag-1.jpg' => '#8B4513',
    'leather-bag-2.jpg' => '#654321',
    'leather-wallet-1.jpg' => '#A0522D',
    'leather-belt-1.jpg' => '#D2691E'
];

// Function to create placeholder image
function createPlaceholder($filename, $color) {
    $width = 800;
    $height = 600;
    
    $image = imagecreatetruecolor($width, $height);
    
    // Convert hex color to RGB
    $color = ltrim($color, '#');
    $r = hexdec(substr($color, 0, 2));
    $g = hexdec(substr($color, 2, 2));
    $b = hexdec(substr($color, 4, 2));
    
    $bgColor = imagecolorallocate($image, $r, $g, $b);
    $textColor = imagecolorallocate($image, 255, 255, 255);
    
    // Fill background
    imagefill($image, 0, 0, $bgColor);
    
    // Add text
    $text = basename($filename, '.jpg');
    $text = str_replace('-', ' ', $text);
    $text = ucwords($text);
    
    $fontSize = 40;
    $font = 5; // Use built-in font
    
    // Center text
    $textWidth = imagefontwidth($font) * strlen($text);
    $textHeight = imagefontheight($font);
    $x = ($width - $textWidth) / 2;
    $y = ($height - $textHeight) / 2;
    
    imagestring($image, $font, $x, $y, $text, $textColor);
    
    // Save image
    imagejpeg($image, $filename, 90);
    imagedestroy($image);
}

// Generate category placeholders
foreach ($categories as $category => $color) {
    $filename = 'images/categories/' . strtolower(str_replace(' ', '-', $category)) . '.jpg';
    createPlaceholder($filename, $color);
}

// Generate product placeholders
foreach ($products as $filename => $color) {
    createPlaceholder('images/products/' . $filename, $color);
}

// Create hero and custom orders background images
createPlaceholder('images/hero-bg.jpg', '#8B4513');
createPlaceholder('images/custom-orders-bg.jpg', '#654321');

echo "Placeholder images generated successfully!";
?> 
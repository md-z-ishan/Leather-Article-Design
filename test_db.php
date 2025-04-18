<?php
require_once 'config/db.php';

try {
    // Test database connection
    echo "Testing database connection...<br>";
    $pdo->query("SELECT 1");
    echo "✓ Database connection successful<br><br>";

    // Test custom_orders table
    echo "Testing custom_orders table...<br>";
    $stmt = $pdo->query("SHOW COLUMNS FROM custom_orders");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Table columns:<br>";
    foreach ($columns as $column) {
        echo "✓ $column<br>";
    }
    echo "<br>";

    // Test inserting a sample order
    echo "Testing order insertion...<br>";
    $test_order = [
        'user_id' => 1,
        'product_type' => 'Test Product',
        'description' => 'Test Description',
        'dimensions' => '10x10x10',
        'color' => 'Brown',
        'material' => 'Leather',
        'budget' => '$100',
        'deadline' => date('Y-m-d'),
        'status' => 'pending'
    ];

    $sql = "INSERT INTO custom_orders (user_id, product_type, description, dimensions, color, material, budget, deadline, status) 
            VALUES (:user_id, :product_type, :description, :dimensions, :color, :material, :budget, :deadline, :status)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($test_order);
    
    echo "✓ Test order inserted successfully<br>";
    echo "Order ID: " . $pdo->lastInsertId() . "<br><br>";

    // Clean up test data
    $pdo->query("DELETE FROM custom_orders WHERE product_type = 'Test Product'");
    echo "✓ Test data cleaned up<br>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 
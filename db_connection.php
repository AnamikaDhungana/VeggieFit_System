<?php
// Database connection settings
$host = "localhost";
$dbname = "personalized_meal_planner"; // your database name
$username = "root";
$password = "";

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Set PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   
    // echo "Database connected successfully!";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>



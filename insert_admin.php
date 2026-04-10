<?php
include 'db_connection.php';

$email = "masteradmin985@gmail.com";   
$password = "@$12Admin34#@";   // admin password
$role = "SuperAdmin";

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Check if admin already exists
$stmt = $conn->prepare("SELECT * FROM admin WHERE email = :email");
$stmt->execute([':email' => $email]);

if ($stmt->rowCount() > 0) {
    echo "Admin already exists!";
} else {
    $stmt = $conn->prepare("INSERT INTO admin (email, password, role) VALUES (:email, :password, :role)");
    $stmt->execute([
        ':email' => $email,
        ':password' => $hashedPassword,
        ':role' => $role
    ]);
    echo "First admin inserted successfully!";
}

$conn = null;
?>

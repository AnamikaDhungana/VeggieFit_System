<?php
session_start();
require __DIR__ . '/../db_connection.php'; // Correct path

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Check password confirmation
    if($password !== $confirm) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit;
    }

    // Fetch user by token
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = :token LIMIT 1");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if token exists and not expired
    if(!$user || strtotime($user['reset_token_expiry']) < time()) {
        die("Invalid or expired token.");
    }

    // Hash new password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Update password and clear reset token/expiry
    $stmt = $conn->prepare("
        UPDATE users 
        SET password = :password, reset_token = NULL, reset_token_expiry = NULL 
        WHERE user_id = :id
    ");
    $stmt->execute([
        ':password' => $hashed,
        ':id' => $user['user_id']
    ]);

    $_SESSION['success'] = "Password updated successfully. You can login now.";
    header("Location: login.php");
    exit;
}
?>

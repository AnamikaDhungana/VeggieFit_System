<?php
session_start();
include __DIR__ . "/../db_connection.php"; // PDO $conn

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer/src/Exception.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: forgot_password.php");
    exit;
}

// Get and validate email
$email = strtolower(trim($_POST['email'] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['reset_error'] = "Please enter a valid email address.";
    header("Location: forgot_password.php");
    exit;
}

// Always show generic message
$_SESSION['reset_success'] = "If the email exists in our system, a password reset link has been sent to your Gmail address.";

// Lookup user
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // Generate token + expiry
    $token = bin2hex(random_bytes(32));
    $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // Save token in DB
    $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE user_id = ?");
    $update->execute([$token, $expiry, $user['user_id']]);

    // Build reset link
    $resetLink = "http://localhost/Nutrition_System/Login/reset_password.php?token=" . urlencode($token);

    // Send email
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'example@gmail.com'; // Your Gmail
        $mail->Password = 'example123@';    // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('example@gmail.com', 'VeggieFit Support');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'VeggieFit — Password Reset Request';
        $mail->Body = "
            <p>Hello,</p>
            <p>We received a request to reset your VeggieFit password. Click the button below to reset:</p>
            <p><a href='{$resetLink}' style='padding:10px 16px;background:#8c34d6;color:#fff;border-radius:6px;text-decoration:none;'>Reset Password</a></p>
            <p>This link will expire in 1 hour.</p>
        ";
        $mail->AltBody = "Reset your VeggieFit password using this link: {$resetLink}";

        $mail->send();
    } catch (Exception $e) {
        // Only log error, do not reveal to user
        error_log("Password reset email error for {$email}: " . $mail->ErrorInfo);
    }
}

// Redirect back with generic message
header("Location: forgot_password.php");
exit;

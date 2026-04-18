<?php
session_start();
include '../db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $identifier = trim($_POST['username']); // email for admin, email/username for user
    $password = trim($_POST['password']);

    if (empty($identifier) || empty($password)) {
        header("Location: login.php?error=Please fill all fields");
        exit();
    }

    try {
        /* 1️⃣ CHECK ADMIN LOGIN (EMAIL) */
        $adminStmt = $conn->prepare("
            SELECT * FROM admin 
            WHERE email = :email 
            LIMIT 1
        ");
        $adminStmt->execute([':email' => $identifier]);
        $admin = $adminStmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['email'] = $admin['email'];
            $_SESSION['role'] = $admin['role']; // SuperAdmin or Manager

            header("Location: ../AdminPage/admin_dashboard.php");
            exit();
        }

        /* 2️⃣ CHECK NORMAL USER LOGIN */
        $stmt = $conn->prepare("
            SELECT * FROM users 
            WHERE email = :identifier 
               OR username = :identifier 
            LIMIT 1
        ");
        $stmt->execute([':identifier' => $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = 'user';

                $_SESSION['login_success'] = true;
                header("Location: /VeggieFit_System/dashboard.php");
                exit();
            } else {
                header("Location: login.php?error=Incorrect password");
                exit();
            }
        } else {
            header("Location: login.php?error=No account found with these credentials");
            exit();
        }

    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }

} else {
    header("Location: login.php");
    exit();
}
?>
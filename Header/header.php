<?php
// Start session if needed
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutrition & Meal Planner</title>
    <link rel="stylesheet" href="/nutrition_system/header/header.css">
</head>
<body>

<header class="main-header">
    <div class="logo">
        <img src="/nutrition_system/Web_image/logo.jpeg" alt="Logo">
        <h2>VeggieFit</h2>
    </div>

    <nav class="navbar">
        <ul>
            <li><a href="/nutrition_system/home_page.php">Home</a></li>
            <li><a href="/nutrition_system/User/about_us.php">About Us</a></li>

            <?php
                if (isset($_SESSION['username'])) {
                    echo '<li><a href="/nutrition_system/Login/logout.php">Log Out</a></li>';
                } else {
                    echo '<li><a href="/nutrition_system/Login/login.php">Login</a></li>';
                }
            ?>
        </ul>
    </nav>
</header>

</body>
</html>

<?php
session_start();
require_once "../db_connection.php";

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['SuperAdmin', 'admin'])) {
    header("Location: ../login.php");
    exit();
}

$adminName = $_SESSION['username'] ?? 'Admin';

// Main stats
$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalFoods = $conn->query("SELECT COUNT(*) FROM foods")->fetchColumn();
$totalMeals = $conn->query("SELECT COUNT(*) FROM meal_items")->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | VeggieFit</title>

<style>
:root{
    --primary:#2d6a4f;
    --dark:#1b4332;
    --bg:#f1f8f5;
    --white:#ffffff;
    --muted:#d8f3dc;
}

html, body{
    height:100%;
}

*{
    box-sizing:border-box;
    font-family:'Times New Roman', Times, serif;
}

body{
    margin:0;
    min-height:100vh;
    display:flex;
    background:var(--bg);
}

/* SIDEBAR */
.sidebar{
    width:260px;
    height:100vh;
    background:var(--primary);
    color:#fff;
    padding:25px 20px;
    display:flex;
    flex-direction:column;
}

.sidebar h2{
    text-align:center;
    margin-bottom:25px;
}

.menu{
    flex:1;
}

.menu a{
    display:block;
    padding:12px 14px;
    margin:6px 0;
    color:#fff;
    text-decoration:none;
    border-radius:6px;
    font-size:15px;
}

.menu a:hover{
    background:var(--dark);
}

.menu .section{
    margin-top:18px;
    font-weight:bold;
    font-size:14px;
    color:var(--muted);
}

.sub{
    margin-left:15px;
    font-size:14px;
    color:#e9f5ef;
}

/* Logout */
.logout-btn{
    margin-top:20px;
    background:#d00000;
    color:#fff;
    border:none;
    padding:10px;
    border-radius:10px;
    cursor:pointer;
}

/*  MAIN */
.main{
    flex:1;
    padding:40px;
    overflow-y:auto;
}

/* Banner */
.banner{
    background:linear-gradient(135deg,#5eaaa8,#8ac4d0);
    color:#fff;
    padding:35px;
    border-radius:20px;
    margin-bottom:35px;
}

.banner h1{
    margin:0 0 10px;
}

/* Cards */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:25px;
}

.card{
    background:#fff;
    padding:30px;
    border-radius:18px;
    box-shadow:0 10px 25px rgba(0,0,0,0.12);
}

.card h3{
    margin:0 0 10px;
}

.card p{
    font-size:30px;
    font-weight:bold;
    color:var(--primary);
}

/* Actions */
.actions{
    margin-top:45px;
}

.actions a{
    display:inline-block;
    padding:14px 26px;
    margin-right:15px;
    background:var(--primary);
    color:#fff;
    border-radius:10px;
    text-decoration:none;
    font-size:15px;
}

.actions a:hover{
    background:var(--dark);
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div>
        <h2>VeggieFit Admin</h2>

        <div class="menu">
            <a href="admin_dashboard.php">🏠 Dashboard</a>
            <a href="manage_users.php">👥 Manage Users</a>

            <div class="section">🥕 Food Database</div>
            <a class="sub" href="add_food.php">➤ Add Food</a>
            <a class="sub" href="manage_foods.php">➤ Manage Foods</a>

            <div class="section">📊 Meal Data</div>
            <a class="sub" href="manage_meals.php">➤ View Meals</a>
             <div class="section">📈 User Progress</div>
            <a class="sub" href="./admin_progress.php">➤ Track Progress</a>
        </div>
    </div>

    <form action="logout.php" method="POST">
        <button class="logout-btn">Logout</button>
    </form>
</div>

<!-- MAIN CONTENT -->
<div class="main">

    <div class="banner">
        <h1>Welcome, <?= htmlspecialchars($adminName) ?> 👋</h1>
        <p>Manage vegetarian foods and monitor auto-generated meal plans.</p>
    </div>

    <div class="cards">
        <div class="card">
            <h3>Total Users</h3>
            <p><?= $totalUsers ?></p>
        </div>

        <div class="card">
            <h3>Foods Available</h3>
            <p><?= $totalFoods ?></p>
        </div>

        <div class="card">
            <h3>Meals Generated</h3>
            <p><?= $totalMeals ?></p>
        </div>
    </div>

    <div class="actions">
        <a href="add_food.php">➕ Add New Food</a>
        <a href="manage_foods.php">📋 Manage Foods</a>
    </div>

</div>

</body>
</html>
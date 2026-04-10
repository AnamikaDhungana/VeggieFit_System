<?php
session_start();
require_once "../db_connection.php";

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['SuperAdmin', 'admin'])) {
    header("Location: ../login.php");
    exit();
}

/* FETCH FOODS */
$stmt = $conn->prepare("SELECT * FROM foods ORDER BY food_id DESC");
$stmt->execute();
$foods = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Foods | VeggieFit</title>
<style>
body {
    margin: 0;
    font-family: 'Poppins', 'Times New Roman', serif;
    background: #f1f8f5;
}

/* CONTAINER LAYOUT */
.container {
    display: flex;
    min-height: 100vh;
}

/*  SIDEBAR */
#admin-sidebar,
.sidebar {
    width: 260px;
    min-height: 100vh;
    background: #2d6a4f;
    color: #fff;
    padding: 25px 20px;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 25px;
    font-size: 22px;
}

/*  MENU  */
.menu a {
    display: block;
    padding: 10px 12px;
    margin: 6px 0;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s;
}

.menu a:hover {
    background: #1b4332;
}

.menu .section {
    margin-top: 18px;
    font-weight: bold;
    font-size: 14px;
    color: #d8f3dc;
}

.sub {
    margin-left: 15px;
    font-size: 14px;
}

/*  LOGOUT BUTTON  */
.logout-btn {
    margin-top: 20px;
    background: #d00000;
    color: #fff;
    border: none;
    padding: 10px;
    border-radius: 10px;
    cursor: pointer;
}

/* MAIN CONTENT */
.main {
    flex: 1;
    padding: 30px 40px;
    display: flex;
    flex-direction: column;
}

.main h2 {
    color: #2d6a4f;
    margin-bottom: 25px;
    font-size: 28px;
}

/* ACTION BUTTONS (TOP) */
.actions {
    margin-bottom: 20px;
    display: flex;
    gap: 12px;
}

.actions a .btn {
    padding: 10px 18px;
    font-size: 14px;
    border-radius: 8px;
    font-weight: 500;
}

/*  TABLE CONTAINER */
.table-container {
    background: #fff;
    border-radius: 12px;
    overflow-x: auto;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
    padding: 15px;
}

table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
}

th,
td {
    padding: 12px 8px;
    text-align: center;
    word-wrap: break-word;
}

th {
    background: #2d6a4f;
    color: #fff;
    font-weight: 600;
    font-size: 14px;
}

tr:nth-child(even) {
    background: #f7fbf9;
}

tr:hover {
    background: #eef6f2;
}

th:last-child,
td:last-child {
    width: 180px;
}

.food-img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #ccc;
}

/* TABLE ACTION BUTTONS */
.action-buttons {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    flex-wrap: nowrap;
}

.btn {
    border: none;
    cursor: pointer;
    border-radius: 8px;
    color: #fff;
    font-weight: 500;
    font-size: 13px;
    padding: 6px 14px;
    white-space: nowrap;
}

td {
    vertical-align: middle;
}

.edit-btn {
    background: #1f6ed4;
}

.delete-btn {
    background: #d00000;
}

.btn:hover {
    opacity: 0.85;
}

/* RESPONSIVE DESIGN */
@media (max-width: 900px) {
    .container {
        flex-direction: column;
    }

    #admin-sidebar,
    .sidebar {
        width: 100%;
        min-height: auto;
    }

    .main {
        padding: 20px;
    }

    table {
        font-size: 12px;
    }

    .food-img {
        width: 40px;
        height: 40px;
    }
}
</style>
</head>
<body>

<div class="container">

    <!-- SIDEBAR -->
    <div id="admin-sidebar"></div>

    <!-- MAIN CONTENT -->
    <div class="main">
        <h2>Manage Foods</h2>

        <div class="actions">
            <a href="add_food.php"><button class="btn" style="background:#2b7a2b;">+ Add New Food</button></a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Food Name</th>
                        <th>Category</th>
                        <th>State</th>
                        <th>Serving</th>
                        <th>Calories</th>
                        <th>Protein</th>
                        <th>Carbs</th>
                        <th>Fats</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($foods as $food): ?>
                    <tr>
                        <td><?= $food['food_id'] ?></td>
                        <td>
                            <?php if (!empty($food['image']) && file_exists($food['image'])): ?>
                                <img src="<?= $food['image'] ?>" class="food-img" alt="Food Image">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/50?text=No" class="food-img" alt="No Image">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($food['food_name']) ?></td>
                        <td><?= htmlspecialchars($food['category']) ?></td>
                        <td><?= $food['food_state'] ?></td>
                        <td><?= $food['serving_size'] ?></td>
                        <td><?= $food['calories'] ?></td>
                        <td><?= $food['protein'] ?></td>
                        <td><?= $food['carbs'] ?></td>
                        <td><?= $food['fats'] ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_food.php?id=<?= $food['food_id'] ?>">
                                    <button class="btn edit-btn">Edit</button>
                                </a>
                                <a href="delete_food.php?id=<?= $food['food_id'] ?>" onclick="return confirm('Are you sure you want to delete this food item?')">
                                    <button class="btn delete-btn">Delete</button>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- SIDEBAR LOADER JS -->
<script>
fetch("admin_dashboard.php")
    .then(res => res.text())
    .then(html => {
        const temp = document.createElement("div");
        temp.innerHTML = html;
        const sidebar = temp.querySelector(".sidebar");
        if (sidebar) {
            document.getElementById("admin-sidebar").replaceWith(sidebar);
        }
    });
</script>

</body>
</html>

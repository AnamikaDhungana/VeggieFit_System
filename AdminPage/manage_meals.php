<?php
session_start();
require_once "../db_connection.php";

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['SuperAdmin','admin'])) {
    header("Location: ../login.php");
    exit();
}

/* DELETE MEAL PLAN */
if (isset($_GET['delete'])) {
    $plan_id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM meal_plans WHERE plan_id = ?");
    $stmt->execute([$plan_id]);
    header("Location: manage_meals.php");
    exit();
}

/* FETCH MEAL PLANS */
$sql = "
SELECT 
    mp.plan_id,
    mp.date,
    u.username,
    mp.total_calories,
    mp.total_protein,
    mp.total_carbs,
    mp.total_fats
FROM meal_plans mp
JOIN users u ON mp.user_id = u.user_id
ORDER BY mp.date DESC
";
$plans = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Meals | VeggieFit</title>
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

/* SIDEBAR */
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

/* LOGOUT BUTTON  */
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

/* TABLE CONTAINER */
.table-container {
    background: #fff;
    border-radius: 12px;
    overflow-x: auto;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
    padding: 15px;
}

/* TABLE STYLES */
table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 12px 8px;
    text-align: left;
    vertical-align: top;
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

/* ACTION BUTTONS */
.actions a {
    background: #d00000;
    color: white;
    padding: 6px 12px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
    display: inline-block;
}

.actions a:hover {
    opacity: 0.85;
}

/* MEAL ITEMS & BADGE */
.meal-items {
    font-size: 14px;
    color: #333;
    margin-top: 8px;
}

.badge {
    background: #e9f5ef;
    padding: 5px 10px;
    border-radius: 6px;
    display: inline-block;
    margin-bottom: 5px;
    color: #2d6a4f;
    font-size: 13px;
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
}
</style>
</head>
<body>

<div class="container">
    <!-- SIDEBAR -->
    <div id="admin-sidebar"></div>

    <!-- MAIN CONTENT -->
    <div class="main">
        <h2>🍽 User Meal Plans</h2>

        <div class="table-container">
            <table>
                <tr>
                    <th>User</th>
                    <th>Date</th>
                    <th>Meals</th>
                    <th>Totals</th>
                    <th>Action</th>
                </tr>
                <?php if (empty($plans)): ?>
                <tr><td colspan="5">No meal plans found.</td></tr>
                <?php endif; ?>
                
                <?php foreach ($plans as $plan): ?>
                <tr>
                    <td><?= htmlspecialchars($plan['username']) ?></td>
                    <td><?= $plan['date'] ?></td>
                    <td>
                        <?php
                        $itemsStmt = $conn->prepare("
                            SELECT mi.meal_type, f.food_name, f.protein, f.carbs, f.fats, mi.quantity
                            FROM meal_items mi
                            JOIN foods f ON mi.food_id = f.food_id
                            WHERE mi.plan_id = ?
                        ");
                        $itemsStmt->execute([$plan['plan_id']]);
                        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($items as $item):
                        ?>
                        <div class="meal-items">
                            <strong><?= $item['meal_type'] ?>:</strong>
                            <?= $item['food_name'] ?> (<?= $item['quantity'] ?>) 
                            — Protein: <?= $item['protein'] ?>g, Carbs: <?= $item['carbs'] ?>g, Fats: <?= $item['fats'] ?>g
                        </div>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <span class="badge"><?= $plan['total_calories'] ?> kcal</span><br>
                        <span class="badge">Protein: <?= $plan['total_protein'] ?>g</span><br>
                        Carbs: <?= $plan['total_carbs'] ?>g | Fats: <?= $plan['total_fats'] ?>g
                    </td>
                    <td class="actions">
                        <a href="?delete=<?= $plan['plan_id'] ?>" onclick="return confirm('Delete this meal plan?')">
                           Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>

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

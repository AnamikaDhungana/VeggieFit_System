<?php
session_start();
require_once "db_connection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user info
$userStmt = $conn->prepare("SELECT username, created_at FROM users WHERE user_id = ?");
$userStmt->execute([$user_id]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// DATE LOGIC (IMPROVED LIKE ADMIN PAGE) //
$user_join_date = date('Y-m-d', strtotime($user['created_at']));
$today = date('Y-m-d');

// Default = last 30 days BUT not before join date
$default_start = date('Y-m-d', strtotime('-30 days'));
if (strtotime($default_start) < strtotime($user_join_date)) {
    $default_start = $user_join_date;
}

// Input dates
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date'])
    ? $_GET['start_date']
    : $default_start;

$end_date = isset($_GET['end_date']) && !empty($_GET['end_date'])
    ? $_GET['end_date']
    : $today;

// Safety rules
if (strtotime($start_date) < strtotime($user_join_date)) {
    $start_date = $user_join_date;
}

if (strtotime($end_date) > strtotime($today)) {
    $end_date = $today;
}

if (strtotime($start_date) > strtotime($end_date)) {
    $start_date = $end_date;
}

// Fetch meal plans history
$historyStmt = $conn->prepare("
    SELECT mp.date, mp.total_calories, mp.total_protein, mp.total_carbs, mp.total_fats,
           GROUP_CONCAT(DISTINCT mi.meal_type ORDER BY FIELD(mi.meal_type,'Breakfast','Lunch','Dinner','Snack') SEPARATOR ', ') as meals
    FROM meal_plans mp
    LEFT JOIN meal_items mi ON mp.plan_id = mi.plan_id
    WHERE mp.user_id = ? AND mp.date BETWEEN ? AND ?
    GROUP BY mp.plan_id
    ORDER BY mp.date DESC
");
$historyStmt->execute([$user_id, $start_date, $end_date]);
$history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch specific meal plan details
$selected_date = isset($_GET['view_date']) ? $_GET['view_date'] : null;
$mealDetails = [];

if ($selected_date) {
    $detailStmt = $conn->prepare("
        SELECT mp.date, mi.meal_type, mi.quantity, 
               f.food_name, f.calories, f.protein, f.carbs, f.fats
        FROM meal_plans mp
        JOIN meal_items mi ON mp.plan_id = mi.plan_id
        JOIN foods f ON f.food_id = mi.food_id
        WHERE mp.user_id = ? AND mp.date = ?
        ORDER BY FIELD(mi.meal_type,'Breakfast','Lunch','Dinner','Snack'), f.food_name
    ");
    $detailStmt->execute([$user_id, $selected_date]);
    $mealDetails = $detailStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal History | VeggieFit</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
      :root {
    --primary: #2d6a4f;
    --primary-light: #40916c;
    --accent: #8c34d6;
    --light: #f1f8f5;
    --dark: #1b4332;
    --gray: #6c757d;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Times New Roman';
    background: var(--light);
    color: #333;
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.header {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: white;
    padding: 25px 30px;
    border-radius: 15px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.header h1 {
    font-size: 28px;
    margin-bottom: 5px;
}

.header p {
    opacity: 0.9;
    font-size: 15px;
}

/* BUTTON */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: 0.3s;
    border: none;
    font-size: 14px;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-light);
    transform: translateY(-2px);
}

.btn-secondary {
    background: white;
    color: var(--primary);
    border: 2px solid var(--primary);
}

.btn-secondary:hover {
    background: var(--primary);
    color: white;
}

/* CARD */
.card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    margin-bottom: 25px;
}

.card-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* == FILTER BAR FIX (MAIN CHANGE) ==*/

.filter-form {
    display: flex;
    gap: 12px;
    align-items: flex-end;
    justify-content: flex-start;
    flex-wrap: wrap;
}

/* each input group */
.filter-form .form-group {
    display: flex;
    flex-direction: column;
    min-width: 180px;
}

/* inputs */
.form-group label {
    font-weight: 500;
    margin-bottom: 5px;
    color: var(--dark);
}

.form-group input {
    height: 42px;
    padding: 0 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
}

/* BUTTON FIX */
.filter-form .form-group:last-child {
    min-width: auto;
    display: flex;
    align-items: flex-end;
}

.filter-form .form-group:last-child button {
    height: 42px;
    padding: 0 18px;
    white-space: nowrap;
}

/* TABLE */
.history-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.history-table th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: var(--dark);
    border-bottom: 2px solid #eee;
}

.history-table td {
    padding: 15px;
    border-bottom: 1px solid #eee;
}

.history-table tr:hover {
    background: #f8f9fa;
}

.date-cell {
    font-weight: 500;
    color: var(--primary);
}

.view-btn {
    background: var(--accent);
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
}

.view-btn:hover {
    background: #7a2db8;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .header {
        flex-direction: column;
        text-align: center;
    }

    .filter-form {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-form .form-group {
        width: 100%;
    }

    .history-table {
        display: block;
        overflow-x: auto;
    }
}
    </style>
</head>

<body>
<div class="container">

    <div class="header">
        <div>
            <h1>Meal History</h1>
            <p>View your past meal plans and nutrition details</p>
        </div>
        <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <!-- FILTER -->
    <div class="card">
        <div class="card-title">📅 Filter History</div>

        <form method="GET" class="filter-form">
            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date"
                    min="<?= $user_join_date ?>"
                    max="<?= $today ?>"
                    value="<?= $start_date ?>">
            </div>

            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date"
                    min="<?= $user_join_date ?>"
                    max="<?= $today ?>"
                    value="<?= $end_date ?>">
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Apply Filter</button>
            </div>
        </form>
    </div>

    <!-- HISTORY -->
    <div class="card">
        <div class="card-title">📋 Meal Plans History</div>

        <?php if (empty($history)): ?>
            <p>No meal plans found for selected dates.</p>
        <?php else: ?>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Calories</th>
                        <th>Protein</th>
                        <th>Carbs</th>
                        <th>Fats</th>
                        <th>Meals</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($history as $record): ?>
                        <tr>
                            <td class="date-cell"><?= date('M j, Y', strtotime($record['date'])) ?></td>
                            <td><?= $record['total_calories'] ?></td>
                            <td><?= $record['total_protein'] ?>g</td>
                            <td><?= $record['total_carbs'] ?>g</td>
                            <td><?= $record['total_fats'] ?>g</td>
                            <td><?= $record['meals'] ?></td>
                            <td>
                                <a class="view-btn"
                                   href="?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&view_date=<?= $record['date'] ?>">
                                   View Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </div>

</div>
</body>
</html>
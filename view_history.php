<?php
session_start();
require_once "db_connection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user info
$userStmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
$userStmt->execute([$user_id]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// Get date range from URL or default to last 30 days
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

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
            font-family: 'Poppins', sans-serif;
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
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
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
        
        /* Filter Form */
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 500;
            margin-bottom: 5px;
            color: var(--dark);
        }
        
        .form-group input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }
        
        /* History Table */
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
            transition: background 0.3s ease;
        }
        
        .view-btn:hover {
            background: #7a2db8;
        }
        
        /* Meal Details */
        .meal-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 15px;
            border-left: 4px solid var(--accent);
        }
        
        .meal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .meal-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 16px;
        }
        
        .food-list {
            list-style: none;
        }
        
        .food-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }
        
        .food-list li:last-child {
            border-bottom: none;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray);
        }
        
        .empty-state p {
            margin-top: 10px;
            margin-bottom: 20px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .history-table {
                display: block;
                overflow-x: auto;
            }
            
            .filter-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h1>Meal History</h1>
                <p>View your past meal plans and nutrition details</p>
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="card">
            <div class="card-title">📅 Filter History</div>
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?= $start_date ?>" required>
                </div>
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?= $end_date ?>" required>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <button type="submit" class="btn btn-primary" style="align-self: flex-end;">Apply Filter</button>
                </div>
            </form>
        </div>
        
        <?php if ($selected_date && $mealDetails): ?>
            <!-- Meal Details -->
            <div class="card">
                <div class="card-title">
                    🍽 Meal Plan for <?= date('F j, Y', strtotime($selected_date)) ?>
                    <a href="view_history.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" class="btn btn-secondary" style="margin-left: auto; font-size: 14px;">
                        ← Back to List
                    </a>
                </div>
                
                <?php 
                // Group meals by type
                $groupedMeals = [];
                foreach ($mealDetails as $item) {
                    $groupedMeals[$item['meal_type']][] = $item;
                }
                
                $mealTypes = ['Breakfast', 'Lunch', 'Dinner', 'Snack'];
                
                foreach ($mealTypes as $mealType):
                    if (isset($groupedMeals[$mealType])): 
                ?>
                    <div class="meal-item">
                        <div class="meal-header">
                            <div class="meal-name"><?= $mealType ?></div>
                        </div>
                        <ul class="food-list">
                            <?php foreach ($groupedMeals[$mealType] as $item): ?>
                                <li>
                                    <span><?= htmlspecialchars($item['food_name']) ?></span>
                                    <span style="color: var(--gray); font-size: 14px;">
                                        <?= $item['quantity'] ?> serving • <?= $item['calories'] ?> kcal
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; endforeach; ?>
            </div>
            
        <?php else: ?>
            <!-- History List -->
            <div class="card">
                <div class="card-title">📋 Meal Plans History</div>
                
                <?php if (empty($history)): ?>
                    <div class="empty-state">
                        <p>No meal plans found for the selected period.</p>
                        <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                    </div>
                <?php else: ?>
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Total Calories</th>
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
                                    <td><?= number_format($record['total_calories']) ?> kcal</td>
                                    <td><?= number_format($record['total_protein'], 1) ?>g</td>
                                    <td><?= number_format($record['total_carbs'], 1) ?>g</td>
                                    <td><?= number_format($record['total_fats'], 1) ?>g</td>
                                    <td><?= $record['meals'] ?: 'No meals recorded' ?></td>
                                    <td>
                                        <a href="view_history.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&view_date=<?= $record['date'] ?>" class="view-btn">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
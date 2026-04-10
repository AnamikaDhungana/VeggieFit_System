<?php
session_start();
require_once "db_connection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: Login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* FETCH USER DATA */
$userStmt = $conn->prepare("
    SELECT username, email, gender, age, height_cm, weight_kg,
           diet_preference, activity_level,
           reminders_enabled,
           breakfast_time, snack_time, lunch_time, dinner_time
    FROM users WHERE user_id = ?
");
$userStmt->execute([$user_id]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: auth/login.php");
    exit();
}

/* CALCULATE METRICS */
require_once "meal_algorithm.php";

$bmi = calculateBMI($user['weight_kg'], $user['height_cm']);
$bmiCategory = match (true) {
    $bmi < 18.5 => 'Underweight',
    $bmi < 25   => 'Normal',
    $bmi < 30   => 'Overweight',
    default     => 'Obese'
};

$bmr = calculateBMR($user['gender'], $user['weight_kg'], $user['height_cm'], $user['age']);
$tdee = calculateTDEE($bmr, $user['activity_level'] ?? 'Moderate');
$calorieTarget = max($tdee - 500, 1200);
$proteinTarget = ($calorieTarget * 0.20) / 4;

$mealSplit = [
    'Breakfast' => 0.25,
    'Lunch'     => 0.35,
    'Dinner'    => 0.30,
    'Snack'     => 0.10
];

/* TODAY'S MEAL PLAN */
$today = date('Y-m-d');
$planStmt = $conn->prepare("
    SELECT plan_id, total_calories, total_protein, total_carbs, total_fats 
    FROM meal_plans 
    WHERE user_id = ? AND date = ?
");
$planStmt->execute([$user_id, $today]);
$plan = $planStmt->fetch(PDO::FETCH_ASSOC);

$meals = [];
$totalConsumed = ['calories' => 0, 'protein' => 0, 'carbs' => 0, 'fats' => 0];

if ($plan) {
    $mealStmt = $conn->prepare("
        SELECT mi.meal_type, mi.quantity, 
               f.food_name, f.calories, f.protein, f.carbs, f.fats, f.recipe_details
        FROM meal_items mi
        JOIN foods f ON f.food_id = mi.food_id
        WHERE mi.plan_id = ?
        ORDER BY FIELD(mi.meal_type,'Breakfast','Lunch','Dinner','Snack')
    ");
    $mealStmt->execute([$plan['plan_id']]);
    
    while ($row = $mealStmt->fetch(PDO::FETCH_ASSOC)) {
        $meals[$row['meal_type']][] = $row;
        $totalConsumed['calories'] += $row['calories'] * $row['quantity'];
        $totalConsumed['protein'] += $row['protein'] * $row['quantity'];
        $totalConsumed['carbs'] += $row['carbs'] * $row['quantity'];
        $totalConsumed['fats'] += $row['fats'] * $row['quantity'];
    }
}

/* CHECK TODAY LOG */
$checkStmt = $conn->prepare("
    SELECT COUNT(*) FROM progress_tracking
    WHERE user_id = ? AND date = ?
");
$checkStmt->execute([$user_id, $today]);
$alreadyLoggedToday = $checkStmt->fetchColumn() > 0;

/* SUCCESS MESSAGE */
$successMsg = '';
if (isset($_SESSION['login_success'])) {
    $successMsg = "Welcome back, " . htmlspecialchars($user['username']) . "!";
    unset($_SESSION['login_success']);
}
if (isset($_SESSION['weight_logged'])) {
    $successMsg = $_SESSION['weight_logged'];
    unset($_SESSION['weight_logged']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | VeggieFit</title>
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

body {
    font-family: 'Times New Roman';
    background: var(--light);
    color: #333;
    margin: 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* DASHBOARD HEADER */
.dashboard-header {
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

.user-info h1 {
    font-size: 28px;
    margin-bottom: 5px;
}

.user-info p {
    opacity: 0.9;
    font-size: 15px;
}

/* METRICS SECTION */
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.metric-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    text-align: center;
    transition: transform 0.3s ease;
}

.metric-card:hover {
    transform: translateY(-3px);
}

.metric-value {
    font-size: 32px;
    font-weight: 700;
    color: var(--primary);
    margin: 10px 0;
}

.metric-label {
    font-size: 14px;
    color: var(--gray);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* BMI BADGES */
.bmi-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 8px;
}

.bmi-normal {
    background: #d4edda;
    color: #155724;
}

.bmi-underweight {
    background: #fff3cd;
    color: #856404;
}

.bmi-overweight {
    background: #f8d7da;
    color: #721c24;
}

/* CARDS */
.card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    margin: 0 auto 25px auto;
    max-width: 900px;
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

/* MEAL SECTION */
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
}

.meal-target {
    background: var(--accent);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
}

/* FOOD LIST */
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

.food-list li small {
    color: #555;
    font-size: 12px;
    display: block;
    margin-top: 2px;
}

/* BUTTONS */
.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 20px;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    border: none;
    font-size: 14px;
    flex: 1;
    text-align: center;
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

/* EMPTY STATE */
.empty-state {
    text-align: center;
    padding: 30px 20px;
    color: var(--gray);
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .btn {
        flex: 100%;
    }
}
</style>
</head>
<body>
<div class="container">
<?php if ($successMsg): ?>
    <div class="alert alert-success">✓ <?= htmlspecialchars($successMsg) ?></div>
<?php endif; ?>

<div class="dashboard-header">
    <div class="user-info">
        <h1>Hello, <?= htmlspecialchars($user['username']) ?>! 👋</h1>
        <p><?= date('l, F j, Y') ?> • Daily Target: <?= number_format($calorieTarget) ?> kcal | Protein Target: <?= number_format($proteinTarget) ?>g</p>
    </div>
    <div>
        <a href="profile.php" class="btn btn-secondary">Edit Profile</a>
        <a href="Login/logout.php" class="btn btn-primary">Logout</a>
    </div>
</div>

<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-label">Body Mass Index</div>
        <div class="metric-value"><?= number_format($bmi, 1) ?></div>
        <span class="bmi-badge <?= 'bmi-' . strtolower($bmiCategory) ?>"><?= $bmiCategory ?></span>
    </div>
    <div class="metric-card">
        <div class="metric-label">Basal Metabolic Rate</div>
        <div class="metric-value"><?= number_format($bmr) ?></div>
        <div class="metric-label">kcal/day</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Daily Calorie Target</div>
        <div class="metric-value"><?= number_format($calorieTarget) ?></div>
        <div class="metric-label">kcal</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Protein Consumed</div>
        <div class="metric-value"><?= number_format($totalConsumed['protein'], 1) ?>g</div>
        <div class="metric-label">Target: <?= number_format($proteinTarget) ?>g</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Current Weight</div>
        <div class="metric-value"><?= number_format($user['weight_kg'], 1) ?></div>
        <div class="metric-label">kg</div>
    </div>
</div>

<div class="card">
    <div class="card-title">🍽 Today's Meal Plan</div>

    <?php if ($bmiCategory === 'Underweight'): ?>
        <div class="alert" style="background:#fff3cd;color:#856404;">⚠️ You are underweight. Please consult a healthcare professional.</div>
    <?php elseif (!$plan): ?>
        <div class="empty-state">
            <p>No meal plan for today.</p>
            <a href="generate_plan.php" class="btn btn-accent" style="margin-top:15px;">Generate Today's Plan</a>
        </div>
    <?php else: ?>
        <?php foreach ($mealSplit as $mealType => $ratio): 
            $targetCalories = $calorieTarget * $ratio;
            $targetProtein = $proteinTarget * $ratio;
            $mealItems = $meals[$mealType] ?? [];
            $mealCalories = $mealProtein = 0;
            foreach ($mealItems as $item) {
                $mealCalories += $item['calories'] * $item['quantity'];
                $mealProtein += $item['protein'] * $item['quantity'];
            }
        ?>
            <div class="meal-item">
                <div class="meal-header">
                    <div class="meal-name"><?= $mealType ?></div>
                    <div class="meal-target">Calories: <?= number_format($mealCalories) ?>/<?= number_format($targetCalories) ?> kcal</div>
                </div>

                <?php if ($mealItems): ?>
                    <ul class="food-list">
                        <?php foreach ($mealItems as $item): ?>
                            <li>
                                <span>
                                    <?= htmlspecialchars($item['food_name']) ?>
                                    <?php if(!empty($item['recipe_details'])): ?>
                                        <small><?= htmlspecialchars($item['recipe_details']) ?></small>
                                    <?php endif; ?>
                                </span>
                                <span style="color: var(--gray); font-size:14px;">
                                    <?= $item['quantity'] ?> serving • <?= $item['calories'] ?> kcal • <?= $item['protein'] ?>g protein
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: var(--gray); font-style:italic; font-size:14px;">No items planned for this meal</p>
                <?php endif; ?>

                <div style="margin-top:12px;">
                    <div style="font-size:12px;color:var(--gray);margin-bottom:4px;">Calories Progress</div>
                    <div style="background:#e9ecef;border-radius:8px;height:8px;overflow:hidden;">
                        <div style="width:<?= min(100, ($mealCalories/$targetCalories*100)) ?>%;height:100%;background:var(--primary);"></div>
                    </div>
                    <div style="font-size:12px;color:var(--gray);margin:4px 0 0;">Protein Progress</div>
                    <div style="background:#e9ecef;border-radius:8px;height:8px;overflow:hidden;">
                        <div style="width:<?= min(100, ($mealProtein/$targetProtein*100)) ?>%;height:100%;background:var(--accent);"></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="action-buttons">
            <?php if ($alreadyLoggedToday): ?>
                <a class="btn btn-primary">✓ Weight Logged</a>
            <?php else: ?>
                <a href="log_weight.php" class="btn btn-primary">Log Weight</a>
            <?php endif; ?>
            <a href="progress.php" class="btn btn-secondary">View Progress</a>
            <a href="view_history.php" class="btn btn-secondary">View History</a>
        </div>

    <?php endif; ?>
</div>

<!-- REMINDER-->
<script>
<?php if ($user['reminders_enabled']): ?>
if ("Notification" in window) {
    if (Notification.permission !== "granted") Notification.requestPermission();

    const reminders = [
        { time: "<?= substr($user['breakfast_time'],0,5) ?>", msg: "🍳 Breakfast time!" },
        { time: "<?= substr($user['snack_time'],0,5) ?>", msg: "🥪 Snack time!" },
        { time: "<?= substr($user['lunch_time'],0,5) ?>", msg: "🍛 Lunch time!" },
        { time: "<?= substr($user['dinner_time'],0,5) ?>", msg: "🍽 Dinner time!" }
    ];

    setInterval(() => {
        const now = new Date();
        const current = now.getHours().toString().padStart(2,'0') + ":" +
                        now.getMinutes().toString().padStart(2,'0');

        reminders.forEach(r => { if(current === r.time) new Notification("VeggieFit Reminder",{body:r.msg}); });
    }, 60000);
}
<?php endif; ?>
</script>

</body>
</html>
<?php
session_start();
require_once "db_connection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: Login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

/* CHECK TODAY'S LOG */
$checkStmt = $conn->prepare("
    SELECT date, weight_kg, calories_consumed, protein_consumed, notes 
    FROM progress_tracking 
    WHERE user_id = ? AND date = ?
");
$checkStmt->execute([$user_id, $today]);
$todayLog = $checkStmt->fetch(PDO::FETCH_ASSOC);

/* LAST LOG DATE */
$lastStmt = $conn->prepare("
    SELECT MAX(date) as last_date 
    FROM progress_tracking 
    WHERE user_id = ?
");
$lastStmt->execute([$user_id]);
$lastLog = $lastStmt->fetch(PDO::FETCH_ASSOC);
$lastDate = $lastLog['last_date'] ?? null;

/* STREAK CALCULATION */
$streak = 0;
if ($lastDate) {
    $lastDateObj = new DateTime($lastDate);
    $todayObj = new DateTime($today);
    $interval = $todayObj->diff($lastDateObj);

    if ($interval->days == 1) {
        $streakStmt = $conn->prepare("
            SELECT COUNT(DISTINCT date) as streak
            FROM progress_tracking
            WHERE user_id = ?
            AND date >= DATE_SUB(?, INTERVAL 30 DAY)
        ");
        $streakStmt->execute([$user_id, $today]);
        $streakData = $streakStmt->fetch(PDO::FETCH_ASSOC);
        $streak = $streakData['streak'] ?? 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log Daily Progress | VeggieFit</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #2d6a4f;
            --primary-light: #40916c;
            --light: #f1f8f5;
            --gray: #6c757d;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .form-container {
            width: 100%;
            max-width: 500px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .form-header h1 {
            color: var(--primary);
            font-size: 24px;
        }

        .form-header p {
            color: var(--gray);
            font-size: 14px;
        }

        .streak-counter {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
            display: <?= $streak > 0 ? 'block' : 'none' ?>;
        }

        .streak-number {
            font-size: 32px;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #555;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            font-size: 15px;
        }

        .form-input:focus {
            border-color: var(--primary);
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn:hover {
            background: var(--primary-light);
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="form-container">

    <?php if ($streak > 0): ?>
        <div class="streak-counter">
            <div class="streak-number">🔥 <?= $streak ?></div>
            <div>Day Streak</div>
        </div>
    <?php endif; ?>

    <div class="form-header">
        <h1>Log Daily Progress</h1>
        <p>Track weight, calories, and protein</p>
    </div>

    <form method="POST" action="save_progress.php">

        <div class="form-group">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-input"
                   value="<?= $today ?>"
                   max="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Weight (kg) *</label>
            <input type="number" name="weight_kg" class="form-input"
                   step="0.1" min="20" max="300"
                   value="<?= $todayLog['weight_kg'] ?? '' ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Calories Consumed *</label>
            <input type="number" name="calories_consumed" class="form-input"
                   min="0" max="10000"
                   value="<?= $todayLog['calories_consumed'] ?? '' ?>"
                   required>
        </div>

        <div class="form-group">
            <label class="form-label">Protein Consumed (g) *</label>
            <input type="number" name="protein_consumed" class="form-input"
                   step="0.1" min="0" max="500"
                   placeholder="e.g. 90"
                   value="<?= $todayLog['protein_consumed'] ?? '' ?>"
                   required>
        </div>

        <div class="form-group">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-input" rows="3"><?= $todayLog['notes'] ?? '' ?></textarea>
        </div>

        <button type="submit" class="btn">
            <?= $todayLog ? '📝 Update Progress' : '✅ Log Progress' ?>
        </button>
    </form>

    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

</div>

</body>
</html>

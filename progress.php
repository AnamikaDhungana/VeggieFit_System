<?php
session_start();
require_once "db_connection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: Login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
$today = date('Y-m-d');

// Clear messages after showing
unset($_SESSION['success']);
unset($_SESSION['error']);

/* =========================
   FETCH PROGRESS HISTORY WITH CALORIES AND PROTEIN
========================= */
$stmt = $conn->prepare("
    SELECT date, weight_kg, calories_consumed, protein_consumed, notes 
    FROM progress_tracking 
    WHERE user_id = ? 
    ORDER BY date DESC 
    LIMIT 30
");
$stmt->execute([$user_id]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user info with full details for TDEE calculation
$userStmt = $conn->prepare("
    SELECT username, weight_kg as current_weight, gender, age, height_cm, activity_level
    FROM users WHERE user_id = ?
");
$userStmt->execute([$user_id]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// Calculate calorie and protein targets from user data
require_once "meal_algorithm.php";
$calorieTarget = 2000; // Default fallback
$proteinTarget = 0;

if ($user) {
    // Calculate BMR and TDEE
    $bmr = calculateBMR($user['gender'], $user['current_weight'], $user['height_cm'], $user['age']);
    
    // Map activity level for TDEE calculation
    $activityMap = [
        'Sedentary' => 'Sedentary',
        'Lightly Active' => 'Light',
        'Moderately Active' => 'Moderate',
        'Very Active' => 'Very Active'
    ];
    $mappedActivity = $activityMap[$user['activity_level']] ?? 'Moderate';
    
    $tdee = calculateTDEE($bmr, $mappedActivity);
    $calorieTarget = max($tdee - 500, 1200); // 500 kcal deficit for weight loss, minimum 1200
    
    // Calculate protein target
    $proteinTarget = calculateProteinTarget($user['current_weight'], $mappedActivity);
}

// Calculate streak
$streak = 0;
if ($logs) {
    $currentDate = new DateTime($today);
    $lastDate = new DateTime($logs[0]['date']);
    
    // Check if logged today
    if ($lastDate->format('Y-m-d') == $today) {
        $streak = 1;
        $checkDate = clone $lastDate;
        
        // Count consecutive days
        foreach ($logs as $log) {
            $logDate = new DateTime($log['date']);
            $dateDiff = $checkDate->diff($logDate)->days;
            
            if ($dateDiff == 1) {
                $streak++;
                $checkDate = $logDate;
            } elseif ($dateDiff > 1) {
                break;
            }
        }
    }
}

// Check if logged today
$loggedToday = false;
if ($logs && $logs[0]['date'] == $today) {
    $loggedToday = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Progress History | VeggieFit</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #2d6a4f;
            --primary-light: #40916c;
            --accent: #8c34d6;
            --light: #f1f8f5;
            --gray: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --streak: #ff6b6b;
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Times New Roman';
            background: var(--light);
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .page-header h1 {
            color: var(--primary);
            font-size: 24px;
        }
        
        /* Daily Status */
        .daily-status {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .status-card {
            flex: 1;
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .status-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .status-text {
            font-size: 14px;
            color: var(--gray);
        }
        
        .status-achieved {
            border-left: 4px solid var(--success);
        }
        
        .status-pending {
            border-left: 4px solid var(--danger);
        }
        
        /* Streak Counter */
        .streak-badge {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Messages */
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Actions */
        .actions {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-light);
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
        
        .btn-streak {
            background: var(--streak);
            color: white;
        }
        
        /* Charts */
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 900px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
        }
        
        .chart-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 15px;
            text-align: center;
        }
        
        .chart-wrapper {
            height: 350px;
            position: relative;
        }
        
        /* Stats */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 13px;
            color: var(--gray);
            text-transform: uppercase;
        }
        
        /* Progress Summary */
        .progress-summary {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .summary-title {
            color: var(--primary);
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .summary-item:last-child {
            border-bottom: none;
        }
        
        .summary-label {
            color: var(--gray);
        }
        
        .summary-value {
            font-weight: 600;
        }
        
        .positive {
            color: var(--success);
        }
        
        .negative {
            color: var(--danger);
        }
        
        /* Table */
        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-top: 25px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #e0e0e0;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .today-row {
            background: #f0f9ff;
        }
        
        .weight-change, .calorie-status {
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 500;
        }
        
        .change-up { background: #f8d7da; color: #721c24; }
        .change-down { background: #d4edda; color: #155724; }
        .change-neutral { background: #e2e3e5; color: #383d41; }
        
        .calorie-under { background: #d4edda; color: #155724; }
        .calorie-over { background: #f8d7da; color: #721c24; }
        .calorie-unknown { background: #e2e3e5; color: #383d41; }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--gray);
        }
        
        .empty-state p {
            margin-bottom: 15px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
            
            .daily-status {
                flex-direction: column;
            }
            
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="page-header">
            <h1>Daily Progress Tracker</h1>
            <div>
                <?php if ($streak > 0): ?>
                    <div class="streak-badge">🔥 <?= $streak ?> Day Streak</div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- Daily Status -->
        <div class="daily-status">
            <div class="status-card <?= $loggedToday ? 'status-achieved' : 'status-pending' ?>">
                <div class="status-icon"><?= $loggedToday ? '✅' : '📅' ?></div>
                <div class="status-text">
                    <strong><?= $loggedToday ? 'Logged Today' : 'Pending Log' ?></strong><br>
                    <?= date('F j, Y') ?>
                </div>
            </div>
            
            <div class="status-card">
                <div class="status-icon">📊</div>
                <div class="status-text">
                    <strong><?= count($logs) ?> Entries</strong><br>
                    Last 30 days
                </div>
            </div>
            
            <div class="status-card">
                <div class="status-icon">🎯</div>
                <div class="status-text">
                    <strong>Daily Targets</strong><br>
                    <?= number_format($calorieTarget) ?> kcal
                    <?php if ($proteinTarget > 0): ?>
                        | <?= number_format($proteinTarget, 1) ?>g protein
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="actions">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
            <?php if ($streak > 2): ?>
                <a href="#" class="btn btn-streak">🔥 <?= $streak ?> Day Streak</a>
            <?php endif; ?>
        </div>
        
        <?php if ($logs): ?>
            <?php
            $latest = $logs[0] ?? null;
            $oldest = end($logs) ?: null;
            $totalEntries = count($logs);
            $weightChange = $latest && $oldest ? $latest['weight_kg'] - $oldest['weight_kg'] : 0;
            
            // Calculate calorie stats
            $calorieEntries = array_filter($logs, function($log) {
                return $log['calories_consumed'] > 0;
            });
            $avgCalories = $calorieEntries ? 
                round(array_sum(array_column($calorieEntries, 'calories_consumed')) / count($calorieEntries)) : 
                null;
            
            // Calculate protein stats
            $proteinEntries = array_filter($logs, function($log) {
                return isset($log['protein_consumed']) && $log['protein_consumed'] > 0;
            });
            $avgProtein = $proteinEntries ? 
                round(array_sum(array_column($proteinEntries, 'protein_consumed')) / count($proteinEntries), 1) : 
                null;
            
            // Get weekly data
            $last7Days = array_slice($logs, 0, 7);
            $weeklyChange = count($last7Days) > 1 ? $last7Days[0]['weight_kg'] - $last7Days[count($last7Days)-1]['weight_kg'] : 0;
            ?>
            
            <!-- Progress Summary -->
            <div class="progress-summary">
                <div class="summary-title">📈 Your Progress Summary</div>
                
                <div class="summary-item">
                    <span class="summary-label">Starting Weight</span>
                    <span class="summary-value"><?= $oldest ? number_format($oldest['weight_kg'], 1) : '-' ?> kg</span>
                </div>
                
                <div class="summary-item">
                    <span class="summary-label">Current Weight</span>
                    <span class="summary-value"><?= $latest ? number_format($latest['weight_kg'], 1) : '-' ?> kg</span>
                </div>
                
                <div class="summary-item">
                    <span class="summary-label">Total Change</span>
                    <span class="summary-value <?= $weightChange < 0 ? 'positive' : ($weightChange > 0 ? 'negative' : '') ?>">
                        <?php if ($weightChange < 0): ?>
                            ▼ Lost <?= number_format(abs($weightChange), 1) ?> kg
                        <?php elseif ($weightChange > 0): ?>
                            ▲ Gained <?= number_format($weightChange, 1) ?> kg
                        <?php else: ?>
                            No change
                        <?php endif; ?>
                    </span>
                </div>
                
                <?php if (count($last7Days) > 1): ?>
                <div class="summary-item">
                    <span class="summary-label">Weekly Change</span>
                    <span class="summary-value <?= $weeklyChange < 0 ? 'positive' : ($weeklyChange > 0 ? 'negative' : '') ?>">
                        <?php if ($weeklyChange < 0): ?>
                            ▼ <?= number_format(abs($weeklyChange), 1) ?> kg this week
                        <?php elseif ($weeklyChange > 0): ?>
                            ▲ <?= number_format($weeklyChange, 1) ?> kg this week
                        <?php else: ?>
                            Stable this week
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if ($avgCalories): ?>
                <div class="summary-item">
                    <span class="summary-label">Avg. Daily Calories</span>
                    <span class="summary-value"><?= number_format($avgCalories) ?> kcal</span>
                </div>
                <?php endif; ?>
                
                <?php if ($avgProtein): ?>
                <div class="summary-item">
                    <span class="summary-label">Avg. Daily Protein</span>
                    <span class="summary-value"><?= number_format($avgProtein, 1) ?>g</span>
                </div>
                <?php endif; ?>
                
                <?php if ($proteinTarget > 0): ?>
                <div class="summary-item">
                    <span class="summary-label">Daily Protein Target</span>
                    <span class="summary-value"><?= number_format($proteinTarget, 1) ?>g</span>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Stats -->
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-value"><?= $totalEntries ?></div>
                    <div class="stat-label">Total Entries</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value">
                        <?= $latest ? number_format($latest['weight_kg'], 1) : '-' ?>
                    </div>
                    <div class="stat-label">Latest Weight</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value">
                        <?php if ($weightChange > 0): ?>
                            <span class="negative">+<?= number_format($weightChange, 1) ?></span>
                        <?php elseif ($weightChange < 0): ?>
                            <span class="positive"><?= number_format($weightChange, 1) ?></span>
                        <?php else: ?>
                            <span>0</span>
                        <?php endif; ?>
                    </div>
                    <div class="stat-label">Weight Change</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value">
                        <?= $avgCalories ? number_format($avgCalories) . ' kcal' : '-' ?>
                    </div>
                    <div class="stat-label">Avg. Calories</div>
                </div>
                
                <?php if ($avgProtein): ?>
                <div class="stat-card">
                    <div class="stat-value">
                        <?= number_format($avgProtein, 1) ?>g
                    </div>
                    <div class="stat-label">Avg. Protein</div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Charts -->
            <div class="charts-container">
                <div class="chart-box">
                    <div class="chart-title">Daily Weight Progress</div>
                    <div class="chart-wrapper">
                        <canvas id="weightChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-box">
                    <div class="chart-title">Daily Calorie Intake</div>
                    <div class="chart-wrapper">
                        <canvas id="calorieChart"></canvas>
                    </div>
                </div>
                
                <?php if ($avgProtein): ?>
                <div class="chart-box">
                    <div class="chart-title">Daily Protein Intake</div>
                    <div class="chart-wrapper">
                        <canvas id="proteinChart"></canvas>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Weight (kg)</th>
                            <th>Calories</th>
                            <th>Protein</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $previousWeight = null;
                        foreach ($logs as $log):
                            $isToday = $log['date'] == $today;
                            $weightChange = null;
                            
                            if ($previousWeight !== null) {
                                $weightChange = $log['weight_kg'] - $previousWeight;
                            }
                            $previousWeight = $log['weight_kg'];
                            
                            // Determine calorie status
                            $calorieStatus = '';
                            if ($log['calories_consumed'] > 0) {
                                if ($log['calories_consumed'] <= $calorieTarget) {
                                    $calorieStatus = 'Under target';
                                    $calorieClass = 'calorie-under';
                                } else {
                                    $calorieStatus = 'Over target';
                                    $calorieClass = 'calorie-over';
                                }
                            } else {
                                $calorieStatus = 'Not logged';
                                $calorieClass = 'calorie-unknown';
                            }
                        ?>
                        <tr class="<?= $isToday ? 'today-row' : '' ?>">
                            <td>
                                <strong><?= htmlspecialchars($log['date']) ?></strong>
                                <?php if ($isToday): ?>
                                    <div style="font-size: 11px; color: var(--primary);">TODAY</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= number_format($log['weight_kg'], 1) ?> kg</div>
                                <?php if ($weightChange !== null): ?>
                                    <?php if ($weightChange > 0): ?>
                                        <span class="weight-change change-up">+<?= number_format($weightChange, 1) ?> kg</span>
                                    <?php elseif ($weightChange < 0): ?>
                                        <span class="weight-change change-down"><?= number_format($weightChange, 1) ?> kg</span>
                                    <?php else: ?>
                                        <span class="weight-change change-neutral">No change</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($log['calories_consumed'] > 0): ?>
                                    <div><?= number_format($log['calories_consumed']) ?> kcal</div>
                                    <div class="calorie-status <?= $calorieClass ?>"><?= $calorieStatus ?></div>
                                <?php else: ?>
                                    <span style="color: var(--gray);">Not logged</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($log['protein_consumed']) && $log['protein_consumed'] > 0): ?>
                                    <div><?= number_format($log['protein_consumed'], 1) ?>g</div>
                                    <?php if ($proteinTarget > 0): ?>
                                        <?php 
                                        $proteinPercentage = round(($log['protein_consumed'] / $proteinTarget) * 100);
                                        $proteinColor = $proteinPercentage >= 100 ? '#28a745' : ($proteinPercentage >= 80 ? '#ffc107' : '#dc3545');
                                        ?>
                                        <div style="width: 100px; height: 6px; background: #e9ecef; border-radius: 3px; overflow: hidden; margin-top: 4px;">
                                            <div style="width: <?= min($proteinPercentage, 100) ?>%; height: 100%; background: <?= $proteinColor ?>;"></div>
                                        </div>
                                        <small style="color: var(--gray); font-size: 11px;"><?= $proteinPercentage ?>%</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: var(--gray);">Not logged</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($calorieTarget > 0 && $log['calories_consumed'] > 0): ?>
                                    <?php 
                                    $percentage = round(($log['calories_consumed'] / $calorieTarget) * 100);
                                    $color = $percentage <= 100 ? '#28a745' : '#dc3545';
                                    ?>
                                    <div style="width: 100px; height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden;">
                                        <div style="width: <?= min($percentage, 100) ?>%; height: 100%; background: <?= $color ?>;"></div>
                                    </div>
                                    <small style="color: var(--gray);"><?= $percentage ?>% of target</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="color: var(--gray); font-size: 13px;">
                                    <?= $log['notes'] ? htmlspecialchars($log['notes']) : '-' ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <script>
                // Prepare chart data
                const logs = <?= json_encode($logs) ?>;
                const calorieTarget = <?= $calorieTarget ?>;
                const proteinTarget = <?= $proteinTarget ?? 0 ?>;
                const today = '<?= $today ?>';
                
                // Sort by date ascending for charts
                const sortedLogs = [...logs].sort((a, b) => new Date(a.date) - new Date(b.date));
                
                // Extract data
                const dates = sortedLogs.map(log => {
                    const date = new Date(log.date);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                });
                const weights = sortedLogs.map(log => log.weight_kg);
                const calories = sortedLogs.map(log => log.calories_consumed || 0);
                const proteins = sortedLogs.map(log => log.protein_consumed || 0);
                const targetLine = Array(dates.length).fill(calorieTarget);
                const proteinTargetLine = Array(dates.length).fill(proteinTarget);
                
                // Check if we have enough data
                const hasEnoughData = dates.length >= 2;
                
                if (hasEnoughData) {
                    // Create Weight Chart
                    const weightCtx = document.getElementById('weightChart').getContext('2d');
                    new Chart(weightCtx, {
                        type: 'line',
                        data: {
                            labels: dates,
                            datasets: [{
                                label: 'Daily Weight (kg)',
                                data: weights,
                                borderColor: '#2d6a4f',
                                backgroundColor: 'rgba(45, 106, 79, 0.2)',
                                borderWidth: 3,
                                tension: 0.3,
                                fill: true,
                                pointBackgroundColor: function(context) {
                                    const index = context.dataIndex;
                                    const date = sortedLogs[index].date;
                                    return date === today ? '#ff6b6b' : '#2d6a4f';
                                },
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: function(context) {
                                    const index = context.dataIndex;
                                    const date = sortedLogs[index].date;
                                    return date === today ? 8 : 6;
                                },
                                pointHoverRadius: 10
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    titleFont: { size: 14 },
                                    bodyFont: { size: 14 },
                                    callbacks: {
                                        title: function(context) {
                                            const index = context[0].dataIndex;
                                            return sortedLogs[index].date;
                                        },
                                        label: function(context) {
                                            return `Weight: ${context.parsed.y} kg`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: false,
                                    title: {
                                        display: true,
                                        text: 'Weight (kg)',
                                        font: { size: 14, weight: 'bold' }
                                    },
                                    ticks: {
                                        font: { size: 12 },
                                        callback: function(value) {
                                            return value + ' kg';
                                        }
                                    }
                                },
                                x: {
                                    ticks: {
                                        font: { size: 12 }
                                    }
                                }
                            }
                        }
                    });
                    
                    // Create Calorie Chart
                    const calorieCtx = document.getElementById('calorieChart').getContext('2d');
                    new Chart(calorieCtx, {
                        type: 'line',
                        data: {
                            labels: dates,
                            datasets: [
                                {
                                    label: 'Daily Calories',
                                    data: calories,
                                    borderColor: '#8c34d6',
                                    backgroundColor: 'rgba(140, 52, 214, 0.2)',
                                    borderWidth: 3,
                                    tension: 0.3,
                                    fill: true,
                                    pointBackgroundColor: function(context) {
                                        const index = context.dataIndex;
                                        const date = sortedLogs[index].date;
                                        return date === today ? '#ff6b6b' : '#8c34d6';
                                    },
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    pointRadius: function(context) {
                                        const index = context.dataIndex;
                                        const date = sortedLogs[index].date;
                                        return date === today ? 8 : 6;
                                    },
                                    pointHoverRadius: 10
                                },
                                {
                                    label: 'Daily Target',
                                    data: targetLine,
                                    borderColor: '#ff6b6b',
                                    borderWidth: 2,
                                    borderDash: [5, 5],
                                    fill: false,
                                    pointRadius: 0
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        font: { size: 13 },
                                        padding: 15
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    titleFont: { size: 14 },
                                    bodyFont: { size: 14 },
                                    callbacks: {
                                        title: function(context) {
                                            const index = context[0].dataIndex;
                                            return sortedLogs[index].date;
                                        },
                                        label: function(context) {
                                            if (context.datasetIndex === 0) {
                                                return `Calories: ${context.parsed.y} kcal`;
                                            } else {
                                                return `Target: ${context.parsed.y} kcal`;
                                            }
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Calories (kcal)',
                                        font: { size: 14, weight: 'bold' }
                                    },
                                    ticks: {
                                        font: { size: 12 },
                                        callback: function(value) {
                                            return value + ' kcal';
                                        }
                                    }
                                },
                                x: {
                                    ticks: {
                                        font: { size: 12 }
                                    }
                                }
                            }
                        }
                    });
                    
                    <?php if ($avgProtein): ?>
                    // Create Protein Chart
                    const proteinCtx = document.getElementById('proteinChart');
                    if (proteinCtx) {
                        new Chart(proteinCtx.getContext('2d'), {
                            type: 'line',
                            data: {
                                labels: dates,
                                datasets: [
                                    {
                                        label: 'Daily Protein',
                                        data: proteins,
                                        borderColor: '#40916c',
                                        backgroundColor: 'rgba(64, 145, 108, 0.2)',
                                        borderWidth: 3,
                                        tension: 0.3,
                                        fill: true,
                                        pointBackgroundColor: function(context) {
                                            const index = context.dataIndex;
                                            const date = sortedLogs[index].date;
                                            return date === today ? '#ff6b6b' : '#40916c';
                                        },
                                        pointBorderColor: '#fff',
                                        pointBorderWidth: 2,
                                        pointRadius: function(context) {
                                            const index = context.dataIndex;
                                            const date = sortedLogs[index].date;
                                            return date === today ? 8 : 6;
                                        },
                                        pointHoverRadius: 10
                                    },
                                    {
                                        label: 'Protein Target',
                                        data: proteinTargetLine,
                                        borderColor: '#ff6b6b',
                                        borderWidth: 2,
                                        borderDash: [5, 5],
                                        fill: false,
                                        pointRadius: 0
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'top',
                                        labels: {
                                            font: { size: 13 },
                                            padding: 15
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                        padding: 12,
                                        titleFont: { size: 14 },
                                        bodyFont: { size: 14 },
                                        callbacks: {
                                            title: function(context) {
                                                const index = context[0].dataIndex;
                                                return sortedLogs[index].date;
                                            },
                                            label: function(context) {
                                                if (context.datasetIndex === 0) {
                                                    return `Protein: ${context.parsed.y} g`;
                                                } else {
                                                    return `Target: ${context.parsed.y} g`;
                                                }
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: 'Protein (g)',
                                            font: { size: 14, weight: 'bold' }
                                        },
                                        ticks: {
                                            font: { size: 12 },
                                            callback: function(value) {
                                                return value + ' g';
                                            }
                                        }
                                    },
                                    x: {
                                        ticks: {
                                            font: { size: 12 }
                                        }
                                    }
                                }
                            }
                        });
                    }
                    <?php endif; ?>
                    
                    // Add motivational message
                    const firstWeight = weights[0];
                    const lastWeight = weights[weights.length - 1];
                    const totalChange = (firstWeight - lastWeight).toFixed(1);
                    
                    let message = '';
                    if (totalChange > 0) {
                        message = `🎉 Amazing! You've lost ${totalChange} kg over ${dates.length} days!`;
                    } else if (totalChange < 0) {
                        message = `💪 You've gained ${Math.abs(totalChange)} kg. Keep tracking!`;
                    } else {
                        message = `⚖️ Your weight is stable. Consistency is key!`;
                    }
                    
                    if (dates.length >= 7) {
                        const weeklyChange = (weights[0] - weights[Math.min(6, weights.length-1)]).toFixed(1);
                        if (weeklyChange > 0) {
                            message += ` 📉 ${weeklyChange} kg lost this week!`;
                        }
                    }
                    
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'alert alert-success';
                    messageDiv.innerHTML = `✓ ${message} <br><small>Log again tomorrow to continue your journey!</small>`;
                    document.querySelector('.charts-container').insertAdjacentElement('afterend', messageDiv);
                    
                } else {
    const loggedToday = <?= json_encode($loggedToday) ?>;
    const todayEntry = logs.find(l => l.date === today);

    let innerHtml = `
        <div style="grid-column:1/-1;text-align:center;padding:40px;">
            <div style="font-size:60px;margin-bottom:20px;">📊</div>
            <h3 style="color:var(--primary);margin-bottom:10px;">Getting Started!</h3>
            <p>You've logged <strong>${logs.length} day</strong>. Keep logging daily to see your progress chart!</p>
    `;

    if (loggedToday && todayEntry) {
        innerHtml += `
            <div style="margin-top:20px;background:#f0fff4;border:1px solid #c3e6cb;border-radius:10px;padding:15px;max-width:300px;margin-left:auto;margin-right:auto;">
                <strong style="color:var(--primary);">✅ Today's Log</strong><br><br>
                ⚖️ ${parseFloat(todayEntry.weight_kg).toFixed(1)} kg &nbsp;|&nbsp;
                🔥 ${todayEntry.calories_consumed > 0 ? todayEntry.calories_consumed + ' kcal' : '—'} &nbsp;|&nbsp;
                💪 ${todayEntry.protein_consumed > 0 ? todayEntry.protein_consumed + 'g' : '—'}
            </div>
            <p style="margin-top:15px;font-size:13px;color:var(--gray);">🌟 Come back tomorrow to build your streak!</p>
        `;
    } else {
        innerHtml += `
            <div style="margin-top:25px;">
                <a href="log_weight.php" class="btn btn-primary" style="display:inline-block;">✅ Log Tomorrow's Data</a>
            </div>
        `;
    }

    innerHtml += `
            <div style="margin-top:20px;background:#f8f9fa;padding:15px;border-radius:8px;">
                <p style="margin:0;font-size:14px;color:var(--gray);">💡 <strong>Tip:</strong> Log daily to build a complete picture of your health journey!</p>
            </div>
        </div>
    `;

    document.querySelector('.charts-container').innerHTML = innerHtml;
}
            </script>
            
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div style="font-size: 60px; margin-bottom: 20px;">📅</div>
                <h3 style="color: var(--primary); margin-bottom: 15px;">Start Your Daily Tracking!</h3>
                <p>Log your first entry today to begin tracking your health journey.</p>
                <p style="margin: 15px 0; font-size: 14px; color: var(--gray);">
                    Track daily to see your progress over time!
                </p>
                <a href="log_weight.php" class="btn btn-primary" style="display: inline-block; margin-top: 15px;">
                    ✅ Log My First Entry
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

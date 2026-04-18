<?php
session_start();
require_once "../db_connection.php";

// Allow both admin and SuperAdmin
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','SuperAdmin'])) {
    header("Location: ../login.php");
    exit();
}

// Get all users for dropdown
$usersStmt = $conn->prepare("SELECT user_id, username, email, created_at FROM users ORDER BY username");
$usersStmt->execute();
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected user or default to first
$selectedUser = $_GET['user_id'] ?? ($users[0]['user_id'] ?? null);
if (!$selectedUser) die("No users found.");

/* ================= USER INFO FIRST (IMPORTANT) ================= */
$userStmt = $conn->prepare("SELECT username, email, created_at FROM users WHERE user_id = ?");
$userStmt->execute([$selectedUser]);
$userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$userInfo) {
    die("User not found.");
}

$user_join_date = date('Y-m-d', strtotime($userInfo['created_at']));
$today = date('Y-m-d');

/* ================= DATE RANGE LOGIC (FIXED) ================= */

// Default = join date → today
$default_start = $user_join_date;
$default_end = $today;

// Input dates
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date'])
    ? $_GET['start_date']
    : $default_start;

$end_date = isset($_GET['end_date']) && !empty($_GET['end_date'])
    ? $_GET['end_date']
    : $default_end;

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

/* ================= FETCH PROGRESS ================= */
$progressStmt = $conn->prepare("
    SELECT date, weight_kg, calories_consumed, notes
    FROM progress_tracking
    WHERE user_id = ? AND date BETWEEN ? AND ?
    ORDER BY date ASC
");
$progressStmt->execute([$selectedUser, $start_date, $end_date]);
$progressLogs = $progressStmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= STATS ================= */
$totalEntries = count($progressLogs);

$loggedToday = false;
$todayWeight = null;
$todayCalories = null;

foreach ($progressLogs as $log) {
    if ($log['date'] == $today) {
        $loggedToday = true;
        $todayWeight = $log['weight_kg'];
        $todayCalories = $log['calories_consumed'];
        break;
    }
}

$latestWeight = $totalEntries > 0 ? end($progressLogs)['weight_kg'] : 0;
$oldestWeight = $totalEntries > 0 ? $progressLogs[0]['weight_kg'] : 0;
$weightChange = $totalEntries > 0 ? $latestWeight - $oldestWeight : 0;

$calorieDays = array_filter($progressLogs, fn($l) => $l['calories_consumed'] > 0);
$avgCalories = count($calorieDays) > 0
    ? round(array_sum(array_column($calorieDays, 'calories_consumed')) / count($calorieDays))
    : 0;

$totalDaysInRange = (strtotime($end_date) - strtotime($start_date)) / 86400 + 1;
$consistencyScore = $totalDaysInRange > 0
    ? round(($totalEntries / $totalDaysInRange) * 100)
    : 0;

/* ================= ALL TIME ================= */
$allTimeStmt = $conn->prepare("
    SELECT COUNT(*) as total_all,
           MIN(weight_kg) as min_weight,
           MAX(weight_kg) as max_weight,
           MIN(date) as first_log
    FROM progress_tracking 
    WHERE user_id = ?
");
$allTimeStmt->execute([$selectedUser]);
$allTimeStats = $allTimeStmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($userInfo['username'] ?? 'User') ?> · Progress</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman';
            background: #f8fafc;
            color: #0f172a;
            line-height: 1.5;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 32px 24px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .header-left h1 {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .header-left p {
            color: #64748b;
            font-size: 14px;
        }

        .back-btn {
            background: white;
            border: 1px solid #e2e8f0;
            padding: 10px 20px;
            border-radius: 10px;
            color: #475569;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .back-btn:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        /* Filter Card */
        .filter-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 32px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }

        .filter-form {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 180px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .filter-group select,
        .filter-group input {
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: white;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #2d6a4f;
            box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.1);
        }

        .apply-btn {
            background: #2d6a4f;
            color: white;
            border: none;
            padding: 10px 28px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            height: 42px;
        }

        .apply-btn:hover {
            background: #1e4f3a;
        }

        .preset-btn {
            background: white;
            border: 1px solid #e2e8f0;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            color: #475569;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .preset-btn:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        /* User Card */
        .user-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .user-info h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .user-meta {
            display: flex;
            gap: 24px;
            color: #64748b;
            font-size: 14px;
        }

        .user-minmax {
            display: flex;
            gap: 32px;
        }

        .minmax-item {
            text-align: right;
        }

        .minmax-label {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .minmax-value {
            font-size: 20px;
            font-weight: 700;
        }

        /* Today Banner */
        .today-banner {
            background: <?= $loggedToday ? '#dcfce7' : '#fff7ed' ?>;
            border: 1px solid <?= $loggedToday ? '#86efac' : '#fed7aa' ?>;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .banner-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: <?= $loggedToday ? '#22c55e' : '#f97316' ?>;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .banner-content h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .banner-content p {
            color: #475569;
            font-size: 14px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #e2e8f0;
        }

        .stat-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #f1f8f5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2d6a4f;
            font-size: 18px;
        }

        .stat-label {
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .stat-trend {
            font-size: 13px;
        }

        .trend-up { color: #dc2626; }
        .trend-down { color: #16a34a; }
        .trend-neutral { color: #64748b; }

        /* Charts */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 32px;
        }

        .chart-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #e2e8f0;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .chart-title {
            font-size: 16px;
            font-weight: 600;
        }

        .chart-container {
            height: 280px;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
            background: #f1f5f9;
        }

        /* Table */
        .table-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .table-header {
            padding: 16px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            font-size: 16px;
            font-weight: 600;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 14px 20px;
            background: #f8fafc;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 14px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }

        tr:hover td {
            background: #f8fafc;
        }

        .today-row {
            background: #f0f9ff;
            font-weight: 500;
        }

        .today-badge {
            background: #2d6a4f;
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
            margin-left: 8px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            border: 1px dashed #cbd5e1;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #64748b;
            margin-bottom: 24px;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-group select,
            .filter-group input {
                width: 100%;
            }
            .user-card {
                flex-direction: column;
                align-items: flex-start;
            }
            .user-minmax {
                width: 100%;
                justify-content: space-between;
            }
            .minmax-item {
                text-align: left;
            }
        }
    </style>

</head>

<body>
<div class="container">

<!-- HEADER -->
<div class="header">
    <div class="header-left">
        <h1>User Progress</h1>
        <p>Track and analyze individual user progress</p>
    </div>
    <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
</div>

<!-- FILTER -->
<div class="filter-card">
<form method="GET" class="filter-form">

    <div class="filter-group">
        <label>Select User</label>
        <select name="user_id" onchange="this.form.submit()">
            <?php foreach($users as $u): ?>
                <option value="<?= $u['user_id'] ?>" <?= $selectedUser == $u['user_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u['username']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filter-group">
        <label>From</label>
        <input type="date" name="start_date"
               min="<?= $user_join_date ?>"
               max="<?= $today ?>"
               value="<?= $start_date ?>">
    </div>

    <div class="filter-group">
        <label>To</label>
        <input type="date" name="end_date"
               min="<?= $user_join_date ?>"
               max="<?= $today ?>"
               value="<?= $end_date ?>">
    </div>

    <button type="submit" class="apply-btn">Apply</button>

    <!-- PRESETS -->
    <a href="?user_id=<?= $selectedUser ?>&start_date=<?= date('Y-m-d', strtotime('-7 days', strtotime($today))) ?>&end_date=<?= $today ?>" class="preset-btn">7 Days</a>

    <a href="?user_id=<?= $selectedUser ?>&start_date=<?= date('Y-m-d', strtotime('-30 days', strtotime($today))) ?>&end_date=<?= $today ?>" class="preset-btn">30 Days</a>

    <a href="?user_id=<?= $selectedUser ?>&start_date=<?= date('Y-m-d', strtotime('-90 days', strtotime($today))) ?>&end_date=<?= $today ?>" class="preset-btn">90 Days</a>

</form>
</div>

<!-- USER CARD -->
<div class="user-card">
    <div class="user-info">
        <h2><?= htmlspecialchars($userInfo['username']) ?></h2>
        <div class="user-meta">
            <span>📧 <?= htmlspecialchars($userInfo['email']) ?></span>
            <span>📅 Joined: <?= date('M j, Y', strtotime($userInfo['created_at'])) ?></span>
            <span>📊 Total logs: <?= $allTimeStats['total_all'] ?? 0 ?></span>
        </div>
    </div>
</div>

<!-- EMPTY STATE -->
<?php if ($totalEntries == 0): ?>
<div class="empty-state">
    <div class="empty-icon">📊</div>
    <h3>No Data Found</h3>
    <p>No entries for <?= htmlspecialchars($userInfo['username']) ?>
    between <?= $start_date ?> and <?= $end_date ?></p>

    <div class="btn-group">
        <a href="?user_id=<?= $selectedUser ?>&start_date=<?= date('Y-m-d', strtotime('-7 days', strtotime($today))) ?>&end_date=<?= $today ?>" class="apply-btn">Last 7 Days</a>

        <a href="?user_id=<?= $selectedUser ?>&start_date=<?= date('Y-m-d', strtotime('-30 days', strtotime($today))) ?>&end_date=<?= $today ?>" class="preset-btn">Last 30 Days</a>
    </div>
</div>
<?php endif; ?>

</div>
</body>
</html>
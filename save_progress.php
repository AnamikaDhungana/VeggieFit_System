<?php
session_start();
require_once "db_connection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: Login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* =========================
   PROCESS FORM SUBMISSION
========================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method";
    header("Location: log_weight.php");
    exit();
}

/* =========================
   GET & SANITIZE INPUTS
========================= */
$weight   = isset($_POST['weight_kg']) ? (float) $_POST['weight_kg'] : 0;

/* REQUIRED FIELDS (UPDATED) */
$calories = $_POST['calories_consumed'] ?? '';
$protein  = $_POST['protein_consumed'] ?? '';

if ($calories === '' || $protein === '') {
    $_SESSION['error'] = "Calories and Protein are required fields";
    header("Location: log_weight.php");
    exit();
}

$calories = (int) $calories;
$protein  = (float) $protein;

$notes = $_POST['notes'] ?? '';
$date  = $_POST['date'] ?? date('Y-m-d');
$today = date('Y-m-d');

/* =========================
   VALIDATION
========================= */
if ($weight <= 0 || $weight > 300) {
    $_SESSION['error'] = "Please enter a valid weight (1–300 kg)";
    header("Location: log_weight.php");
    exit();
}

if ($calories < 0 || $calories > 10000) {
    $_SESSION['error'] = "Please enter valid calories (0–10000)";
    header("Location: log_weight.php");
    exit();
}

if ($protein < 0 || $protein > 500) {
    $_SESSION['error'] = "Please enter valid protein amount (0–500 g)";
    header("Location: log_weight.php");
    exit();
}

if ($date > $today) {
    $_SESSION['error'] = "Cannot log data for future dates";
    header("Location: log_weight.php");
    exit();
}

/* =========================
   CHECK EXISTING LOG
========================= */
$checkStmt = $conn->prepare("
    SELECT progress_id 
    FROM progress_tracking 
    WHERE user_id = ? AND date = ?
");
$checkStmt->execute([$user_id, $date]);
$hasLogged = $checkStmt->fetch();

/* =========================
   YESTERDAY WEIGHT
========================= */
$yesterday = date('Y-m-d', strtotime('-1 day'));
$yesterdayStmt = $conn->prepare("
    SELECT weight_kg 
    FROM progress_tracking 
    WHERE user_id = ? AND date = ?
");
$yesterdayStmt->execute([$user_id, $yesterday]);
$yesterdayWeight = $yesterdayStmt->fetch(PDO::FETCH_ASSOC);

/* =========================
   INSERT / UPDATE
========================= */
try {

    if ($hasLogged) {

        $stmt = $conn->prepare("
            UPDATE progress_tracking
            SET weight_kg = ?, 
                calories_consumed = ?, 
                protein_consumed = ?, 
                notes = ?, 
                updated_at = NOW()
            WHERE user_id = ? AND date = ?
        ");

        $stmt->execute([
            $weight,
            $calories,
            $protein,
            $notes,
            $user_id,
            $date
        ]);

        $message = "Today's progress updated successfully!";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO progress_tracking 
            (user_id, date, weight_kg, calories_consumed, protein_consumed, notes)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $date,
            $weight,
            $calories,
            $protein,
            $notes
        ]);

        $message = "Daily progress logged successfully!";
    }

    /* UPDATE USER WEIGHT */
    $updateUserStmt = $conn->prepare("
        UPDATE users SET weight_kg = ? WHERE user_id = ?
    ");
    $updateUserStmt->execute([$weight, $user_id]);

    /* WEIGHT CHANGE MESSAGE */
    $changeMessage = "";
    if ($yesterdayWeight && $date === $today) {
        $diff = $weight - $yesterdayWeight['weight_kg'];

        if ($diff < 0) {
            $changeMessage = " 🎉 You lost " . number_format(abs($diff), 1) . " kg since yesterday!";
        } elseif ($diff > 0) {
            $changeMessage = " 📈 You gained " . number_format($diff, 1) . " kg since yesterday.";
        } else {
            $changeMessage = " ⚖️ Your weight is stable since yesterday.";
        }
    }

    $_SESSION['success'] = $message . $changeMessage;

} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

header("Location: progress.php");
exit();
?>

<?php
session_start();
require_once "db_connection.php";
require_once "meal_algorithm.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: Login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* CHECK TODAY'S PLAN */
$checkStmt = $conn->prepare("
    SELECT plan_id FROM meal_plans 
    WHERE user_id = ? AND date = CURDATE()
");
$checkStmt->execute([$user_id]);

if ($checkStmt->fetch()) {
    $_SESSION['message'] = "You already have a meal plan for today.";
    header("Location: dashboard.php");
    exit();
}

/* FETCH USER DATA */
$stmt = $conn->prepare("
    SELECT username, gender, age, height_cm, weight_kg, 
           diet_preference, activity_level 
    FROM users WHERE user_id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: Login/login.php");
    exit();
}

/* CHECK IF UNDERWEIGHT */
$bmi = calculateBMI($user['weight_kg'], $user['height_cm']);
$bmiCategory = getBMICategory($bmi);

if ($bmiCategory === 'Underweight') {
    $_SESSION['warning'] = "Meal planning is not recommended for underweight individuals. Please consult a healthcare professional.";
    header("Location: dashboard.php");
    exit();
}

/* CALCULATE NUTRITION NEEDS */
$bmr = calculateBMR($user['gender'], $user['weight_kg'], $user['height_cm'], $user['age']);
$tdee = calculateTDEE($bmr, $user['activity_level'] ?? 'Moderate');
$calorieTarget = calculateWeightLossCalories($tdee);

// NEW: Calculate daily protein target based on weight and activity
$proteinTarget = calculateProteinTarget($user['weight_kg'], $user['activity_level'] ?? 'Moderate');

/* CREATE MEAL PLAN */
$date = date('Y-m-d');

try {
    $conn->beginTransaction();
    
    // Create meal plan record
    $planStmt = $conn->prepare("
        INSERT INTO meal_plans (user_id, date, total_calories, total_protein)
        VALUES (?, ?, ?, 0)
    ");
    $planStmt->execute([$user_id, $date, $calorieTarget]);
    
    $plan_id = $conn->lastInsertId();
    
    // Generate meals using improved algorithm
    $success = generateMealPlan($plan_id, $calorieTarget, $proteinTarget);
    
    if ($success) {
        $conn->commit();
        $_SESSION['success'] = "Meal plan generated successfully!";
    } else {
        $conn->rollBack();
        $_SESSION['error'] = "Failed to generate meal plan.";
    }
    
} catch (PDOException $e) {
    $conn->rollBack();
    $_SESSION['error'] = "Error generating meal plan: " . $e->getMessage();
}

header("Location: dashboard.php");
exit();
?>
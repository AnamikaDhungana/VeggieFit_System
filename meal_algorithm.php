<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "db_connection.php";

/* HEALTH CALCULATIONS */

function calculateBMI($weight, $height_cm) {
    $height_m = $height_cm / 100;
    return round($weight / ($height_m * $height_m), 2);
}

function calculateBMR($gender, $weight, $height_cm, $age) {
    if (strtolower($gender) === 'male') {
        return (10 * $weight) + (6.25 * $height_cm) - (5 * $age) + 5;
    }
    return (10 * $weight) + (6.25 * $height_cm) - (5 * $age) - 161;
}

function calculateTDEE($bmr, $activity_level) {
    $factors = [
        'Sedentary' => 1.2,
        'Light' => 1.375,
        'Moderate' => 1.55,
        'Active' => 1.725,
        'Very Active' => 1.9
    ];
    return $bmr * ($factors[$activity_level] ?? 1.375);
}

function calculateWeightLossCalories($tdee) {
    return max($tdee - 500, 1200);
}

function calculateProteinTarget($weight, $activity_level = 'Moderate') {
    $map = [
        'Sedentary' => 0.8,
        'Light' => 0.9,
        'Moderate' => 1.0,
        'Active' => 1.2,
        'Very Active' => 1.5
    ];
    return round($weight * ($map[$activity_level] ?? 1.0), 2);
}

function getBMICategory($bmi) {
    if ($bmi < 18.5) return 'Underweight';
    if ($bmi < 25) return 'Normal';
    if ($bmi < 30) return 'Overweight';
    return 'Obese';
}

/* FOOD HELPERS */

function getFoodByKeywords($keywords, $meal, $states, $calLimit) {
    global $conn;

    $conditions = [];
    foreach ($keywords as $k) {
        $conditions[] = "food_name LIKE '%$k%'";
    }

    $sql = "
        SELECT * FROM foods
        WHERE category = ?
        AND diet_type = 'Vegetarian'
        AND food_state IN (" . implode(',', array_fill(0, count($states), '?')) . ")
        AND (" . implode(' OR ', $conditions) . ")
        AND calories <= ?
        ORDER BY RAND()
        LIMIT 1
    ";

    $params = array_merge([$meal], $states, [$calLimit]);
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getExtraProteinFood($meal, $states) {
    global $conn;

    $proteinFoods = ['Paneer','Dal','Chana','Chickpea','Rajma','Sprouts','Tofu'];

    $conditions = [];
    foreach ($proteinFoods as $p) {
        $conditions[] = "food_name LIKE '%$p%'";
    }

    $sql = "
        SELECT * FROM foods
        WHERE category = ?
        AND diet_type = 'Vegetarian'
        AND food_state IN (" . implode(',', array_fill(0, count($states), '?')) . ")
        AND (" . implode(' OR ', $conditions) . ")
        ORDER BY protein DESC
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute(array_merge([$meal], $states));

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/* MAIN MEAL GENERATOR */

function generateMealPlan($plan_id, $dailyCalories, $dailyProtein) {
    global $conn;

    $meals = [
        'Breakfast' => ['ratio' => 0.25, 'states' => ['Raw','Fresh','Plain']],
        'Lunch'     => ['ratio' => 0.35, 'states' => ['Cooked','Boiled']],
        'Dinner'    => ['ratio' => 0.30, 'states' => ['Cooked','Boiled']],
        'Snack'     => ['ratio' => 0.10, 'states' => ['Raw','Fresh','Plain']]
    ];

    $roles = [
        'carb'    => ['Rice','Roti','Chapati','Poha','Upma'],
        'protein' => ['Dal','Paneer','Chana','Chickpea','Rajma','Sprouts'],
        'veg'     => ['Veg','Sabji','Curry','Saag']
    ];

    $totalCalories = $totalProtein = $totalCarbs = $totalFats = 0;

    foreach ($meals as $meal => $config) {

        $mealCalLimit = $dailyCalories * $config['ratio'];
        $foodsToInsert = [];

        /* === LUNCH & DINNER: STRICT STRUCTURE === */
        if ($meal === 'Lunch' || $meal === 'Dinner') {
            $foodsToInsert[] = getFoodByKeywords($roles['carb'], $meal, $config['states'], $mealCalLimit);
            $foodsToInsert[] = getFoodByKeywords($roles['protein'], $meal, $config['states'], $mealCalLimit);
            $foodsToInsert[] = getFoodByKeywords($roles['veg'], $meal, $config['states'], $mealCalLimit);
        } 
        /* === BREAKFAST & SNACK: CALORIE-BASED SELECTION === */
        else {
            $mealCaloriesSoFar = 0;

            // Fetch all eligible foods randomly
            $stmt = $conn->prepare("
                SELECT * FROM foods
                WHERE category = ?
                AND diet_type = 'Vegetarian'
                AND food_state IN (" . implode(',', array_fill(0, count($config['states']), '?')) . ")
                ORDER BY RAND()
            ");
            $stmt->execute(array_merge([$meal], $config['states']));
            $allFoods = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($allFoods as $food) {
                if (!$food) continue;

                $foodsToInsert[] = $food;
                $mealCaloriesSoFar += $food['calories'];

                if ($mealCaloriesSoFar >= $mealCalLimit) {
                    break; // stop once we reach calorie target
                }
            }
        }

        /* INSERT FOODS INTO DB */
        foreach ($foodsToInsert as $food) {
            if (!$food) continue;

            $insert = $conn->prepare("
                INSERT INTO meal_items (plan_id, meal_type, food_id, quantity)
                VALUES (?, ?, ?, 1)
            ");
            $insert->execute([$plan_id, $meal, $food['food_id']]);

            $totalCalories += $food['calories'];
            $totalProtein  += $food['protein'];
            $totalCarbs    += $food['carbs'];
            $totalFats     += $food['fats'];
        }
    }

    /* PROTEIN BOOST LOGIC */
    $proteinGap = $dailyProtein - $totalProtein;

    if ($proteinGap > 5) {
        $boostMeals = ['Snack','Dinner'];

        foreach ($boostMeals as $boostMeal) {
            $states = ($boostMeal === 'Snack')
                ? ['Raw','Fresh','Plain']
                : ['Cooked','Boiled'];

            $extra = getExtraProteinFood($boostMeal, $states);

            if ($extra) {
                $insert = $conn->prepare("
                    INSERT INTO meal_items (plan_id, meal_type, food_id, quantity)
                    VALUES (?, ?, ?, 1)
                ");
                $insert->execute([$plan_id, $boostMeal, $extra['food_id']]);

                $totalCalories += $extra['calories'];
                $totalProtein  += $extra['protein'];
                $totalCarbs    += $extra['carbs'];
                $totalFats     += $extra['fats'];

                break; // only one boost
            }
        }
    }

    /* FINAL UPDATE */
    $update = $conn->prepare("
        UPDATE meal_plans
        SET total_calories = ?, total_protein = ?, total_carbs = ?, total_fats = ?
        WHERE plan_id = ?
    ");

    return $update->execute([
        round($totalCalories),
        round($totalProtein, 2),
        round($totalCarbs, 2),
        round($totalFats, 2),
        $plan_id
    ]);
}
?>
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

function getFoodByKeywords($keywords, $meal, $states, $calLimit, $excludeIds = []) {
    global $conn;

    $conditions = [];
    foreach ($keywords as $k) {
        $conditions[] = "food_name LIKE '%$k%'";
    }

    $excludeClause = '';
    if (!empty($excludeIds)) {
        $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
        $excludeClause = "AND food_id NOT IN ($placeholders)";
    }

    $sql = "
        SELECT * FROM foods
        WHERE category = ?
        AND diet_type = 'Vegetarian'
        AND food_state IN (" . implode(',', array_fill(0, count($states), '?')) . ")
        AND (" . implode(' OR ', $conditions) . ")
        AND calories <= ?
        $excludeClause
        ORDER BY RAND()
        LIMIT 1
    ";

    $params = array_merge([$meal], $states, [$calLimit], $excludeIds);
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/* Multiple options for randomness */
function getMultipleFoodsByKeywords($keywords, $meal, $states, $calLimit, $excludeIds = [], $limit = 2) {
    global $conn;

    $conditions = [];
    foreach ($keywords as $k) {
        $conditions[] = "food_name LIKE '%$k%'";
    }

    $excludeClause = '';
    if (!empty($excludeIds)) {
        $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
        $excludeClause = "AND food_id NOT IN ($placeholders)";
    }

    $sql = "
        SELECT * FROM foods
        WHERE category = ?
        AND diet_type = 'Vegetarian'
        AND food_state IN (" . implode(',', array_fill(0, count($states), '?')) . ")
        AND (" . implode(' OR ', $conditions) . ")
        AND calories <= ?
        $excludeClause
        ORDER BY RAND()
        LIMIT $limit
    ";

    $params = array_merge([$meal], $states, [$calLimit], $excludeIds);
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getExtraProteinFood($meal, $states, $excludeIds = []) {
    global $conn;

    $proteinFoods = ['Paneer','Dal','Chana','Chickpea','Rajma','Sprouts','Tofu','Soya','Soybean','Tempeh','Seitan','Edamame'];

    $conditions = [];
    foreach ($proteinFoods as $p) {
        $conditions[] = "food_name LIKE '%$p%'";
    }

    $excludeClause = '';
    if (!empty($excludeIds)) {
        $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
        $excludeClause = "AND food_id NOT IN ($placeholders)";
    }

    $sql = "
        SELECT * FROM foods
        WHERE category = ?
        AND diet_type = 'Vegetarian'
        AND food_state IN (" . implode(',', array_fill(0, count($states), '?')) . ")
        AND (" . implode(' OR ', $conditions) . ")
        $excludeClause
        ORDER BY protein DESC
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute(array_merge([$meal], $states, $excludeIds));

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/* MAIN MEAL GENERATOR */

function generateMealPlan($plan_id, $dailyCalories, $dailyProtein) {
    global $conn;

    $meals = [
        'Breakfast' => ['ratio' => 0.25, 'states' => ['Raw','Fresh','Plain','Cooked','Boiled']],
        'Lunch'     => ['ratio' => 0.35, 'states' => ['Cooked','Boiled','Fresh']],
        'Dinner'    => ['ratio' => 0.30, 'states' => ['Cooked','Boiled','Fresh']],
        'Snack'     => ['ratio' => 0.10, 'states' => ['Raw','Fresh','Plain']]
    ];

    $roles = [
        'carb'    => ['Rice','Roti','Chapati','Paratha','Khichdi','Sorghum','Junelo'],
        'protein' => ['Dal','Paneer','Chana','Chickpea','Rajma','Sprouts','Tofu','Soya','Soybean','Tempeh','Seitan','Edamame','Curry'],
        'veg'     => ['Veg','Sabji','Curry','Saag','Salad','Soup','Vegetable']
    ];

    $totalCalories = $totalProtein = $totalCarbs = $totalFats = 0;
    $usedFoodIds = [];

    foreach ($meals as $meal => $config) {

        $mealCalLimit = $dailyCalories * $config['ratio'];
        $foodsToInsert = [];

        /* LUNCH & DINNER: STRUCTURED ROLE-BASED SELECTION WITH VARIETY */
        if ($meal === 'Lunch' || $meal === 'Dinner') {

            foreach (['carb', 'protein', 'veg'] as $role) {

                $foods = getMultipleFoodsByKeywords(
                    $roles[$role],
                    $meal,
                    $config['states'],
                    $mealCalLimit,
                    $usedFoodIds,
                    2
                );

                if (!empty($foods)) {
                    $food = $foods[array_rand($foods)];
                    $foodsToInsert[] = $food;
                    $usedFoodIds[] = $food['food_id'];
                }
            }

        }
        /* BREAKFAST & SNACK: CALORIE-BASED SELECTION */
        else {
            $mealCaloriesSoFar = 0;

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
                if (in_array($food['food_id'], $usedFoodIds)) continue;

                $foodsToInsert[] = $food;
                $usedFoodIds[] = $food['food_id'];
                $mealCaloriesSoFar += $food['calories'];

                if ($mealCaloriesSoFar >= $mealCalLimit) break;
            }
        }

        /* INSERT FOODS INTO DB */
        foreach ($foodsToInsert as $food) {
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

    /* PROTEIN BOOST LOGIC — tries Dinner first, falls back to Snack */
    $proteinGap = $dailyProtein - $totalProtein;

    if ($proteinGap > 5) {
        $boostMeals = [
            'Dinner' => ['Cooked', 'Boiled', 'Fresh'],
            'Snack'  => ['Raw', 'Fresh', 'Plain'],
        ];

        foreach ($boostMeals as $boostMeal => $states) {
            $extra = getExtraProteinFood($boostMeal, $states, $usedFoodIds);

            if ($extra) {
                $insert = $conn->prepare("
                    INSERT INTO meal_items (plan_id, meal_type, food_id, quantity)
                    VALUES (?, ?, ?, 1)
                ");
                $insert->execute([$plan_id, $boostMeal, $extra['food_id']]);

                /* Update running totals so final UPDATE is accurate */
                $totalCalories += $extra['calories'];
                $totalProtein  += $extra['protein'];
                $totalCarbs    += $extra['carbs'];
                $totalFats     += $extra['fats'];

                $usedFoodIds[] = $extra['food_id'];

                break; // stop after first successful boost
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

/* CONTENT-BASED MATCHING ALGORITHM */

function findSimilarUserPlan($current_user_id, $gender, $weight, $height, $age, $activity_level, $diet_preference) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT mp.plan_id, mp.total_calories, mp.total_protein, mp.total_carbs, mp.total_fats
        FROM meal_plans mp
        JOIN users u ON mp.user_id = u.user_id
        WHERE u.user_id != :current_user_id
          AND LOWER(TRIM(u.gender)) = LOWER(TRIM(:gender))
          AND LOWER(TRIM(u.diet_preference)) = LOWER(TRIM(:diet_preference))
          AND LOWER(TRIM(u.activity_level)) = LOWER(TRIM(:activity_level))
          AND ABS(u.weight_kg - :weight) <= 2
          AND ABS(u.height_cm - :height) <= 2
          AND ABS(u.age - :age) <= 2
        ORDER BY mp.plan_id DESC
        LIMIT 1
    ");

    $stmt->execute([
        ':current_user_id' => $current_user_id,
        ':gender'          => $gender,
        ':diet_preference' => $diet_preference,
        ':activity_level'  => $activity_level,
        ':weight'          => $weight,
        ':height'          => $height,
        ':age'             => $age,
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function cloneMealPlan($source_plan_id, $new_plan_id) {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO meal_items (plan_id, meal_type, food_id, quantity)
        SELECT :new_plan_id, meal_type, food_id, quantity
        FROM meal_items
        WHERE plan_id = :source_plan_id
    ");

    return $stmt->execute([
        ':new_plan_id'    => $new_plan_id,
        ':source_plan_id' => $source_plan_id,
    ]);
}

function generateMealPlanFromTemplate($source_plan_id, $new_plan_id, $calorieTarget, $proteinTarget) {
    global $conn;

    // Clone base plan
    $cloned = cloneMealPlan($source_plan_id, $new_plan_id);
    if (!$cloned) return false;

    // Change 1 random food (variation)
    $stmt = $conn->prepare("
        SELECT meal_item_id, meal_type
        FROM meal_items
        WHERE plan_id = ?
        ORDER BY RAND()
        LIMIT 1
    ");
    $stmt->execute([$new_plan_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $stmt2 = $conn->prepare("
            SELECT food_id FROM foods
            WHERE category = ?
            ORDER BY RAND()
            LIMIT 1
        ");
        $stmt2->execute([$item['meal_type']]);
        $newFood = $stmt2->fetch(PDO::FETCH_ASSOC);

        if ($newFood) {
            $update = $conn->prepare("
                UPDATE meal_items SET food_id = ?
                WHERE meal_item_id = ?
            ");
            $update->execute([$newFood['food_id'], $item['meal_item_id']]);
        }
    }

    // Recalculate totals
    $stmt = $conn->prepare("
        SELECT f.calories, f.protein, f.carbs, f.fats
        FROM meal_items mi
        JOIN foods f ON mi.food_id = f.food_id
        WHERE mi.plan_id = ?
    ");
    $stmt->execute([$new_plan_id]);

    $cal = $pro = $carb = $fat = 0;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cal  += $row['calories'];
        $pro  += $row['protein'];
        $carb += $row['carbs'];
        $fat  += $row['fats'];
    }

    $update = $conn->prepare("
        UPDATE meal_plans
        SET total_calories=?, total_protein=?, total_carbs=?, total_fats=?
        WHERE plan_id=?
    ");

    return $update->execute([
        round($cal),
        round($pro, 2),
        round($carb, 2),
        round($fat, 2),
        $new_plan_id
    ]);
}
?>
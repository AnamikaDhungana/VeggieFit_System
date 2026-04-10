<?php
// Include PDO database connection
include 'db_connection.php';

try {

    /* USERS TABLE */
    $sql = "CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        reset_token VARCHAR(255) DEFAULT NULL,
        reset_token_expiry DATETIME DEFAULT NULL,
        gender ENUM('Male','Female','Other') NOT NULL,
        age INT NOT NULL,
        height_cm FLOAT NOT NULL,
        weight_kg FLOAT NOT NULL,
        diet_preference ENUM('Vegetarian','Vegan','Non-Vegetarian') DEFAULT 'Vegetarian',
        activity_level FLOAT DEFAULT 1.2,
        reminders_enabled TINYINT(1) DEFAULT 1,
        breakfast_time TIME DEFAULT '08:00:00',
        lunch_time TIME DEFAULT '12:30:00',
        snack_time TIME DEFAULT '15:30:00',
        dinner_time TIME DEFAULT '19:00:00',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;";
    $conn->exec($sql);
    echo "✅ users table ready<br>";

    /* FOODS TABLE */
    $sql = "CREATE TABLE IF NOT EXISTS foods (
        food_id INT AUTO_INCREMENT PRIMARY KEY,
        food_name VARCHAR(100) NOT NULL,

        -- NEW: Meal category
        category ENUM('Breakfast','Lunch','Dinner','Snack') NOT NULL,

        diet_type ENUM('Vegetarian','Non-Vegetarian') DEFAULT 'Vegetarian',
        food_state ENUM('Raw','Boiled','Cooked','Fresh','Plain') DEFAULT 'Cooked',

        serving_size VARCHAR(50),

        calories FLOAT NOT NULL,
        protein FLOAT NOT NULL,
        carbs FLOAT DEFAULT 0,
        fats FLOAT DEFAULT 0,
        recipe_details TEXT DEFAULT NULL,
        image VARCHAR(255) DEFAULT NULL,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;";
    $conn->exec($sql);
    echo "✅ foods table ready (with category & protein)<br>";

    /* MEAL PLANS TABLE */
    $sql = "CREATE TABLE IF NOT EXISTS meal_plans (
        plan_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        date DATE,
        total_calories FLOAT,
        total_protein FLOAT,
        total_carbs FLOAT,
        total_fats FLOAT,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    $conn->exec($sql);
    echo "✅ meal_plans table ready<br>";

    /* MEAL ITEMS TABLE */
    $sql = "CREATE TABLE IF NOT EXISTS meal_items (
        meal_item_id INT AUTO_INCREMENT PRIMARY KEY,
        plan_id INT,
        meal_type ENUM('Breakfast','Lunch','Dinner','Snack'),
        food_id INT,
        quantity FLOAT,
        eaten BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (plan_id) REFERENCES meal_plans(plan_id) ON DELETE CASCADE,
        FOREIGN KEY (food_id) REFERENCES foods(food_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    $conn->exec($sql);
    echo "✅ meal_items table ready<br>";

    /* PROGRESS TRACKING TABLE */
    $sql = "CREATE TABLE IF NOT EXISTS progress_tracking (
        progress_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        date DATE,
        weight_kg FLOAT,
        calories_consumed FLOAT,
        protein_consumed FLOAT DEFAULT NULL,
        notes TEXT,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    $conn->exec($sql);
    echo "✅ progress_tracking table ready<br>";

    /* ADMIN TABLE */
    $sql = "CREATE TABLE IF NOT EXISTS admin (
        admin_id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(225) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('SuperAdmin','Manager') DEFAULT 'Manager',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;";
    $conn->exec($sql);
    echo "✅ admin table ready<br>";

} catch (PDOException $e) {
    echo "❌ PDO Error: " . $e->getMessage();
}

$conn = null;
?>

<?php
session_start();
require_once "../db_connection.php";

$errors = [];
$successMsg = "";

/* --- FORM SUBMISSION ---*/
if (isset($_POST['add_food'])) {

    // Sanitize inputs
    $food_name = trim($_POST['food_name']); // Food name
    $category = $_POST['category'];         // Meal category
    $food_state = $_POST['food_state'];     // Raw, Boiled, Cooked, Fresh, Plain
    $serving_size = trim($_POST['serving_size']); // Text serving size
    $calories = $_POST['calories'];         // kcal
    $protein = $_POST['protein'];           // g
    $carbs = $_POST['carbs'];               // g
    $fats = $_POST['fats'];                 // g
    $recipe_details = trim($_POST['recipe_details'] ?? ''); // NEW: ingredients/recipe

    // Always vegetarian for this system
    $diet_type = "Vegetarian";

    // Image upload handling
    $image = $_FILES['image']['name'] ?? '';
    $tmp_name = $_FILES['image']['tmp_name'] ?? '';

    /* ---SERVER-SIDE VALIDATION ---*/
    if (empty($food_name)) {
        $errors[] = "Food name is required.";
    }

    // Category validation
    if (!in_array($category, ['Breakfast','Lunch','Dinner','Snack'])) {
        $errors[] = "Invalid meal category selected.";
    }

    // Food state validation
    if (!in_array($food_state, ['Raw', 'Boiled', 'Cooked', 'Fresh', 'Plain'])) {
        $errors[] = "Invalid food state selected.";
    }

    if (empty($serving_size)) {
        $errors[] = "Serving size is required.";
    }

    // Numeric validations
    if (!is_numeric($calories) || $calories < 0) {
        $errors[] = "Calories must be 0 or greater.";
    }
    if (!is_numeric($protein) || $protein < 0) {
        $errors[] = "Protein must be 0 or greater.";
    }
    if (!is_numeric($carbs) || $carbs < 0) {
        $errors[] = "Carbs must be 0 or greater.";
    }
    if (!is_numeric($fats) || $fats < 0) {
        $errors[] = "Fats must be 0 or greater.";
    }

    /* ---INSERT IF NO ERRORS ---*/
    if (empty($errors)) {

        // Handle image upload
        $image_path = null;
        if (!empty($image) && !empty($tmp_name)) {

            $upload_dir = "uploads/foods/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $image_path = $upload_dir . basename($image);
            move_uploaded_file($tmp_name, $image_path);
        }

        // PDO insert query (UPDATED TO INCLUDE recipe_details)
        $sql = "INSERT INTO foods 
            (food_name, category, diet_type, food_state, serving_size, calories, protein, carbs, fats, image, recipe_details)
            VALUES 
            (:food_name, :category, :diet_type, :food_state, :serving_size, :calories, :protein, :carbs, :fats, :image, :recipe_details)";

        $stmt = $conn->prepare($sql);

        $success = $stmt->execute([
            ':food_name' => $food_name,
            ':category' => $category,
            ':diet_type' => $diet_type,
            ':food_state' => $food_state,
            ':serving_size' => $serving_size,
            ':calories' => $calories,
            ':protein' => $protein,
            ':carbs' => $carbs,
            ':fats' => $fats,
            ':image' => $image_path,
            ':recipe_details' => $recipe_details 
        ]);

        if ($success) {
            $successMsg = "✅ Food meal added successfully!";
        } else {
            $errors[] = "Failed to add food item.";
        }

    } // end if empty errors

} // end if add_food
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Meal</title>
<style>
body {
    overflow:hidden;
    margin: 0;
    font-family: 'Times New Roman', Times, serif;
    background: #f5f8f3;
}
.container {
    display: flex;
    min-height: 100vh;
}
.left {
    flex: 1;
    background: url("https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg") center/cover no-repeat;
}
.right {
    flex: 1;
    background: #fff;
    max-width: 600px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 10px 30px 20px;
}
.right form {
    max-height: calc(100vh - 60px); /* subtract heading + padding */
    overflow-y: auto;
}
h2 {
    color: #2b6e2b;
    margin-bottom: 8px;
}
label {
    font-size: 14px;
}
.input-box, select, textarea {
    width: 100%;
    padding: 8px;
    margin-bottom: 8px;
    border: 1px solid #aad1aa;
    border-radius: 6px;
}
button {
    width: 100%;
    padding: 12px;
    margin-top: 8px;
    background: #2b7a2b;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
}
.msg {
    padding: 10px;
    margin-bottom: 12px;
    border-radius: 6px;
}
.success { background: #d4edda; color: #155724; }
.error { background: #f8d7da; color: #721c24; }
</style>
</head>
<body>
<div class="container">

<!---LEFT IMAGE --->
<div class="left"></div>

<!--- RIGHT FORM --->
<div class="right">
<h2>Add Meal (Full Food)</h2>

<!---SUCCESS MESSAGE --->
<?php if (!empty($successMsg)) : ?>
<div class="msg success"><?= $successMsg ?></div>
<?php endif; ?>

<!--- ERROR MESSAGES --->
<?php if (!empty($errors)) : ?>
<div class="msg error">
<ul>
<?php foreach ($errors as $err) : ?>
<li><?= htmlspecialchars($err) ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<!---FORM --->
<form method="POST" enctype="multipart/form-data">

<label>Meal Name</label>
<input class="input-box" type="text" name="food_name" required>

<label>Meal Category</label>
<select name="category" required>
<option value="">-- Select Category --</option>
<option value="Breakfast">Breakfast</option>
<option value="Lunch">Lunch</option>
<option value="Dinner">Dinner</option>
<option value="Snack">Snack</option>
</select>

<label>Food State</label>
<select name="food_state" required>
<option value="">-- Select --</option>
<option value="Raw">Raw</option>
<option value="Boiled">Boiled</option>
<option value="Cooked">Cooked</option>
<option value="Fresh">Fresh</option>
<option value="Plain">Plain</option>
</select>

<label>Serving Size</label>
<input class="input-box" type="text" name="serving_size" value="1 plate / 100 g" required>

<label>Calories (kcal)</label>
<input class="input-box" type="number" name="calories" min="0" step="0.01" required>

<label>Protein (g)</label>
<input class="input-box" type="number" name="protein" min="0" step="0.01" required>

<label>Carbs (g)</label>
<input class="input-box" type="number" name="carbs" min="0" step="0.01" required>

<label>Fats (g)</label>
<input class="input-box" type="number" name="fats" min="0" step="0.01" required>

<!-- NEW FIELD: Recipe / Ingredients -->
<label>Recipe / Ingredients (optional)</label>
<textarea class="input-box" name="recipe_details" rows="4" placeholder="E.g., 50g flattened rice, 5ml oil, 10g peanuts, 30g onion"></textarea>

<label>Meal Image (optional)</label>
<input class="input-box" type="file" name="image" accept="image/*">

<button type="submit" name="add_food">Add Meal</button>
</form>

</div> 
</div> 
</body>
</html>

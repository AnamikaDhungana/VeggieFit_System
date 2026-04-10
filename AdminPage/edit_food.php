<?php
session_start();
require_once "../db_connection.php";

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['SuperAdmin', 'admin'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_foods.php");
    exit();
}

$food_id = $_GET['id'];
$errors = [];
$successMsg = "";

/* Fetch food */
$stmt = $conn->prepare("SELECT * FROM foods WHERE food_id = ?");
$stmt->execute([$food_id]);
$food = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$food) {
    header("Location: manage_foods.php");
    exit();
}

/* Update food */
if (isset($_POST['update_food'])) {

    $food_name      = trim($_POST['food_name']);
    $category       = $_POST['category'];
    $food_state     = $_POST['food_state'];
    $serving_size   = trim($_POST['serving_size']);
    $calories       = $_POST['calories'];
    $protein        = $_POST['protein'];
    $carbs          = $_POST['carbs'];
    $fats           = $_POST['fats'];
    $recipe_details = trim($_POST['recipe_details']);

    if (empty($food_name)) $errors[] = "Food name is required.";

    if (!in_array($category, ['Breakfast','Lunch','Dinner','Snack'])) {
        $errors[] = "Invalid meal category selected.";
    }

    if (!in_array($food_state, ['Raw','Boiled','Cooked','Fresh','Plain'])) {
        $errors[] = "Invalid food state.";
    }

    foreach (['Calories'=>$calories,'Protein'=>$protein,'Carbs'=>$carbs,'Fats'=>$fats] as $k=>$v) {
        if (!is_numeric($v) || $v < 0) $errors[] = "$k must be 0 or greater.";
    }

    /* Image handling */
    $image_path = $food['image'];
    if (!empty($_FILES['image']['name'])) {
        if (!empty($food['image']) && file_exists($food['image'])) {
            unlink($food['image']);
        }
        $upload_dir = "uploads/foods/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $image_path = $upload_dir . time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    }

    /* Update in database */
    if (empty($errors)) {
        $sql = "UPDATE foods SET 
                food_name=?, category=?, food_state=?, serving_size=?,
                calories=?, protein=?, carbs=?, fats=?, recipe_details=?, image=?
                WHERE food_id=?";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $food_name, $category, $food_state, $serving_size,
            $calories, $protein, $carbs, $fats, $recipe_details, $image_path, $food_id
        ]);

        $successMsg = "✅ Food updated successfully!";

        /* Refresh the food record */
        $stmt = $conn->prepare("SELECT * FROM foods WHERE food_id=?");
        $stmt->execute([$food_id]);
        $food = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Food</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(to right, #e8f5e9, #f1f8f5);
}

/*  LAYOUT WRAPPER */
.wrapper {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

/*CARD CONTAINER*/
.card {
    background: #ffffff;
    width: 100%;
    max-width: 650px;
    border-radius: 14px;
    padding: 35px 40px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.card h2 {
    text-align: center;
    color: #2d6a4f;
    margin-bottom: 25px;
}

/*FORM ELEMENTS */
label {
    font-size: 14px;
    font-weight: 500;
}

input,
select,
textarea {
    width: 100%;
    padding: 11px;
    margin: 6px 0 16px;
    border: 1px solid #cde3d6;
    border-radius: 8px;
    font-size: 14px;
}

input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: #2d6a4f;
}
.grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #cde3d6;
    margin-bottom: 10px;
}

button {
    width: 100%;
    padding: 14px;
    background: #2d6a4f;
    color: #ffffff;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    background: #1b4332;
}

/* MESSAGE ALERTS */
.msg {
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 14px;
}

.success {
    background: #d4edda;
    color: #155724;
}

.error {
    background: #f8d7da;
    color: #721c24;
}

.back-link {
    text-align: center;
    margin-top: 15px;
}

.back-link a {
    color: #2d6a4f;
    text-decoration: none;
    font-weight: 500;
}

textarea.input-box {
    resize: vertical;
    min-height: 80px;
}
</style>
</head>
<body>

<div class="wrapper">
    <div class="card">

        <h2>Edit Food Item</h2>

        <?php if ($successMsg): ?>
            <div class="msg success"><?= $successMsg ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="msg error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <label>Food Name</label>
            <input type="text" name="food_name" value="<?= htmlspecialchars($food['food_name']) ?>" required>

            <label>Category</label>
            <select name="category">
                <?php foreach (['Breakfast','Lunch','Dinner','Snack'] as $cat): ?>
                    <option value="<?= $cat ?>" <?= $food['category']==$cat?'selected':'' ?>><?= $cat ?></option>
                <?php endforeach; ?>
            </select>

            <label>Food State</label>
            <select name="food_state">
                <?php foreach (['Raw','Boiled','Cooked','Fresh','Plain'] as $s): ?>
                    <option value="<?= $s ?>" <?= $food['food_state']==$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>

            <label>Serving Size</label>
            <input type="text" name="serving_size" value="<?= $food['serving_size'] ?>" required>

            <div class="grid">
                <div>
                    <label>Calories</label>
                    <input type="number" step="0.01" min="0" name="calories" value="<?= $food['calories'] ?>" required>
                </div>
                <div>
                    <label>Protein</label>
                    <input type="number" step="0.01" min="0" name="protein" value="<?= $food['protein'] ?>" required>
                </div>
                <div>
                    <label>Carbs</label>
                    <input type="number" step="0.01" min="0" name="carbs" value="<?= $food['carbs'] ?>" required>
                </div>
                <div>
                    <label>Fats</label>
                    <input type="number" step="0.01" min="0" name="fats" value="<?= $food['fats'] ?>" required>
                </div>
            </div>

            <label>Recipe / Ingredients</label>
            <textarea name="recipe_details" class="input-box" placeholder="e.g., 50g rice, 30g veggies, 5ml oil"><?= htmlspecialchars($food['recipe_details']) ?></textarea>

            <?php if ($food['image'] && file_exists($food['image'])): ?>
                <label>Current Image</label><br>
                <img src="<?= $food['image'] ?>" alt="Food Image">
            <?php endif; ?>

            <label>Change Image (optional)</label>
            <input type="file" name="image" accept="image/*">

            <button type="submit" name="update_food">Update Food</button>
        </form>

        <div class="back-link">
            <a href="manage_foods.php">← Back to Manage Foods</a>
        </div>

    </div>
</div>

</body>
</html>

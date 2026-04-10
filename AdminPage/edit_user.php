<?php
session_start();
require_once "../db_connection.php";

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['SuperAdmin','admin'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found");
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    // Convert height from feet to cm
    $height_ft = $_POST['height_ft'];
    $height_cm = round($height_ft * 30.48, 2);

    $stmt = $conn->prepare(
        "UPDATE users 
         SET username = ?, email = ?, age = ?, height_cm = ?, weight_kg = ?
         WHERE user_id = ?"
    );

    $stmt->execute([
        $_POST['username'],
        $_POST['email'],
        $_POST['age'],
        $height_cm,
        $_POST['weight_kg'],
        $id
    ]);

    header("Location: manage_users.php?success=updated");
    exit();
}

// Convert stored cm → feet for display
$height_ft_display = round($user['height_cm'] / 30.48, 2);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User | VeggieFit Admin</title>

<style>
:root{
    --primary:#2d6a4f;
    --primary-dark:#1b4332;
    --white:#ffffff;
}

*{box-sizing:border-box;font-family:'Times New Roman';}

body{
    margin:0;
    min-height:100vh;
    background:linear-gradient(135deg,#e9f5ee,#f1f8f5);
    display:flex;
    align-items:center;
    justify-content:center;
}

.edit-card{
    width:100%;
    max-width:540px;
    background:#fff;
    border-radius:18px;
    padding:35px 40px;
    box-shadow:0 15px 35px rgba(0,0,0,0.15);
}

.edit-card h2{
    text-align:center;
    color:var(--primary-dark);
    margin-bottom:8px;
}

.edit-card p{
    text-align:center;
    color:#555;
    margin-bottom:28px;
}

.form-group{margin-bottom:18px;}

label{
    display:block;
    font-size:14px;
    margin-bottom:6px;
    font-weight:600;
}

input{
    width:100%;
    padding:12px;
    border-radius:10px;
    border:1px solid #ccc;
}

.row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

.btn-group{
    display:flex;
    gap:15px;
    margin-top:30px;
}

button{
    flex:1;
    padding:12px;
    border:none;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
}

.update-btn{
    background:var(--primary);
    color:#fff;
}

.cancel-btn{
    background:#adb5bd;
}
</style>
</head>

<body>

<div class="edit-card">
    <h2>✏️ Edit User</h2>
    <p>Update user physical and account details</p>

    <form method="POST">

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username"
                   value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email"
                   value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="row">
            <div class="form-group">
                <label>Age</label>
                <input type="number" name="age" value="<?= $user['age'] ?>">
            </div>

            <div class="form-group">
                <label>Height (ft)</label>
                <input type="number" step="0.01" name="height_ft"
                       value="<?= $height_ft_display ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Weight (kg)</label>
            <input type="number" step="0.1" name="weight_kg"
                   value="<?= $user['weight_kg'] ?>">
        </div>

        <div class="btn-group">
            <button class="update-btn">Update User</button>
            <a href="manage_users.php">
                <button type="button" class="cancel-btn">Cancel</button>
            </a>
        </div>
    </form>
</div>

</body>
</html>

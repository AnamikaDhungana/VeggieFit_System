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

/* Fetch image path */
$stmt = $conn->prepare("SELECT image FROM foods WHERE food_id = ?");
$stmt->execute([$food_id]);
$food = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$food) {
    header("Location: manage_foods.php");
    exit();
}

/* Delete image file */
if (!empty($food['image']) && file_exists($food['image'])) {
    unlink($food['image']);
}

/* Delete record */
$stmt = $conn->prepare("DELETE FROM foods WHERE food_id = ?");
$stmt->execute([$food_id]);

header("Location: manage_foods.php");
exit();

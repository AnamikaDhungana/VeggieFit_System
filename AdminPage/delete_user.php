<?php
require_once "../db_connection.php";
$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
$stmt->execute([$id]);

header("Location: manage_users.php");
exit();

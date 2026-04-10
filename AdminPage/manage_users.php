<?php
session_start();
require_once "../db_connection.php";

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['SuperAdmin','admin'])) {
    header("Location: ../login.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users ORDER BY user_id DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users | VeggieFit</title>
<style>
body {
    margin: 0;
    font-family: 'Poppins', 'Times New Roman', serif;
    background: #f1f8f5;
}

.container {
    display: flex;
    min-height: 100vh;
}

/* SIDEBAR  */
#admin-sidebar, .sidebar {
    width: 260px;
    min-height: 100vh;
    background: #2d6a4f;
    color: #fff;
    padding: 25px 20px;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
}
.sidebar h2 {
    text-align: center;
    margin-bottom: 25px;
    font-size: 22px;
}
.menu a {
    display: block;
    padding: 10px 12px;
    margin: 6px 0;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s;
}
.menu a:hover { background: #1b4332; }
.menu .section {
    margin-top: 18px;
    font-weight: bold;
    font-size: 14px;
    color: #d8f3dc;
}
.sub { margin-left: 15px; font-size: 14px; }
.logout-btn {
    margin-top: 20px;
    background: #d00000;
    color: #fff;
    border: none;
    padding: 10px;
    border-radius: 10px;
    cursor: pointer;
}

/* MAIN CONTENT  */
.main {
    flex: 1;
    padding: 30px 40px;
    display: flex;
    flex-direction: column;
}
.main h2 {
    color: #2d6a4f;
    margin-bottom: 25px;
    font-size: 28px;
}

/* TABLE CONTAINER  */
.table-container {
    background: #fff;
    border-radius: 12px;
    overflow-x: auto;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
    padding: 15px;
}

table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
}
th, td {
    padding: 12px 8px;
    text-align: center;
    word-wrap: break-word;
}
th {
    background: #2d6a4f;
    color: #fff;
    font-weight: 600;
    font-size: 14px;
}
tr:nth-child(even) { background: #f7fbf9; }
tr:hover { background: #eef6f2; }

th:last-child,
td:last-child {
    width: 160px;
}

/*  TABLE ACTION BUTTONS */
.action-buttons {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    flex-wrap: nowrap;
}

.btn {
    border: none;
    cursor: pointer;
    border-radius: 8px;
    color: #fff;
    font-weight: 500;
    font-size: 13px;
    padding: 6px 14px;
    white-space: nowrap;
}

td {
    vertical-align: middle;
}

.edit-btn { background: #1f6ed4; }
.delete-btn { background: #d00000; }
.btn:hover { opacity: 0.85; }

/* RESPONSIVE  */
@media (max-width: 900px) {
    .container { flex-direction: column; }
    #admin-sidebar, .sidebar { width: 100%; min-height: auto; }
    .main { padding: 20px; }
    table { font-size: 12px; }
}
</style>
</head>
<body>

<div class="container">
    <!-- SIDEBAR -->
    <div id="admin-sidebar"></div>

    <!-- MAIN CONTENT -->
    <div class="main">
        <h2>Manage Users</h2>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>Diet</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['user_id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= $user['gender'] ?></td>
                        <td><?= $user['age'] ?></td>
                        <td><?= $user['diet_preference'] ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_user.php?id=<?= $user['user_id'] ?>">
                                    <button class="btn edit-btn">Edit</button>
                                </a>
                                <a href="delete_user.php?id=<?= $user['user_id'] ?>" onclick="return confirm('Delete this user?')">
                                    <button class="btn delete-btn">Delete</button>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SIDEBAR LOADER JS -->
<script>
fetch("admin_dashboard.php")
    .then(res => res.text())
    .then(html => {
        const temp = document.createElement("div");
        temp.innerHTML = html;
        const sidebar = temp.querySelector(".sidebar");
        if (sidebar) {
            document.getElementById("admin-sidebar").replaceWith(sidebar);
        }
    });
</script>

</body>
</html>
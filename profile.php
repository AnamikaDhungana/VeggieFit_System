<?php
session_start();
require_once "db_connection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: Login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

/* =========================
   FETCH USER DATA
========================= */
$stmt = $conn->prepare("
    SELECT username, email, gender, age, height_cm, weight_kg, 
           diet_preference, activity_level
    FROM users 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: Login/login.php");
    exit();
}

/* =========================
   HANDLE FORM SUBMISSION
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gender = $_POST['gender'] ?? $user['gender'];
    $age = (int) ($_POST['age'] ?? $user['age']);
    $height = (float) ($_POST['height_cm'] ?? $user['height_cm']);
    $weight = (float) ($_POST['weight_kg'] ?? $user['weight_kg']);
    $diet = $_POST['diet_preference'] ?? $user['diet_preference'];
    $activity = $_POST['activity_level'] ?? $user['activity_level'];
    
    // Validation
    $errors = [];
    
    if ($age < 10 || $age > 100) {
        $errors[] = "Age must be between 10 and 100 years.";
    }
    
    if ($height < 50 || $height > 250) {
        $errors[] = "Height must be between 50 and 250 cm.";
    }
    
    if ($weight < 20 || $weight > 300) {
        $errors[] = "Weight must be between 20 and 300 kg.";
    }
    
    if (empty($errors)) {
        try {
            // Update user profile
            $updateStmt = $conn->prepare("
                UPDATE users 
                SET gender = ?, age = ?, height_cm = ?, weight_kg = ?, 
                    diet_preference = ?, activity_level = ?, updated_at = NOW()
                WHERE user_id = ?
            ");
            
            $updateStmt->execute([
                $gender, $age, $height, $weight, $diet, $activity, $user_id
            ]);
            
            $message = "Profile updated successfully!";
            
            // Refresh user data
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $error = "Error updating profile: " . $e->getMessage();
        }
    } else {
        $error = implode(" ", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | VeggieFit</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2d6a4f;
            --primary-light: #40916c;
            --accent: #8c34d6;
            --light: #f1f8f5;
            --gray: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light);
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .profile-container {
            width: 100%;
            max-width: 900px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: 1fr 1.5fr;
        }
        
        /* Sidebar */
        .profile-sidebar {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .sidebar-content h1 {
            font-size: 26px;
            margin-bottom: 15px;
            line-height: 1.3;
        }
        
        .sidebar-content p {
            opacity: 0.9;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 32px;
        }
        
        /* Form Section */
        .form-section {
            padding: 40px;
        }
        
        .form-header {
            margin-bottom: 25px;
        }
        
        .form-header h2 {
            color: var(--primary);
            font-size: 22px;
            margin-bottom: 8px;
        }
        
        .form-header p {
            color: var(--gray);
            font-size: 14px;
        }
        
        /* Messages */
        .message {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .message-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
            font-size: 14px;
        }
        
        .form-input, .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s ease;
        }
        
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .form-input:disabled {
            background: #f8f9fa;
            cursor: not-allowed;
        }
        
        .form-note {
            font-size: 12px;
            color: var(--gray);
            margin-top: 5px;
        }
        
        /* Full width fields */
        .full-width {
            grid-column: 1 / -1;
        }
        
        /* Button */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 30px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .btn:hover {
            background: var(--primary-light);
        }
        
        .btn-full {
            width: 100%;
            justify-content: center;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
            
            .profile-sidebar {
                padding: 30px 20px;
                text-align: center;
            }
            
            .form-section {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <!-- Sidebar -->
        <div class="profile-sidebar">
            <div class="sidebar-content">
                <div class="user-avatar">
                    👤
                </div>
                <h1>Update Your Profile</h1>
                <p>
                    Keep your information accurate for personalized meal plans 
                    and accurate calorie calculations.
                </p>
            </div>
        </div>
        
        <!-- Form Section -->
        <div class="form-section">
            <div class="form-header">
                <h2>Personal Information</h2>
                <p>Update your details below</p>
            </div>
            
            <?php if ($message): ?>
                <div class="message message-success">
                    ✓ <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message message-error">
                    ⚠ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-grid">
                    <!-- Basic Info -->
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input 
                            type="text" 
                            class="form-input" 
                            value="<?= htmlspecialchars($user['username']) ?>" 
                            disabled
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input 
                            type="email" 
                            class="form-input" 
                            value="<?= htmlspecialchars($user['email']) ?>" 
                            disabled
                        >
                    </div>
                    
                    <!-- Personal Details -->
                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?= $user['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= $user['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= $user['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Age</label>
                        <input 
                            type="number" 
                            name="age" 
                            class="form-input" 
                            value="<?= htmlspecialchars($user['age']) ?>" 
                            min="10"
                            max="100"
                            required
                        >
                        <div class="form-note">Years</div>
                    </div>
                    
                    <!-- Physical Measurements -->
                    <div class="form-group">
                        <label class="form-label">Height</label>
                        <input 
                            type="number" 
                            name="height_cm" 
                            class="form-input" 
                            value="<?= htmlspecialchars($user['height_cm']) ?>" 
                            step="0.1"
                            min="50"
                            max="250"
                            required
                        >
                        <div class="form-note">Centimeters</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Weight</label>
                        <input 
                            type="number" 
                            name="weight_kg" 
                            class="form-input" 
                            value="<?= htmlspecialchars($user['weight_kg']) ?>" 
                            step="0.1"
                            min="20"
                            max="300"
                            required
                        >
                        <div class="form-note">Kilograms</div>
                    </div>
                    
                    <!-- Preferences -->
                    <div class="form-group full-width">
                        <label class="form-label">Diet Preference</label>
                        <select name="diet_preference" class="form-select">
                            <option value="Vegetarian" <?= $user['diet_preference'] === 'Vegetarian' ? 'selected' : '' ?>>Vegetarian</option>
                            <option value="Vegan" <?= $user['diet_preference'] === 'Vegan' ? 'selected' : '' ?>>Vegan</option>
                            <option value="Non-Vegetarian" <?= $user['diet_preference'] === 'Non-Vegetarian' ? 'selected' : '' ?>>Non-Vegetarian</option>
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <label class="form-label">Activity Level</label>
                        <select name="activity_level" class="form-select">
                            <option value="Sedentary" <?= ($user['activity_level'] ?? 'Moderate') === 'Sedentary' ? 'selected' : '' ?>>
                                Sedentary (little or no exercise)
                            </option>
                            <option value="Light" <?= ($user['activity_level'] ?? 'Moderate') === 'Light' ? 'selected' : '' ?>>
                                Light (exercise 1-3 days/week)
                            </option>
                            <option value="Moderate" <?= ($user['activity_level'] ?? 'Moderate') === 'Moderate' ? 'selected' : '' ?>>
                                Moderate (exercise 3-5 days/week)
                            </option>
                            <option value="Active" <?= ($user['activity_level'] ?? 'Moderate') === 'Active' ? 'selected' : '' ?>>
                                Active (exercise 6-7 days/week)
                            </option>
                            <option value="Very Active" <?= ($user['activity_level'] ?? 'Moderate') === 'Very Active' ? 'selected' : '' ?>>
                                Very Active (hard exercise daily)
                            </option>
                        </select>
                    </div>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-full">
                        💾 Save Changes
                    </button>
                    
                    <a href="dashboard.php" class="back-link">
                        ← Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
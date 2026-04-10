<?php
session_start();
include '../db_connection.php';

if (isset($_POST['register'])) {

    // Collect & sanitize inputs //
    $old = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'gender' => $_POST['gender'] ?? '',
        'age' => $_POST['age'] ?? '',
        'height_cm' => $_POST['height_cm'] ?? '',
        'weight_kg' => $_POST['weight_kg'] ?? '',
        'activity_level' => $_POST['activity_level'] ?? '',
    ];

    $username = $old['username'];
    $email = $old['email'];
    $password = $old['password'];
    $confirmPassword = $old['confirm_password'];
    $gender = $old['gender'];
    $age = (int) ($old['age'] !== '' ? $old['age'] : 0);
    $height_cm = (float) ($old['height_cm'] !== '' ? $old['height_cm'] : 0);
    $weight_kg = (float) ($old['weight_kg'] !== '' ? $old['weight_kg'] : 0);
    $activity_level = $old['activity_level'];

    // Field-wise errors so we can color each input.
    $fieldErrors = [];

    // Validation (field by field) //
    if ($username === '') $fieldErrors['username'] = "Full name is required.";
    if ($email === '') $fieldErrors['email'] = "Email is required.";
    if ($password === '') $fieldErrors['password'] = "Password is required.";
    if ($confirmPassword === '') $fieldErrors['confirm_password'] = "Confirm password is required.";
    if ($gender === '') $fieldErrors['gender'] = "Gender is required.";
    if ($age === 0) $fieldErrors['age'] = "Age is required.";
    if ($height_cm === 0) $fieldErrors['height_cm'] = "Height is required.";
    if ($weight_kg === 0) $fieldErrors['weight_kg'] = "Weight is required.";
    if ($activity_level === '') $fieldErrors['activity_level'] = "Activity level is required.";

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['email'] = "Invalid email format.";
    }

    // Email must not start with a capital letter
    if ($email !== '' && preg_match('/^[A-Z]/', $email)) {
        $fieldErrors['email'] = "Email must not start with a capital letter.";
    }

    
    // Full name validation: each word starts with a capital letter, rest lowercase letters, min 2 characters per word
if ($username !== '' && !preg_match('/^[A-Z][a-z]+(\s[A-Z][a-z]+)*$/', $username)) {
    $fieldErrors['username'] = "Full name must start with a capital letter, each word's first letter capitalized, only letters and spaces allowed.";
}

    // Password strength validation (match frontend pattern)
    if ($password !== '' && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/', $password)) {
        $fieldErrors['password'] = "Password must be at least 8 chars and include uppercase, lowercase, number and special character.";
    }

    if ($password !== '' && $confirmPassword !== '' && $password !== $confirmPassword) {
        $fieldErrors['confirm_password'] = "Passwords do not match.";
    }

    if ($age !== 0 && ($age < 10 || $age > 100)) {
        $fieldErrors['age'] = "Age must be between 10 and 100.";
    }

    // Gender validation
    $valid_genders = ['Male', 'Female', 'Other'];
    if ($gender !== '' && !in_array($gender, $valid_genders, true)) {
        $fieldErrors['gender'] = "Invalid gender selected.";
    }

    // Height handling (CM & "feet" style)
    if ($height_cm !== 0) {
        if ($height_cm <= 0) {
            $fieldErrors['height_cm'] = "Height must be greater than 0.";
        } elseif ($height_cm < 50) {
            // Likely entered in feet → convert to cm
            $height_cm = $height_cm * 30.48;
            if ($height_cm > 250) {
                $fieldErrors['height_cm'] = "Height cannot exceed 250 cm.";
            }
        } elseif ($height_cm > 250) {
            $fieldErrors['height_cm'] = "Height cannot exceed 250 cm.";
        }
    }

    if ($weight_kg !== 0 && ($weight_kg < 25 || $weight_kg > 300)) {
        $fieldErrors['weight_kg'] = "Weight must be between 25 kg and 300 kg.";
    }

    $valid_levels = ['1.2', '1.375', '1.55', '1.725'];
    if ($activity_level !== '' && !in_array($activity_level, $valid_levels, true)) {
        $fieldErrors['activity_level'] = "Invalid activity level selected.";
    }

    if (!empty($fieldErrors)) {
        $_SESSION['register_old'] = $old;
        $_SESSION['register_field_errors'] = $fieldErrors;
        header("Location: register.php");
        exit();
    }

    try {
        // Check email uniqueness //
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $fieldErrors['email'] = "This email is already registered.";
            $_SESSION['register_old'] = $old;
            $_SESSION['register_field_errors'] = $fieldErrors;
            header("Location: register.php");
            exit();
        }

        // Insert user //
       
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users 
            (username, email, password, gender, age, height_cm, weight_kg, activity_level, diet_preference)
            VALUES 
            (:username, :email, :password, :gender, :age, :height_cm, :weight_kg, :activity_level, 'Vegetarian')";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':username'       => $username,
            ':email'          => $email,
            ':password'       => $hashedPassword,
            ':gender'         => $gender,
            ':age'            => $age,
            ':height_cm'      => $height_cm,
            ':weight_kg'      => $weight_kg,
            ':activity_level' => $activity_level
        ]);

        // Redirect to login on success //
       
        header("Location: login.php?registered=success");
        exit();

    } catch (PDOException $e) {
        $_SESSION['register_old'] = $old;
        $_SESSION['register_field_errors'] = [
            'email' => 'Database error. Please try again.'
        ];
        header("Location: register.php");
        exit();
    }
}
?>

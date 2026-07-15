<?php
/**
 * Signup Processing
 * Handles new user registration
 */

// Enable output buffering to prevent header errors
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require_once('../config/functions.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');

    // Validation
    $errors = [];

    if (empty($name) || empty($email) || empty($password) || empty($phone)) {
        $errors[] = 'All fields are required';
    }

    if (!isValidEmail($email)) {
        $errors[] = 'Invalid email format';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }

    if (!preg_match('/^[0-9]{11}$/', $phone)) {
        $errors[] = 'Phone number must be 11 digits';
    }

    // Check if email already exists
    $query = "SELECT user_id FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = 'Email already registered';
    }

    // If there are errors, display them
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        redirect('signup.php', implode(', ', $errors), 'danger');
    }

    // Hash password
    $hashedPassword = hashPassword($password);

    // Insert user
    $query = "INSERT INTO users (name, email, password, phone, gender, status) VALUES (?, ?, ?, ?, ?, 'Active')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $name, $email, $hashedPassword, $phone, $gender);

    if ($stmt->execute()) {
        redirect('login.php', 'Registration successful! Please login.', 'success');
    } else {
        redirect('signup.php', 'Error during registration. Please try again.', 'danger');
    }
}

?>

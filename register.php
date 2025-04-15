<?php
session_start();
require 'config.php';

function sanitize($data) {
    return htmlspecialchars(trim($data));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitize($_POST['fullname']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (!preg_match("/^[a-zA-Z\s]{3,50}$/", $full_name)) $errors[] = "Invalid name.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";
    if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*?&]{6,}$/", $password)) $errors[] = "Password must be at least 6 characters and include a number.";

    if (empty($errors)) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "Email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $token = bin2hex(random_bytes(32));

            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, verification_token) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $token);

            if ($stmt->execute()) {
                header("Location: registration-success.php");
                exit;
            } else {
                $errors[] = "An error occurred during registration.";
            }
        }
    }

    foreach ($errors as $e) echo "<p style='color:red;'>$e</p>";
}
?>

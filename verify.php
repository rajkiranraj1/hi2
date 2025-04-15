<?php
require 'config.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ?");
    $stmt->bind_param("s", $token);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "Email verified. You can now log in.";
    } else {
        echo "Invalid or expired token.";
    }
}
?>

<?php
include 'includes/config.php';
header("Content-Type: application/json");

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Get email and new password
$email = trim($data['email'] ?? '');
$new_password = trim($data['new_password'] ?? '');

// Validate inputs
if (empty($email) || empty($new_password)) {
    echo json_encode([
        "status" => "error",
        "message" => "Email and new password are required"
    ]);
    exit;
}

// Hash the password securely
$hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);

// Update password in meetup_signup table
$stmt = $conn->prepare("UPDATE meetup_signup SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hashedPassword, $email);

if ($stmt->execute()) {
    // Optional: remove any OTP entry for this email
    $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $del->bind_param("s", $email);
    $del->execute();

    echo json_encode([
        "status" => "success",
        "message" => "Password updated successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update password"
    ]);
}
?>

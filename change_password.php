change_password.php
<?php
include 'includes/config.php'; // your DB connection
header("Content-Type: application/json");

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$current_password = $input['current_password'] ?? null;
$new_password = $input['new_password'] ?? null;
$confirm_password = $input['confirm_password'] ?? null;

if (!$current_password || !$new_password || !$confirm_password) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Check if new password and confirm password match
if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'New password and confirm password do not match']);
    exit;
}

// Fetch all users (or just one, depending on your setup)
$result = $conn->query("SELECT id, password FROM meetup_signup");

$updated = false;

while ($row = $result->fetch_assoc()) {
    $hashed_password = $row['password'];
    // Check if current password matches
    if (password_verify($current_password, $hashed_password)) {
        $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE meetup_signup SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_hashed_password, $row['id']);
        if ($stmt->execute()) {
            $updated = true;
            break; // password updated, exit loop
        }
    }
}

if ($updated) {
    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
}
?>

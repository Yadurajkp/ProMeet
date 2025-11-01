<?php
include 'includes/config.php';
header('Content-Type: application/json');

// Decode JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Read input fields
$usernameOrEmail = isset($data['usernameOrEmail']) ? trim($data['usernameOrEmail']) : '';
$password = isset($data['password']) ? trim($data['password']) : '';

if (empty($usernameOrEmail) || empty($password)) {
    echo json_encode([
        "status" => "error",
        "message" => "Username/Email and Password are required"
    ]);
    exit;
}

// ✅ Determine if input is an email or name
if (strpos($usernameOrEmail, '@') !== false) {
    $sql = "SELECT * FROM meetup_signup WHERE email = ? LIMIT 1";
} else {
    $sql = "SELECT * FROM meetup_signup WHERE name = ? LIMIT 1";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usernameOrEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    // ✅ Verify password
    if (password_verify($password, $row['password'])) {
        echo json_encode([
            "status" => "success",
            "message" => "Login successful",
            "user" => [
                "id" => $row['id'],
                "name" => $row['name'],
                "email" => $row['email'],
                "address" => $row['address'],
                "created_at" => $row['created_at'],
                "domain" => $row['domain'],
                "about" => $row['about'],
                "tagline" => $row['tagline'],
                "languages" => $row['languages'],
                "profile_image" => $row['profile_image']
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid password"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "User not found"
    ]);
}

$stmt->close();
$conn->close();
?>

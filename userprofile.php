<?php
include 'includes/config.php';
header("Content-Type: application/json");

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

// Read POST data (supports both form-data and JSON)
$input = $_POST;
if (empty($input)) {
    $json = file_get_contents('php://input');
    $input = json_decode($json, true) ?? [];
}

$action = $input['action'] ?? '';
$email  = $input['email'] ?? '';

// Validate required fields
if (!$action || !$email) {
    echo json_encode(["status" => "error", "message" => "Action or email missing"]);
    exit;
}

if ($action === 'get') {

    // Fetch profile details
    $query = "SELECT name, address, domain, about, tagline, languages, profile_image 
              FROM meetup_signup 
              WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            "status" => "success",
            "message" => "Profile fetched successfully",
            "data" => $row
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "User not found"]);
    }

    $stmt->close();

} elseif ($action === 'update') {

    // Get update fields safely
    $domain    = $input['domain'] ?? '';
    $about     = $input['about'] ?? '';
    $tagline   = $input['tagline'] ?? '';
    $languages = $input['languages'] ?? '';

    // Check if user exists
    $checkQuery = "SELECT email FROM meetup_signup WHERE email=?";
    $stmtCheck = $conn->prepare($checkQuery);
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        // User exists → update
        $query = "UPDATE meetup_signup 
                  SET domain=?, about=?, tagline=?, languages=? 
                  WHERE email=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $domain, $about, $tagline, $languages, $email);
        $stmt->execute();
        echo json_encode(["status" => "success", "message" => "Profile updated successfully"]);
        $stmt->close();
    } else {
        // User does not exist → create new
        $query = "INSERT INTO meetup_signup (email, domain, about, tagline, languages) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $email, $domain, $about, $tagline, $languages);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Profile created successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Profile creation failed"]);
        }
        $stmt->close();
    }

    $stmtCheck->close();

} else {
    echo json_encode(["status" => "error", "message" => "Invalid action"]);
}
?>

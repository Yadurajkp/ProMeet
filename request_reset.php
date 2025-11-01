<?php
include 'includes/config.php';
include 'includes/send_email.php'; // PHPMailer function
header('Content-Type: application/json');

// ✅ Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');

// 1️⃣ Validate empty inputs
if (empty($email)) {
    echo json_encode(["status" => "error", "message" => "Email is required"]);
    exit;
}

// 2️⃣ Check if email exists in meetup_signup
$stmt = $conn->prepare("SELECT id FROM meetup_signup WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res_signup = $stmt->get_result();

if ($res_signup->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Email not found"]);
    exit;
}

// 3️⃣ Generate OTP & expiry
$otp = rand(100000, 999999);

// ✅ Let MySQL handle expiry to avoid timezone mismatch
$stmt3 = $conn->prepare("
    INSERT INTO password_resets (email, otp, expires_at) 
    VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))
");
$stmt3->bind_param("si", $email, $otp); // otp as INT
$stmt3->execute();

// 4️⃣ Send OTP email
if (sendOtpEmail($email, $otp)) {
    echo json_encode(["status" => "success", "message" => "OTP sent successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to send OTP"]);
}
?>

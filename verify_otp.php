<?php
header("Content-Type: application/json");
include 'includes/config.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data["email"] ?? '');
$otp   = trim($data["otp"] ?? '');

if (empty($email) || empty($otp)) {
    echo json_encode(["status"=>"error","message"=>"Email and OTP are required"]);
    exit;
}

// Verify using MySQL's clock
$stmt = $conn->prepare("
    SELECT * FROM password_resets 
    WHERE email=? AND otp=? AND expires_at >= NOW()
    ORDER BY id DESC LIMIT 1
");
$stmt->bind_param("ss", $email, $otp);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status"=>"success","message"=>"OTP verified successfully"]);
} else {
    echo json_encode(["status"=>"error","message"=>"Invalid or expired OTP"]);
}
?>

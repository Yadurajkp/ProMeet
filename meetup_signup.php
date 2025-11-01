<?php
include 'includes/config.php'; // Adjust path if needed
header('Content-Type: application/json');

// Decode JSON input
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = mysqli_real_escape_string($conn, $data['name']);
    $email    = mysqli_real_escape_string($conn, $data['email']);
    $address  = mysqli_real_escape_string($conn, $data['address']);
    $password = mysqli_real_escape_string($conn, $data['password']);
    $cpassword = mysqli_real_escape_string($conn, $data['cpassword']);

    // Check if passwords match
    if ($password !== $cpassword) {
        echo json_encode(["status" => "error", "message" => "Passwords do not match"]);
        exit;
    }

    // Check if email already exists
    $checkEmail = $conn->query("SELECT id FROM meetup_signup WHERE email='$email'");
    if ($checkEmail->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email already registered"]);
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $sql = "INSERT INTO meetup_signup (name, email, address, password) 
            VALUES ('$name', '$email', '$address', '$hashedPassword')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Signup successful"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    }
}
?>

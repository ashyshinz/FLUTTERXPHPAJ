<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS requests (Common for Flutter/Browsers)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 1. Database Connection - Using Port 3306 as set in your my.ini
$host = "localhost";
$user = "root";
$pass = "";
$db   = "alumni_db";
$port = 3306; 

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

// 2. Get JSON Data from Flutter
$data = json_decode(file_get_contents("php://input"));

// Validation
if (!$data || empty($data->email) || empty($data->password) || empty($data->full_name) || empty($data->role)) {
    echo json_encode(["status" => "error", "message" => "Please fill in all fields, including role"]);
    exit();
}

$full_name = mysqli_real_escape_string($conn, $data->full_name);
$email = mysqli_real_escape_string($conn, $data->email);
$password = mysqli_real_escape_string($conn, $data->password); 
$role = mysqli_real_escape_string($conn, $data->role); 

// 3. Check if email exists
$checkEmail = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");

if (mysqli_num_rows($checkEmail) > 0) {
    echo json_encode(["status" => "error", "message" => "Email already registered"]);
} else {
    /* 4. INSERT WITH VERIFICATION STATUS
       is_verified = 0 (Pending) requires Admin approval.
    */
    $sql = "INSERT INTO users (full_name, email, password, role, is_verified) 
            VALUES ('$full_name', '$email', '$password', '$role', 0)";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            "status" => "success", 
            "message" => "Registration Successful! Please wait for Superuser approval."
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "SQL Error: " . mysqli_error($conn)]);
    }
}

mysqli_close($conn);
?>
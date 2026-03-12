<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database Connection
$conn = new mysqli("localhost", "root", "", "alumni_db", 3306);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "DB Connection Failed: " . $conn->connect_error
    ]);
    exit();
}

// Get JSON input from Flutter
$data = json_decode(file_get_contents("php://input"), true);

if ($data && !empty($data['email']) && !empty($data['password'])) {

    $email = $data['email'];
    $password = $data['password'];

    // Prepare statement
    $stmt = $conn->prepare("SELECT id, full_name, password, role, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($userData = $result->fetch_assoc()) {

        // Password check (Plain text comparison)
        if ($password === $userData['password']) {

            // Check verification
            if (isset($userData['is_verified']) && (int)$userData['is_verified'] === 0) {

                echo json_encode([
                    "status" => "error",
                    "message" => "Account pending. Please wait for Admin verification."
                ]);

            } else {

                $userId = $userData['id'];

                // Update last_login safely
                $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->bind_param("i", $userId);
                $updateStmt->execute();
                $updateStmt->close();

                // Role formatting
                $role = strtolower($userData['role']);
                $role_display = "Alumni Member";

                if ($email === "superuser@jmc.edu.ph" || $role === "superuser") {
                    $role_display = "Superuser Access";
                } elseif ($role === "admin") {
                    $role_display = "System Administrator";
                } elseif ($role === "dean") {
                    $role_display = "College Dean";
                }

                echo json_encode([
                    "status" => "success",
                    "id" => $userId,
                    "full_name" => $userData['full_name'],
                    "email" => $email,
                    "role" => $role,
                    "role_display" => $role_display,
                    "message" => "Login Successful"
                ]);
            }

        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Incorrect password."
            ]);
        }

    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No account found with that email."
        ]);
    }

    $stmt->close();

} else {
    echo json_encode([
        "status" => "error",
        "message" => "Incomplete login data."
    ]);
}

$conn->close();
?>
<?php
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Create a connection to the database
$conn = new mysqli("localhost", "root", "", "alumni_db", 3306);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed"]));
}

// Get the raw POST data (assuming JSON)
$data = json_decode(file_get_contents("php://input"));

// Check if 'id' is provided in the request
if (isset($data->id)) {
    $id = intval($data->id); // Ensure ID is an integer (for security)
    
    // Prepare the SQL statement to update the user's verification status
    $sql = "UPDATE users SET is_verified = 1 WHERE id = ?";
    
    // Initialize the prepared statement
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        echo json_encode(["status" => "error", "message" => "Failed to prepare the SQL statement"]);
        exit();
    }

    // Bind the parameter to the prepared statement
    $stmt->bind_param("i", $id); // "i" for integer type
    
    // Execute the prepared statement
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "User verified successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
    
    // Close the prepared statement
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "No ID provided"]);
}

// Close the database connection
$conn->close();
?>
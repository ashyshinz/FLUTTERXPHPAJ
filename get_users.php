<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
$host = "localhost";
$user = "root"; // Default XAMPP user
$pass = "";     // Default XAMPP password
$db   = "alumni_db"; // CHANGE THIS to your actual DB name

$conn = new mysqli($host, $user, $pass, $db, 3306);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}


// Query to get users and their activities as a comma-separated string
$sql = "SELECT u.*, GROUP_CONCAT(a.activity SEPARATOR '||') as activities 
        FROM users u 
        LEFT JOIN user_activities a ON u.id = a.user_id 
        GROUP BY u.id";

$result = $conn->query($sql);
$users = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Turn the activities string back into a List/Array for Flutter
        $row['activities'] = $row['activities'] ? explode('||', $row['activities']) : [];
        $users[] = $row;
    }
}

echo json_encode($users);
$conn->close();
?>
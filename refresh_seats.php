<?php
session_start();
header('Content-Type: application/json');

$host = 'localhost'; 
$dbUser = 'root'; 
$dbPass = ''; 
$dbName = 'registration';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

$input = json_decode(file_get_contents('php://input'), true);
$city = $input['city'];
$movie = $input['movie'];
$day = $input['day'];
$time = $input['time'];

$sql = "SELECT seat_number FROM seats WHERE city = ? AND movie = ? AND day = ? AND screening_time = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die(json_encode(['error' => "Error preparing statement: " . $conn->error]));
}

$stmt->bind_param("ssss", $city, $movie, $day, $time);
$stmt->execute();
$result = $stmt->get_result();

$reservedSeats = [];
while ($row = $result->fetch_assoc()) {
    $reservedSeats[] = $row['seat_number'];
}

$stmt->close();
$conn->close();

echo json_encode(['reservedSeats' => $reservedSeats]);
?>
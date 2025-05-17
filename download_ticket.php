<?php
session_start();

require('fpdf.php');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die("Unauthorized access.");
}

$conn = new mysqli('localhost', 'root', '', 'registration');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_email = $_SESSION['email'];

$city = isset($_GET['city']) ? $_GET['city'] : '';
$movie = isset($_GET['movie']) ? $_GET['movie'] : '';
$day = isset($_GET['day']) ? $_GET['day'] : '';
$time = isset($_GET['time']) ? $_GET['time'] : '';
$seat = isset($_GET['seat']) ? $_GET['seat'] : '';

if (!$city || !$movie || !$day || !$time || !$seat) {
    die("Missing ticket details.");
}

$stmt = $conn->prepare("SELECT * FROM seats WHERE user_email = ? AND city = ? AND movie = ? AND day = ? AND screening_time = ? AND seat_number = ?");
$stmt->bind_param("ssssss", $user_email, $city, $movie, $day, $time, $seat);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Ticket not found or unauthorized.");
}

$ticket = $result->fetch_assoc();

$movies = [
    'Joker: Folie à Deux' => 12.00,
    'Spider-Man: Homecoming' => 10.00,
    'Terrifier 3' => 15.00,
    'Venom: Let There Be Carnage' => 14.00,
    'Cars 2' => 8.00,
    'Smile 2' => 9.00
];

$price = isset($movies[$movie]) ? number_format($movies[$movie], 2) : "N/A";

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(204, 8, 17);
$pdf->Cell(0, 10, 'Cinema Ticket', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 14);
$pdf->SetTextColor(0, 0, 0);

$pdf->Cell(50, 10, 'City:', 0, 0);
$pdf->Cell(0, 10, $city, 0, 1);

$pdf->Cell(50, 10, 'Movie:', 0, 0);
$pdf->Cell(0, 10, $movie, 0, 1);

$pdf->Cell(50, 10, 'Day:', 0, 0);
$pdf->Cell(0, 10, $day, 0, 1);

$pdf->Cell(50, 10, 'Time:', 0, 0);
$pdf->Cell(0, 10, $time, 0, 1);

$pdf->Cell(50, 10, 'Seat Number:', 0, 0);
$pdf->Cell(0, 10, $seat, 0, 1);

$pdf->Cell(50, 10, 'Price:', 0, 0);
$pdf->Cell(0, 10, "$" . $price, 0, 1);

$pdf->Ln(15);
$pdf->SetFont('Arial', 'I', 12);
$pdf->Cell(0, 10, 'Thank you for your purchase!', 0, 1, 'C');

$pdf->Output('D', 'Cinema_Ticket_' . $movie . '_' . $seat . '.pdf');

$stmt->close();
$conn->close();
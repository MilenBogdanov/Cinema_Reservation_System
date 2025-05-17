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
$day   = isset($_GET['day']) ? $_GET['day'] : '';
$time  = isset($_GET['time']) ? $_GET['time'] : '';
$seat  = isset($_GET['seat']) ? $_GET['seat'] : '';

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

$movies = [
    'Joker: Folie à Deux' => 12.00,
    'Spider-Man: Homecoming' => 10.00,
    'Terrifier 3' => 15.00,
    'Venom: Let There Be Carnage' => 14.00,
    'Cars 2' => 8.00,
    'Smile 2' => 9.00
];

$price = isset($movies[$movie]) ? number_format($movies[$movie], 2) : "N/A";

class PDF extends FPDF {
    var $angle = 0;
    function Header() {
        // Background
        $this->SetFillColor(30, 30, 30);
        $this->Rect(0, 0, 210, 297, 'F');

        // Title
        $this->SetFont('Arial', 'B', 26);
        $this->SetTextColor(255, 215, 0); // Gold
        $this->Cell(0, 20, 'CINEMA-ISLAND TICKET', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(255, 215, 0);
        $this->Cell(0, 10, 'Cinema-Island | Unleash the Experience | www.cinema-island.com', 0, 0, 'C');
    }

    function FancyBox($label, $value) {
        $this->SetFillColor(45, 45, 45);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(55, 12, strtoupper($label), 0, 0, 'L', true);
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 12, strtoupper($value), 0, 1, 'L', true);
        $this->Ln(1);
    }

    function RedDivider() {
        $this->SetDrawColor(255, 0, 0);
        $this->SetLineWidth(0.7);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(6);
    }

    function Barcode($text) {
    $this->SetFont('Arial', 'B', 14);
    $this->SetTextColor(255, 0, 0);
    $code = strtoupper(substr(md5($text), 0, 10));
    $this->Cell(0, 15, 'TICKET CODE: ' . $code, 0, 1, 'C');

    
    $this->Ln(20);
}

    function Watermark() {
    $this->SetFont('Arial', 'B', 50);
    $this->SetTextColor(255, 0, 0, 10);
    $this->Rotate(45, 55, 220);
    $this->Text(30, 240, 'CINEMA ISLAND');
    $this->Rotate(0);
}

    function Rotate($angle, $x = -1, $y = -1) {
        if ($x == -1) $x = $this->x;
        if ($y == -1) $y = $this->y;
        if ($this->angle != 0)
            $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy));
        }
    }

    function _endpage() {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetY(45);

$pdf->Watermark();

$pdf->RedDivider();
$pdf->FancyBox('City:', $city);
$pdf->FancyBox('Movie:', $movie);
$pdf->FancyBox('Date:', $day);
$pdf->FancyBox('Time:', $time);
$pdf->FancyBox('Seat:', $seat);
$pdf->FancyBox('Price:', "$" . $price);
$pdf->RedDivider();

$pdf->Barcode($user_email . $movie . $seat);

$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 10, 'PLEASE ARRIVE AT LEAST 15 MINUTES EARLY', 0, 1, 'C');

$pdf->Output('D', 'Cinema_Ticket_' . str_replace(' ', '_', $movie) . '_' . $seat . '.pdf');

$stmt->close();
$conn->close();
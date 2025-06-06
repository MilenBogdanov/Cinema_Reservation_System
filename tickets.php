<?php 
session_start();
 
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}
 
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    echo '<script>localStorage.clear(); window.location.href = "index.php";</script>';
    exit;
}
 
$host = 'localhost'; 
$dbUser = 'root'; 
$dbPass = ''; 
$dbName = 'registration';
 
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
 
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
 
$weeklyPrograms = [
    'Varna' => [
        'Monday' => [
            'Cars 2' => ['12:00', '15:30', '19:00'],
            'Joker: Folie à Deux' => ['18:00', '21:30'],
            'Venom: Let There Be Carnage' => ['20:00'],
            'Spider-Man: Homecoming' => ['22:00'],
        ],
        'Tuesday' => [
            'Spider-Man: Homecoming' => ['14:00', '17:30', '21:00'],
            'Smile 2' => ['20:00', '22:30'],
            'Terrifier 3' => ['19:00'],
            'Joker: Folie à Deux' => ['22:00'],
        ],
        'Wednesday' => [
            'Venom: Let There Be Carnage' => ['16:00', '18:30', '21:00'],
            'Joker: Folie à Deux' => ['22:30'],
            'Spider-Man: Homecoming' => ['14:00'],
            'Smile 2' => ['17:00'],
        ],
        'Thursday' => [
            'Terrifier 3' => ['19:00', '20:30'],
            'Spider-Man: Homecoming' => ['22:00', '00:30'],
            'Joker: Folie à Deux' => ['16:00'],
            'Venom: Let There Be Carnage' => ['21:00'],
        ],
        'Friday' => [
            'Smile 2' => ['17:00', '19:30'],
            'Cars 2' => ['15:00'],
            'Joker: Folie à Deux' => ['20:00'],
            'Terrifier 3' => ['22:30'],
        ],
    ],
    'Plovdiv' => [
        'Monday' => [
            'Spider-Man: Homecoming' => ['15:00', '18:30'],
            'Joker: Folie à Deux' => ['20:00'],
            'Venom: Let There Be Carnage' => ['22:00'],
            'Terrifier 3' => ['23:30'],
        ],
        'Tuesday' => [
            'Smile 2' => ['15:00', '18:00'],
            'Cars 2' => ['20:30'],
            'Joker: Folie à Deux' => ['22:00'],
            'Venom: Let There Be Carnage' => ['23:30'],
        ],
        'Wednesday' => [
            'Terrifier 3' => ['16:00', '19:00'],
            'Smile 2' => ['21:30'],
            'Cars 2' => ['22:30'],
            'Joker: Folie à Deux' => ['23:45'],
        ], 
        'Thursday' => [
            'Venom: Let There Be Carnage' => ['15:00', '19:00'],
            'Smile 2' => ['22:00'],
            'Terrifier 3' => ['23:30'],
            'Cars 2' => ['01:00'],
        ],
        'Friday' => [
            'Joker: Folie à Deux' => ['18:00', '21:00'],
            'Venom: Let There Be Carnage' => ['22:30'],
        ],
        'Saturday' => [
            'Spider-Man: Homecoming' => ['11:00', '14:00'],
            'Smile 2' => ['16:30'],
            'Joker: Folie à Deux' => ['19:30'],
            'Terrifier 3' => ['22:00'],
        ],
        'Sunday' => [
            'Venom: Let There Be Carnage' => ['12:00', '15:00'],
            'Joker: Folie à Deux' => ['18:30'],
        ],
    ],
    'Sofia' => [
        'Monday' => [
            'Spider-Man: Homecoming' => ['15:00', '18:30'],
            'Joker: Folie à Deux' => ['20:00'],
            'Venom: Let There Be Carnage' => ['22:00'],
            'Terrifier 3' => ['23:30'],
        ],
        'Tuesday' => [
            'Smile 2' => ['15:00', '18:00'],
            'Cars 2' => ['20:30'],
            'Joker: Folie à Deux' => ['22:00'],
            'Venom: Let There Be Carnage' => ['23:30'],
        ],
        'Wednesday' => [
            'Terrifier 3' => ['16:00', '19:00'],
            'Smile 2' => ['21:30'],
            'Cars 2' => ['22:30'],
            'Joker: Folie à Deux' => ['23:45'],
        ],
        'Thursday' => [
            'Venom: Let There Be Carnage' => ['15:00', '19:00'],
            'Smile 2' => ['22:00'],
            'Terrifier 3' => ['23:30'],
            'Cars 2' => ['01:00'],
        ],
        'Friday' => [
            'Joker: Folie à Deux' => ['18:00', '21:00'],
            'Venom: Let There Be Carnage' => ['22:30'],
        ],
        'Saturday' => [
            'Cars 2' => ['11:00', '14:00'],
            'Spider-Man: Homecoming' => ['16:30'],
            'Joker: Folie à Deux' => ['19:00'],
            'Venom: Let There Be Carnage' => ['21:30'],
        ],
    ]
];
 
$movies = [
    'Joker: Folie à Deux' => 12.00,
    'Spider-Man: Homecoming' => 10.00,
    'Terrifier 3' => 15.00,
    'Venom: Let There Be Carnage' => 14.00,
    'Cars 2' => 8.00,
    'Smile 2' => 9.00
];
 

$totalPrice = 0;
$reservedSeats = [];
 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get POST data
    $city = $_POST['city'];
    $movie = $_POST['movie'];
    $day = $_POST['day'];
    $time = $_POST['time'];
    $tickets = $_POST['tickets'];
    $seats = isset($_POST['seats']) ? explode(',', $_POST['seats']) : [];
    $userEmail = $_SESSION['email'];
    $message = "";

    if (isset($movies[$movie])) {
        $totalPrice = $movies[$movie] * $tickets;
    }
 

if ($totalPrice > 0 && !empty($seats)) {
    foreach ($seats as $seat) {
        
        $sql = "INSERT INTO seats (city, movie, day, screening_time, seat_number, user_email) VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE user_email = ?";
 
        $stmt = $conn->prepare($sql);
 
        
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
 
        
        if (!$stmt->bind_param("sssssss", $city, $movie, $day, $time, $seat, $userEmail, $userEmail)) {
            die("Error binding parameters: " . $stmt->error);
        }
 
        
        if (!$stmt->execute()) {
            die("Execute error: " . $stmt->error);
        }
 
        
        $stmt->close();
    }
 
    
    $purchaseMessage = "Your tickets for '$movie' in '$city' have been successfully booked.";
}
}
 
 
 

if ($_SERVER["REQUEST_METHOD"] == "POST" && $totalPrice > 0) {
    $sql = "SELECT seat_number FROM seats WHERE city = ? AND movie = ? AND day = ? AND screening_time = ?";
    $stmt = $conn->prepare($sql);
 
    
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
 
    $stmt->bind_param("ssss", $city, $movie, $day, $time);
    $stmt->execute();
    $result = $stmt->get_result();
 
    while ($row = $result->fetch_assoc()) {
        $reservedSeats[] = $row['seat_number'];
    }
 
    $stmt->close();
}elseif ($_SERVER["REQUEST_METHOD"] != "POST") {
    
    if (isset($_POST['city'])) {
        $city = $_POST['city'];
 
        
        $movie = isset($_POST['movie']) ? $_POST['movie'] : '';
        $day = isset($_POST['day']) ? $_POST['day'] : '';
        $time = isset($_POST['time']) ? $_POST['time'] : '';
 
        
        if ($movie && $day && $time) {
            $sql = "SELECT seat_number FROM seats WHERE city = ? AND movie = ? AND day = ? AND screening_time = ?";
            $stmt = $conn->prepare($sql);
 
            if ($stmt === false) {
                die("Error preparing statement: " . $conn->error);
            }
 
            $stmt->bind_param("ssss", $city, $movie, $day, $time);
            $stmt->execute();
            $result = $stmt->get_result();
 
            while ($row = $result->fetch_assoc()) {
                $reservedSeats[] = $row['seat_number'];
            }
 
            $stmt->close();
        }
    }
}
 

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    
    $city = isset($_POST['city']) ? $_POST['city'] : '';
    $movie = isset($_POST['movie']) ? $_POST['movie'] : '';
    $day = isset($_POST['day']) ? $_POST['day'] : '';
    $time = isset($_POST['time']) ? $_POST['time'] : '';
 
    
    if ($city && $movie && $day && $time) {
        $sql = "SELECT seat_number FROM seats WHERE city = ? AND movie = ? AND day = ? AND screening_time = ?";
        $stmt = $conn->prepare($sql);
 
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
 
        $stmt->bind_param("ssss", $city, $movie, $day, $time);
        $stmt->execute();
        $result = $stmt->get_result();
 
        while ($row = $result->fetch_assoc()) {
            $reservedSeats[] = $row['seat_number'];
        }
 
        $stmt->close();
    }
}
 
if (isset($purchaseMessage)) {
    
    $purchaseConfirmation = "
        <div id='confirmationPopup' style='
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            font-family: Arial, sans-serif;
        '>
            <div style='
                background-color: rgba(51, 51, 51, 0.9); /* Semi-transparent dark background */
                padding: 30px;
                border-radius: 8px;
                max-width: 600px;
                width: 90%;
                color: #fff;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                text-align: left;
            '>
                <h2 style='color: #ff4444; text-align: center;'>Ticket Booking Confirmation</h2><br>
                <p>Dear customer,</p>
                <p>Thank you for booking tickets with Cinema-Island!</p><br>
                <p><strong>Movie:</strong> $movie</p>
                <p><strong>City:</strong> $city</p>
                <p><strong>Day:</strong> $day</p>
                <p><strong>Time:</strong> $time</p>
                <p><strong>Tickets:</strong> $tickets</p>
                <p><strong>Total Price:</strong> $" . number_format($totalPrice, 2) . "</p>
                <br>
                <p>We look forward to seeing you!</p>
                <button onclick='closePopup()' style='
                    background-color: #ff4444;
                    color: #fff;
                    padding: 10px 20px;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    margin-top: 20px;
                    font-size: 16px;
                    display: block;
                    width: 100%;
                    text-align: center;
                '>Close</button>
            </div>
        </div>
 
        <script>
            // Function to close the popup
            function closePopup() {
                document.getElementById('confirmationPopup').style.display = 'none';
            }
        </script>
    ";
 
    
    echo $purchaseConfirmation;
}
 
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Tickets</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="tickets.css">
</head>
<body>
 
    
    <div class="navbar-container">
        <div class="navbar">
            <a href="main.php">
                <img src="images/logo.png" alt="Cinema-Island Logo" class="logo">
            </a>
            <div class="navbar-title">Cinema-Island</div>
            <div class="navbar-links">
                <a href="main.php">Home</a>
                <a href="main.php#now-playing">Movies</a>
                <div class="dropdown">
                    <a href="#">Weekly Program</a>
                    <ul class="dropdown-menu">
                        <li><a href="varna.php">Varna</a></li>
                        <li><a href="sofia.php">Sofia</a></li>
                        <li><a href="plovdiv.php">Plovdiv</a></li>
                    </ul>
                </div>
                <a href="main.php#coming-soon">Coming Soon</a>
                <a href="gallery.php">Gallery</a>
                <a href="contacts.php">Contacts</a>
            </div>
            <form method="post" class="logout">
                <button type="submit" name="logout">Logout</button>
            </form>
        </div>
    </div>
 
    <div class="container">
        <h1>Book Tickets</h1>
        <br>
        
        <div class="movie-prices">
            <h3>Movie Prices</h3>
            <ul>
                <?php foreach ($movies as $movieName => $price): ?>
                    <li><?= $movieName ?>: $<?= number_format($price, 2) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
 
        
        <form method="post">
            <div class="movie-selection">
                <div class="form-group">
                    <label for="city">City:</label>
                    <select name="city" id="city" required>
                        <?php foreach ($weeklyPrograms as $cityName => $program): ?>
                            <option value="<?= $cityName ?>" <?= isset($city) && $city == $cityName ? 'selected' : '' ?>><?= $cityName ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
 
                <div class="form-group">
                    <label for="movie">Movie:</label>
                    <select name="movie" id="movie" required>
                        <?php foreach ($movies as $movieName => $price): ?>
                            <option value="<?= $movieName ?>" <?= isset($movie) && $movie == $movieName ? 'selected' : '' ?>><?= $movieName ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
 
                <div class="form-group">
                    <label for="day">Day:</label>
                    <select name="day" id="day" required>
                        
                    </select>
                    <div id="dayMessage" class="message" style="display: none;">No options available.</div>
                </div>
 
                <div class="form-group">
                    <label for="time">Time:</label>
                    <select name="time" id="time" required>
                        
                    </select>
                    <div id="timeMessage" class="message" style="display: none;">No options available.</div>
                </div>
 
                <div class="form-group">
                    <label for="tickets">Number of Tickets:</label>
                    <input type="number" name="tickets" id="tickets" value="<?= isset($tickets) ? $tickets : 1 ?>" min="1" max="10" required>
                </div>
            </div>
 
            
<div class="seat-selection">
    <h3>Select Seats</h3>
    <div id="seatsContainer">
        <?php for ($i = 1; $i <= 104; $i++): ?>
            <div class="seat <?= in_array($i, $reservedSeats) ? 'reserved' : '' ?>" data-seat="<?= $i ?>"><?= $i ?></div>
        <?php endfor; ?>
    </div>
    <input type="hidden" name="seats" id="selectedSeats" value="<?= isset($seats) ? implode(',', $seats) : '' ?>">
</div>
 
            <div class="summary" style="text-align: center;">
    <p>Total Price: $<span id="totalPrice"><?= number_format($totalPrice, 2) ?></span></p><br>
                
<form id="paymentForm">
    <div class="payment-section" style="background: black; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); max-width: 400px; margin: auto;">
        <h3 style="text-align: center; color: red;">Please Enter Your Credit Card Details:</h3><br>
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="cardName" style="display: block; font-weight: bold;">Cardholder Name:</label>
            <input type="text" id="cardName" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="cardNumber" style="display: block; font-weight: bold;">Card Number:</label>
            <input type="text" id="cardNumber" pattern="\d{16}" maxlength="16" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
        </div>
        <div class="form-group" style="margin-bottom: 15px; display: flex; justify-content: space-between;">
            <div style="width: 48%;">
                <label for="expiryDate" style="display: block; font-weight: bold;">Expiry Date:</label>
                <input type="month" id="expiryDate" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            </div>
            <div style="width: 48%;">
                <label for="cvv" style="display: block; font-weight: bold;">CVV:</label>
                <input type="text" id="cvv" pattern="\d{3}" maxlength="3" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            </div>
        </div>
    </div>

    
    <button type="submit" id="bookTicketsBtn" style="width: 100%; padding: 12px; background-color: red; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin-top: 15px;">
        Book Tickets
    </button>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const today = new Date();
        const year = today.getFullYear();
        let month = today.getMonth() + 1;
        month = month < 10 ? `0${month}` : month;

        const expiryInput = document.getElementById('expiryDate');
        expiryInput.setAttribute('min', `${year}-${month}`);

        
        expiryInput.addEventListener('input', function () {
            const [selectedYear, selectedMonth] = expiryInput.value.split('-').map(Number);
            if (selectedYear < year || (selectedYear === year && selectedMonth < month)) {
                alert('You cannot select an expired date.');
                expiryInput.value = `${year}-${month}`;
            }
        });
    });

    function validatePaymentForm() {
        const cardName = document.getElementById('cardName').value.trim();
        const cardNumber = document.getElementById('cardNumber').value.trim();
        const expiryDate = document.getElementById('expiryDate').value;
        const cvv = document.getElementById('cvv').value.trim();
        const bookTicketsBtn = document.getElementById('bookTicketsBtn');

        
        const today = new Date();
        const currentYear = today.getFullYear();
        const currentMonth = today.getMonth() + 1;
        const [year, month] = expiryDate ? expiryDate.split('-').map(Number) : [0, 0];

        
        const isExpiryValid = year > currentYear || (year === currentYear && month >= currentMonth);

        const isValid = cardName !== '' &&
                        /^\d{16}$/.test(cardNumber) &&
                        expiryDate !== '' && isExpiryValid &&
                        /^\d{3}$/.test(cvv);

        
        if (isValid) {
            bookTicketsBtn.disabled = false;
            bookTicketsBtn.style.backgroundColor = '#28a745';
        } else {
            bookTicketsBtn.disabled = true;
            bookTicketsBtn.style.backgroundColor = 'red';
        }

        return isValid;
    }

    document.querySelectorAll('.payment-section input').forEach(input => {
        input.addEventListener('input', validatePaymentForm);
    });

    document.getElementById('paymentForm').addEventListener('submit', function(event) {
        if (!validatePaymentForm()) {
            event.preventDefault();
            alert('Please fill in all payment details before booking tickets.');
        }
    });
</script>
            </div>
        </form>
    </div>
 
    <script>
        const weeklyPrograms = <?= json_encode($weeklyPrograms) ?>;
const moviePrices = <?= json_encode($movies) ?>;
let ticketCount = parseInt(document.getElementById('tickets').value) || 1;
 
function setupSeatClickListeners() {
    document.querySelectorAll('.seat').forEach(seat => {
        seat.addEventListener('click', () => {
            if (!seat.classList.contains('reserved')) {
                seat.classList.toggle('selected');
                updateSelectedSeats();
            }
        });
    });
}
 
function updateSelectedSeats() {
    const selectedSeats = Array.from(document.querySelectorAll('.seat.selected'));
    const maxSeats = parseInt(document.getElementById('tickets').value) || 1;
 
    if (selectedSeats.length > maxSeats) {
        selectedSeats.slice(maxSeats).forEach(seat => seat.classList.remove('selected'));
    }
 
    const selectedSeatNumbers = selectedSeats.map(seat => seat.dataset.seat);
    document.getElementById('selectedSeats').value = selectedSeatNumbers.join(',');
}
 
function updateTotalPrice() {
    const selectedMovie = document.getElementById('movie').value;
    const ticketCount = parseInt(document.getElementById('tickets').value) || 1;
    const pricePerTicket = moviePrices[selectedMovie] || 0;
    const totalPrice = pricePerTicket * ticketCount;
 
    document.getElementById('totalPrice').textContent = totalPrice.toFixed(2);
}
 
document.addEventListener('DOMContentLoaded', () => {
    setupSeatClickListeners();
    updateDayOptions();
    updateTotalPrice();
});
 
function updateDayOptions() {
    const city = document.getElementById('city').value;
    const daySelect = document.getElementById('day');
    const dayMessage = document.getElementById('dayMessage');
 
    daySelect.innerHTML = '';
    dayMessage.style.display = 'none';
 
    if (weeklyPrograms[city]) {
        const program = weeklyPrograms[city];
 
        for (const day in program) {
            const option = document.createElement('option');
            option.value = day;
            option.textContent = day;
            daySelect.appendChild(option);
        }
 
        updateTimeOptions();
    } else {
        dayMessage.style.display = 'block';
        dayMessage.textContent = 'No options available.';
    }
}
 
function updateTimeOptions() {
    const city = document.getElementById('city').value;
    const movie = document.getElementById('movie').value;
    const day = document.getElementById('day').value;
    const timeSelect = document.getElementById('time');
    const timeMessage = document.getElementById('timeMessage');
 
    timeSelect.innerHTML = '';
    timeMessage.style.display = 'none';
 
    const times = (weeklyPrograms[city] && weeklyPrograms[city][day] && weeklyPrograms[city][day][movie]) || [];
 
    if (times.length > 0) {
        times.forEach(time => {
            const option = document.createElement('option');
            option.value = time;
            option.textContent = time;
            timeSelect.appendChild(option);
        });
    } else {
        timeMessage.style.display = 'block';
        timeMessage.textContent = 'No times available.';
    }
}
 

document.getElementById('city').addEventListener('change', () => {
    updateDayOptions();
    refreshSeats();
});
document.getElementById('movie').addEventListener('change', () => {
    updateTimeOptions();
    refreshSeats();
});
document.getElementById('day').addEventListener('change', () => {
    updateTimeOptions();
    refreshSeats();
});
 
 
function refreshSeats() {
    const city = document.getElementById('city').value;
    const movie = document.getElementById('movie').value;
    const day = document.getElementById('day').value;
    const time = document.getElementById('time').value;
 
    fetch('refresh_seats.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ city, movie, day, time })
    })
    .then(response => response.json())
    .then(data => {
        document.querySelectorAll('.seat').forEach(seat => seat.classList.remove('reserved', 'selected'));
        data.reservedSeats.forEach(seatNumber => {
            const seatElement = document.querySelector(`.seat[data-seat="${seatNumber}"]`);
            if (seatElement) seatElement.classList.add('reserved');
        });
    })
    .catch(error => console.error('Error refreshing seats:', error));
}
 
function calculateTotalPrice() {
            const movieSelect = document.getElementById('movie');
            const ticketsInput = document.getElementById('tickets');
            const totalPriceDisplay = document.getElementById('totalPrice');
 
            const selectedMovie = movieSelect.value;
            const ticketCount = parseInt(ticketsInput.value) || 1;
 
 
            const pricePerTicket = moviePrices[selectedMovie] || 0;
            const totalPrice = pricePerTicket * ticketCount;
 
            
            totalPriceDisplay.textContent = totalPrice.toFixed(2);
        }
 
        
        document.getElementById('movie').addEventListener('change', calculateTotalPrice);
        document.getElementById('tickets').addEventListener('change', calculateTotalPrice);
 
        
        calculateTotalPrice();
    </script>
 
    <<script>
    
    window.addEventListener('load', function() {
        
        <?php if (isset($purchaseMessage)): ?>
            alert("<?= $purchaseMessage ?> This information has been saved in the database.");
        <?php endif; ?>
    });
</script>
</body>
 
<footer class="footer">
    <p>© 2025 Cinema-Island. All rights reserved.</p>
</footer>
</html>
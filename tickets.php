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
 
// Initialize total price variable
$totalPrice = 0;
$reservedSeats = [];
 
// Handle form submission for ticket selection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get POST data
    $city = $_POST['city'];
    $movie = $_POST['movie'];
    $day = $_POST['day'];
    $time = $_POST['time'];
    $tickets = $_POST['tickets'];
    $seats = isset($_POST['seats']) ? explode(',', $_POST['seats']) : [];
    $userEmail = $_SESSION['email']; // Assuming user email is stored in session
    $message = "";
    // Calculate total price
    if (isset($movies[$movie])) {
        $totalPrice = $movies[$movie] * $tickets;
    }
 
    // Reserve the seats in the database
if ($totalPrice > 0 && !empty($seats)) {
    foreach ($seats as $seat) {
        // Prepare the SQL statement
        $sql = "INSERT INTO seats (city, movie, day, screening_time, seat_number, user_email) VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE user_email = ?";
 
        $stmt = $conn->prepare($sql);
 
        // Check if prepare() was successful
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
 
        // Bind parameters
        if (!$stmt->bind_param("sssssss", $city, $movie, $day, $time, $seat, $userEmail, $userEmail)) {
            die("Error binding parameters: " . $stmt->error);
        }
 
        // Execute the statement
        if (!$stmt->execute()) {
            die("Execute error: " . $stmt->error);
        }
 
        // Close the statement after execution
        $stmt->close();
    }
 
    // Set a message indicating success
    $purchaseMessage = "Your tickets for '$movie' in '$city' have been successfully booked.";
}
}
 
 
 
// Fetch reserved seats for the current movie and screening in the selected city
if ($_SERVER["REQUEST_METHOD"] == "POST" && $totalPrice > 0) {
    $sql = "SELECT seat_number FROM seats WHERE city = ? AND movie = ? AND day = ? AND screening_time = ?";
    $stmt = $conn->prepare($sql);
 
    // Check if prepare() was successful
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
    // Check if the city is set
    if (isset($_POST['city'])) {
        $city = $_POST['city'];
 
        // Example values; you might want to change these based on the user's selection
        $movie = isset($_POST['movie']) ? $_POST['movie'] : '';
        $day = isset($_POST['day']) ? $_POST['day'] : '';
        $time = isset($_POST['time']) ? $_POST['time'] : '';
 
        // Fetch reserved seats based on user selection
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
 
// New block to fetch reserved seats if the form has not been submitted yet
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // Example values; you might want to change these based on the user's selection
    $city = isset($_POST['city']) ? $_POST['city'] : '';
    $movie = isset($_POST['movie']) ? $_POST['movie'] : '';
    $day = isset($_POST['day']) ? $_POST['day'] : '';
    $time = isset($_POST['time']) ? $_POST['time'] : '';
 
    // Fetch reserved seats based on user selection
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
    // The HTML for the popup message
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

    // Display the popup confirmation message
    echo $purchaseConfirmation;
}

?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Tickets</title>
    <link rel="stylesheet" href="main.css"> <!-- Link to main.css for navbar styling -->
    <link rel="stylesheet" href="tickets.css"> <!-- Link to tickets.css for page-specific styling -->
</head>
<body>
 
    <!-- Navbar -->
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
        <!-- Movie Prices Box -->
        <div class="movie-prices">
            <h3>Movie Prices</h3>
            <ul>
                <?php foreach ($movies as $movieName => $price): ?>
                    <li><?= $movieName ?>: $<?= number_format($price, 2) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
 
        <!-- Movie selection form -->
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
                        <!-- Options will be populated dynamically -->
                    </select>
                    <div id="dayMessage" class="message" style="display: none;">No options available.</div>
                </div>
 
                <div class="form-group">
                    <label for="time">Time:</label>
                    <select name="time" id="time" required>
                        <!-- Options will be populated dynamically -->
                    </select>
                    <div id="timeMessage" class="message" style="display: none;">No options available.</div>
                </div>
 
                <div class="form-group">
                    <label for="tickets">Number of Tickets:</label>
                    <input type="number" name="tickets" id="tickets" value="<?= isset($tickets) ? $tickets : 1 ?>" min="1" max="10" required>
                </div>
            </div>
 
            <!-- Seat selection -->
<div class="seat-selection">
    <h3>Select Seats</h3>
    <div id="seatsContainer">
        <?php for ($i = 1; $i <= 104; $i++): ?>
            <div class="seat <?= in_array($i, $reservedSeats) ? 'reserved' : '' ?>" data-seat="<?= $i ?>"><?= $i ?></div>
        <?php endfor; ?>
    </div>
    <input type="hidden" name="seats" id="selectedSeats" value="<?= isset($seats) ? implode(',', $seats) : '' ?>">
</div>
 
            <!-- Summary and submit -->
            <div class="summary">
                <h3>Summary</h3><br>
                <p>Total Price: $<span id="totalPrice"><?= number_format($totalPrice, 2) ?></span></p>
                <button type="submit">Book Tickets</button>
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
 
// Event listeners for dropdown updates
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
 
            // Update total price display
            totalPriceDisplay.textContent = totalPrice.toFixed(2);
        }
 
        // Setup listeners for initial total price calculation
        document.getElementById('movie').addEventListener('change', calculateTotalPrice);
        document.getElementById('tickets').addEventListener('change', calculateTotalPrice);
 
        // Initial calculation when the page loads
        calculateTotalPrice();
    </script>
 
    <<script>
    // Show purchase message after the form is submitted
    window.addEventListener('load', function() {
        // Check if purchaseMessage variable is set
        <?php if (isset($purchaseMessage)): ?>
            alert("<?= $purchaseMessage ?> This information has been saved in the database.");
        <?php endif; ?>
    });
</script>
</body>
<!-- Footer -->
<footer class="footer">
    <p>© 2024 Cinema-Island. All rights reserved.</p>
</footer>
</html>
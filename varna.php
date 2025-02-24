<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// Logout functionality
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    echo '<script>localStorage.clear(); window.location.href = "index.php";</script>';
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "registration";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch schedule data for Varna
$sql = "SELECT day, movie_name, show_times, image FROM varna_schedule ORDER BY day, movie_name";
$result = $conn->query($sql);

$moviesByDay = [];
if ($result->num_rows > 0) {
    // Organize data by day of the week
    while ($row = $result->fetch_assoc()) {
        $moviesByDay[$row['day']][] = $row;
    }
} else {
    echo "No movie schedule found.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Program - Varna</title>
    <link rel="stylesheet" href="main.css">
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
            <?php if (isset($_SESSION['email']) && $_SESSION['email'] === 'adminCinemaIsland@gmail.com'): ?>
                <a href="admin.php" class="admin-button">Edit Movies</a>
            <?php endif; ?>
        </div>
        <form method="post" class="logout">
            <button type="submit" name="logout">Logout</button>
        </form>
    </div>
</div>

<!-- Weekly Program Section -->
<div class="program-section">
    <h2>Weekly Program - Varna</h2>

    <?php
    // Days of the week
    $daysOfWeek = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
    
    foreach ($daysOfWeek as $day) {
        if (isset($moviesByDay[$day])) {
            echo "<div class='day-block'>";
            echo "<div class='day-title'>$day</div>";
            echo "<div class='movie-row'>";
            
            foreach ($moviesByDay[$day] as $movie) {
                echo "<div class='movie-block'>";
                echo "<img src='" . $movie['image'] . "' alt='" . $movie['movie_name'] . "' class='movie-poster'>";
                echo "<div class='movie-details'>";
                echo "<h3>" . $movie['movie_name'] . "</h3>";
                echo "<p class='show-time'>" . $movie['show_times'] . "</p>";
                echo "<a href='tickets.php' class='buy-tickets-button'>Buy Tickets</a>";
                echo "</div></div>";
            }

            echo "</div></div>";
        }
    }
    ?>
</div>

<!-- Footer -->
<footer class="footer">
    <p>© 2024 Cinema-Island. All rights reserved.</p>
</footer>
</body>
</html>

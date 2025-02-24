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
$dbname = 'registration'; 
$username = 'root'; 
$password = ''; 


$conn = new mysqli($host, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "SELECT * FROM cinemas";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Cinema-Island</title>
    <link rel="stylesheet" href="main.css">
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
    <?php if (isset($_SESSION['email']) && $_SESSION['email'] === 'adminCinemaIsland@gmail.com'): ?>
        <a href="admin.php" class="admin-button">Edit Movies</a>
    <?php endif; ?>
        </div>
        <form method="post" class="logout">
            <button type="submit" name="logout">Logout</button>
        </form>
    </div>
</div>

<div class="gallery-container">

    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="cinema-section">';
            echo '<h1>' . htmlspecialchars($row['name']) . '</h1>';
            echo '<p class="gallery-description">Established: ' . htmlspecialchars($row['established_year']) . '</p>';
            
            $images = explode(',', $row['image_path']);
            echo '<div class="gallery-grid">';
            foreach ($images as $image) {
                echo '<div class="gallery-item">';
                echo '<img src="' . htmlspecialchars(trim($image)) . '" alt="' . htmlspecialchars($row['name']) . ' Image">';
                echo '</div>';
            }
            echo '</div>';
            
            echo '<div class="pros-box">';
            echo '<h2>Pros and Technologies</h2>';
            $prosList = explode(',', $row['pros']);
            foreach ($prosList as $pros) {
                echo '<p><span class="icon">✔</span> ' . htmlspecialchars(trim($pros)) . '</p>';
            }
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo "<p>No cinemas found.</p>";
    }

    $conn->close();
    ?>
</div>

<footer class="footer">
    <p>© 2024 Cinema-Island. All rights reserved.</p>
</footer>
</body>
</html>

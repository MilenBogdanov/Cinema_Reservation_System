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

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

$sql = "SELECT * FROM contacts";
$stmt = $pdo->query($sql);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacts - Cinema-Island</title>
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

<div class="contacts-container">
    <h1>Contact Us</h1>
    <p>If you have any inquiries or need assistance, find the contact details for each city below:</p>

    <?php foreach ($contacts as $contact): ?>
    <div class="contact-city">
        <h2>Cinema-Island <?php echo htmlspecialchars($contact['city_name']); ?></h2>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($contact['phone']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($contact['address']); ?></p>
        <p><strong>Working Hours:</strong> <?php echo htmlspecialchars($contact['working_hours']); ?></p>
        <p><strong>Working Days:</strong> <?php echo htmlspecialchars($contact['working_days']); ?></p>
    </div>
    <?php endforeach; ?>
</div>

<footer class="footer">
    <p>© 2024 Cinema-Island. All rights reserved.</p>
</footer>

</body>
</html>
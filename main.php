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

$conn = new mysqli('localhost', 'root', '', 'registration');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema-Island</title>
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
            <a href="#now-playing">Movies</a>
            <div class="dropdown">
            <a href="#">Weekly Program</a>
            <ul class="dropdown-menu">
                    <li><a href="varna.php">Varna</a></li>
                    <li><a href="sofia.php">Sofia</a></li>
                    <li><a href="plovdiv.php">Plovdiv</a></li>
                </ul>
            </div>
            <a href="#coming-soon">Coming Soon</a>
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

<div class="slider-container">
    <div class="slider">
        <?php
        
        $bestSellersQuery = "SELECT title, image_url, genre, duration, description FROM now_playing WHERE title IN ('Joker', 'Spider-Man: Homecoming', 'Terrifier 3')";
        $bestSellersResult = $conn->query($bestSellersQuery);

        if ($bestSellersResult->num_rows > 0) {
            while ($row = $bestSellersResult->fetch_assoc()) {
                echo '<div class="slide">';
                echo '<div class="slide-info">';
                echo '<h2>' . htmlspecialchars($row['title']) . '<br></h2>';
                echo '<p class="genre">Genre: ' . htmlspecialchars($row['genre']) . '</p>';
                echo '<p class="duration">Duration: ' . htmlspecialchars($row['duration']) . ' minutes</p>';
                echo '<p class="long-description">' . htmlspecialchars($row['description']) . '</p>';
                echo '<a href="tickets.php" class="tickets-button">Buy Tickets</a>';
                echo '</div>';
                echo '<div class="slide-image" style="background-image: url(\'' . $row['image_url'] . '\');"></div>';
                echo '<div class="best-sellers-text">Our Best Sellers</div>';
                echo '</div>';
            }
        } else {
            echo '<p>No best sellers found.</p>';
        }
        ?>
    </div>
    <button class="prev" onclick="changeSlide(-1)">&#10094;</button>
    <button class="next" onclick="changeSlide(1)">&#10095;</button>
</div>

<div class="films-section" id="now-playing">
    <h2 class="films-title">Now Playing</h2>
    <div class="cinema-thumbnail-gallery">
    <?php
    
    $nowPlayingQuery = "SELECT title, image_url, duration, genre, description, release_date FROM now_playing";
    $nowPlayingResult = $conn->query($nowPlayingQuery);

    if ($nowPlayingResult->num_rows > 0) {
        while ($row = $nowPlayingResult->fetch_assoc()) {
            echo '<div class="cinema-thumbnail-item" onclick="openModal(\'' . htmlspecialchars($row['title']) . '\', \'' . htmlspecialchars($row['image_url']) . '\', \'' . htmlspecialchars($row['description']) . '\', \'' . htmlspecialchars($row['duration']) . '\', \'' . htmlspecialchars($row['genre']) . '\', \'' . htmlspecialchars($row['release_date']) . '\')">';
            echo '<div class="thumbnail-image" style="background-image: url(\'' . htmlspecialchars($row['image_url']) . '\');"></div>';
            echo '<div class="cinema-thumbnail-title">' . htmlspecialchars($row['title']) . '</div>';
            echo '</div>';
        }
    } else {
        echo '<p>No movies are currently playing.</p>';
    }
    ?>
</div>
</div>

<div class="coming-soon-section" id="coming-soon">
    <h2 class="films-title">Coming Soon</h2>
    <div class="cinema-thumbnail-gallery">
        <?php
        
        $comingSoonQuery = "SELECT title, image_url, description, duration, genre, release_date FROM coming_soon";
        $comingSoonResult = $conn->query($comingSoonQuery);

        if ($comingSoonResult->num_rows > 0) {
    while ($row = $comingSoonResult->fetch_assoc()) {
        // Include duration and genre in the onclick function
        echo '<div class="cinema-thumbnail-item" onclick="openComingSoonModal(\'' . htmlspecialchars($row['title']) . '\', \'' . htmlspecialchars($row['image_url']) . '\', \'' . htmlspecialchars($row['description']) . '\', \'' . htmlspecialchars($row['duration']) . '\', \'' . htmlspecialchars($row['genre']) . '\', \'' . htmlspecialchars($row['release_date']) . '\')">';
        echo '<div class="thumbnail-image" style="background-image: url(\'' . htmlspecialchars($row['image_url']) . '\');"></div>';
        echo '<div class="cinema-thumbnail-title">' . htmlspecialchars($row['title']) . '</div>';
        echo '</div>';
    }

} else {
    echo '<p>No upcoming movies at the moment.</p>';
}

        ?>
    </div>
</div>

<footer class="footer">
    <p>© 2024 Cinema-Island. All rights reserved.</p>
</footer>

<script src="slider.js"></script>

<div id="movieModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div class="modal-image" id="modalImage" style="background-image: url('');"></div>
        <div class="modal-info">
            <h2 id="modalTitle"></h2><br>
            <p id="modalDescription"></p><br>
            <p id="modalDuration"></p>
            <p id="modalGenre"></p>
            <p id="modalReleaseDate"></p><br>
            <a href="tickets.php" class="ticketspop-button">Buy Tickets</a>
        </div>
    </div>
</div>
<div id="comingSoonModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeComingSoonModal()">&times;</span>
        <div class="modal-image" id="comingSoonModalImage" style="background-image: url('');"></div>
        <div class="modal-info">
            <h2 id="comingSoonModalTitle"></h2><br>
            <p id="comingSoonModalDescription"></p><br>
            <p id="comingSoonModalDuration"></p>
            <p id="comingSoonModalGenre"></p> 
            <p id="comingSoonModalReleaseDate"></p>
        </div>
    </div>
</div>
<script>
function openComingSoonModal(title, imageUrl, description, duration, genre, releaseDate) {
    console.log('Opening modal with:', title, imageUrl, description, duration, genre, releaseDate);
    document.getElementById('comingSoonModalTitle').textContent = title;
    document.getElementById('comingSoonModalImage').style.backgroundImage = `url('${imageUrl}')`;
    document.getElementById('comingSoonModalDescription').textContent = description;
    document.getElementById('comingSoonModalDuration').textContent = `Duration: ${duration} minutes`;
    document.getElementById('comingSoonModalGenre').textContent = `Genre: ${genre}`;
    document.getElementById('comingSoonModalReleaseDate').textContent = `Release Date: ${releaseDate}`;
    document.getElementById('comingSoonModal').style.display = 'block';
}

function closeComingSoonModal() {
    document.getElementById('comingSoonModal').style.display = 'none';
}

window.addEventListener('click', function(event) {
    const modal = document.getElementById('comingSoonModal');
    if (event.target === modal) {
        closeComingSoonModal();
    }
});
</script>

</body>
</html>
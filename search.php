<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'registration');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    echo '<script>localStorage.clear(); window.location.href = "index.php";</script>';
    exit;
}


$query = isset($_GET['query']) ? trim($_GET['query']) : '';

$searchResults = [];

if (!empty($query)) {

    $sql = "(SELECT title, image_url, description, duration, genre, release_date, 'now_playing' AS source 
            FROM now_playing 
            WHERE title LIKE ? OR genre LIKE ?) 
            UNION 
            (SELECT title, image_url, description, duration, genre, release_date, 'coming_soon' AS source 
            FROM coming_soon 
            WHERE title LIKE ? OR genre LIKE ?)";

    $stmt = $conn->prepare($sql);
    $searchParam = "%" . $query . "%";
    $stmt->bind_param("ssss", $searchParam, $searchParam, $searchParam, $searchParam);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $searchResults = [];

    while ($row = $result->fetch_assoc()) {
        $searchResults[] = $row;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
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


<div class="films-section">
    <div class="films-header">
        <h2 class="films-title">Search Results for "<?php echo htmlspecialchars($query); ?>"</h2>
        <div class="sort-container">
    <label for="sort-options">Sort By:</label>
    <select id="sort-options" onchange="sortResults()">
        <option value="default">Default</option>
        <option value="date">Release Date (Newest to Oldest)</option>
        <option value="date-oldest">Release Date (Oldest to Newest)</option>
        <option value="alphabet">Alphabetical (A-Z)</option>
        <option value="now-playing">Now Playing (First)</option>
        <option value="coming-soon">Coming Soon (First)</option>
        <option value="duration">Duration (Shortest to Longest)</option>
        <option value="duration-desc">Duration (Longest to Shortest)</option>
    </select>
    <button id="clear-filters" onclick="clearFilters()">Clear Filters</button>
</div>
    </div>

    <div class="cinema-thumbnail-gallery" id="resultsContainer">
        <?php if (!empty($searchResults)): ?>
            <?php foreach ($searchResults as $movie): ?>
                <div class="cinema-thumbnail-item" 
                     data-title="<?php echo htmlspecialchars($movie['title']); ?>"
                     data-duration="<?php echo htmlspecialchars($movie['duration']); ?>"
                     data-release-date="<?php echo htmlspecialchars($movie['release_date']); ?>"
                     data-source="<?php echo $movie['source']; ?>"
                     onclick="openModal(
                        '<?php echo htmlspecialchars($movie['title']); ?>', 
                        '<?php echo htmlspecialchars($movie['image_url']); ?>', 
                        '<?php echo htmlspecialchars($movie['description']); ?>', 
                        '<?php echo htmlspecialchars($movie['duration']); ?>', 
                        '<?php echo htmlspecialchars($movie['genre']); ?>', 
                        '<?php echo htmlspecialchars($movie['release_date']); ?>',
                        '<?php echo $movie['source']; ?>'
                     )">
                    <div class="thumbnail-image" style="background-image: url('<?php echo htmlspecialchars($movie['image_url']); ?>');"></div>
                    <div class="cinema-thumbnail-title"><?php echo htmlspecialchars($movie['title']); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No results found.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    let originalMoviesHTML = "";

document.addEventListener("DOMContentLoaded", function () {
    originalMoviesHTML = document.getElementById('resultsContainer').innerHTML;
});
function sortResults() {
    const sortBy = document.getElementById('sort-options').value;
    const container = document.getElementById('resultsContainer');
    const items = Array.from(container.getElementsByClassName('cinema-thumbnail-item'));

    if (sortBy === "default") {
        container.innerHTML = originalMoviesHTML;
        return;
    }

    items.sort((a, b) => {
        if (sortBy === "alphabet") {
            return a.dataset.title.localeCompare(b.dataset.title);
        } else if (sortBy === "date") {
            return new Date(b.dataset.releaseDate) - new Date(a.dataset.releaseDate);
        } else if (sortBy === "date-oldest") {
            return new Date(a.dataset.releaseDate) - new Date(b.dataset.releaseDate);
        } else if (sortBy === "duration") {
            return parseInt(a.dataset.duration) - parseInt(b.dataset.duration);
        } else if (sortBy === "duration-desc") {
            return parseInt(b.dataset.duration) - parseInt(a.dataset.duration);
        } else if (sortBy === "now-playing") {
            return a.dataset.source === "now_playing" ? -1 : 1;
        } else if (sortBy === "coming-soon") {
            return a.dataset.source === "coming_soon" ? -1 : 1;
        }
    });

    container.innerHTML = "";
    items.forEach(item => container.appendChild(item));
}

function openModal(title, imageUrl, description, duration, genre, releaseDate, source) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalImage').style.backgroundImage = `url('${imageUrl}')`;
    document.getElementById('modalDescription').textContent = description;
    document.getElementById('modalDuration').textContent = `Duration: ${duration} minutes`;
    document.getElementById('modalGenre').textContent = `Genre: ${genre}`;
    document.getElementById('modalReleaseDate').textContent = `Release Date: ${releaseDate}`;

    const ticketButton = document.getElementById('ticketButton');
    const comingSoonText = document.getElementById('comingSoonText');

    if (source === 'now_playing') {
        ticketButton.style.display = 'inline-block';
        comingSoonText.style.display = 'none';
    } else {
        ticketButton.style.display = 'none';
        comingSoonText.style.display = 'block';
    }

    document.getElementById('movieModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('movieModal').style.display = 'none';
}

window.addEventListener('click', function(event) {
    const modal = document.getElementById('movieModal');
    if (event.target === modal) {
        closeModal();
    }
});

function clearFilters() {
    document.getElementById('sort-options').value = "default";
    document.getElementById('resultsContainer').innerHTML = originalMoviesHTML;
}
</script>

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

            <a href="tickets.php" id="ticketButton" class="ticketspop-button">Buy Tickets</a>

            <p id="comingSoonText" class="coming-soon-text" style="display: none;">Coming Soon...</p>
        </div>
    </div>
</div>


</body>

</html>

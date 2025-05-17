<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}
$conn = new mysqli('localhost', 'root', '', 'registration');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$movies = [
    'Joker: Folie à Deux' => 12.00,
    'Spider-Man: Homecoming' => 10.00,
    'Terrifier 3' => 15.00,
    'Venom: Let There Be Carnage' => 14.00,
    'Cars 2' => 8.00,
    'Smile 2' => 9.00
];

$user_email = $_SESSION['email'];

$cityFilter = isset($_GET['city']) ? $_GET['city'] : '';
$movieFilter = isset($_GET['movie']) ? $_GET['movie'] : '';
$dayFilter = isset($_GET['day']) ? $_GET['day'] : '';

$query = "SELECT city, movie, day, screening_time, seat_number FROM seats WHERE user_email = ?";
$params = [$user_email];
$types = "s";

if (!empty($cityFilter)) {
    $query .= " AND city = ?";
    $params[] = $cityFilter;
    $types .= "s";
}
if (!empty($movieFilter)) {
    $query .= " AND movie = ?";
    $params[] = $movieFilter;
    $types .= "s";
}
if (!empty($dayFilter)) {
    $query .= " AND day = ?";
    $params[] = $dayFilter;
    $types .= "s";
}

$stmt = $conn->prepare($query);

$a_params = [];
$a_params[] = & $types;
for ($i = 0; $i < count($params); $i++) {
    $a_params[] = & $params[$i];
}

call_user_func_array([$stmt, 'bind_param'], $a_params);

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Bookings</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        body {
    background-color: #1b1b1b;
    background-attachment: fixed;
    margin: 0;
    padding: 0;
}

.booking-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 100vh;
    background-color: rgba(27, 27, 27, 0.8);
    padding: 30px;
    border-radius: 12px;
    margin: 40px auto;
    width: 90%;
    max-width: 1000px;
}

.booking-title {
    color: white;
    font-size: 36px;
    margin-bottom: 20px;
}

.filter-form {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 15px;
    margin-bottom: 20px;
}

.filter-form select,
.filter-form button {
    padding: 10px;
    font-size: 16px;
    border-radius: 6px;
    border: none;
}

.filter-form button {
    background-color: #cc0811;
    color: white;
    cursor: pointer;
}

.filter-form button:hover {
    background-color: #a3070e;
}

.booking-table {
    width: 100%;
    border-collapse: collapse;
    background-color: white;
    border-radius: 10px;
    overflow: hidden;
}

.booking-table th,
.booking-table td {
    padding: 12px 15px;
    border: 1px solid #ccc;
    text-align: center;
}

.booking-table th {
    background-color: #cc0811;
    color: white;
}

.homepage-btn {
    background-color: #cc0811;
    color: white;
    padding: 12px 5px;
    font-size: 18px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    margin-bottom: 8px;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.3s ease;
    box-shadow: 0 4px 6px rgba(204, 8, 17, 0.4);
}

.homepage-btn:hover {
    background-color: #a3070e;
    box-shadow: 0 6px 10px rgba(163, 7, 14, 0.6);
}

.download-btn {
    background-color: #28a745;
    color: white;
    padding: 6px 10px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: background-color 0.3s ease;
}
.download-btn:hover {
    background-color: #218838;
}
    </style>
</head>
<body>

<div class="booking-container">
    <a href="main.php" class="homepage-btn" title="Go to Homepage">🏠 Home</a>
    <h1 class="booking-title">My Bookings</h1>

    <form method="get" class="filter-form">
        <select name="city">
            <option value="">All Cities</option>
            <option value="Plovdiv" <?= $cityFilter === 'Plovdiv' ? 'selected' : '' ?>>Plovdiv</option>
            <option value="Sofia" <?= $cityFilter === 'Sofia' ? 'selected' : '' ?>>Sofia</option>
            <option value="Varna" <?= $cityFilter === 'Varna' ? 'selected' : '' ?>>Varna</option>
        </select>

        <select name="movie">
    <option value="">All Movies</option>
    <?php foreach (array_keys($movies) as $movieTitle): ?>
        <option value="<?= htmlspecialchars($movieTitle) ?>" <?= $movieFilter === $movieTitle ? 'selected' : '' ?>>
            <?= htmlspecialchars($movieTitle) ?>
        </option>
    <?php endforeach; ?>
</select>

        <select name="day">
        <option value="">All Days</option>
        <option value="Monday" <?= $dayFilter === 'Monday' ? 'selected' : '' ?>>Monday</option>
        <option value="Tuesday" <?= $dayFilter === 'Tuesday' ? 'selected' : '' ?>>Tuesday</option>
        <option value="Wednesday" <?= $dayFilter === 'Wednesday' ? 'selected' : '' ?>>Wednesday</option>
        <option value="Thursday" <?= $dayFilter === 'Thursday' ? 'selected' : '' ?>>Thursday</option>
        <option value="Friday" <?= $dayFilter === 'Friday' ? 'selected' : '' ?>>Friday</option>
        <option value="Saturday" <?= $dayFilter === 'Saturday' ? 'selected' : '' ?>>Saturday</option>
        <option value="Sunday" <?= $dayFilter === 'Sunday' ? 'selected' : '' ?>>Sunday</option>
    </select>

        <button type="submit">Apply Filters</button>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <table class="booking-table">
            <tr>
                <th>City</th>
                <th>Movie</th>
                <th>Price</th>
                <th>Day</th>
                <th>Time</th>
                <th>Seat</th>
                <th>Download</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['city']) ?></td>
        <td><?= htmlspecialchars($row['movie']) ?></td>
        <td>
            <?= isset($movies[$row['movie']]) ? '$' . number_format($movies[$row['movie']], 2) : 'N/A' ?>
        </td>
        <td><?= htmlspecialchars($row['day']) ?></td>
        <td><?= htmlspecialchars($row['screening_time']) ?></td>
        <td><?= htmlspecialchars($row['seat_number']) ?></td>
        <td>
            <a href="download_ticket.php?city=<?= urlencode($row['city']) ?>&movie=<?= urlencode($row['movie']) ?>&day=<?= urlencode($row['day']) ?>&time=<?= urlencode($row['screening_time']) ?>&seat=<?= urlencode($row['seat_number']) ?>" target="_blank" class="download-btn">Download Ticket</a>
        </td>
    </tr>
<?php endwhile; ?>
        </table>
    <?php else: ?>
        <p style="color: white; text-align: center;">No bookings match your filters.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
<?php
$conn = new mysqli('localhost', 'root', '', 'registration');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

if ($query != '') {
    $sql = "
        SELECT title, genre FROM now_playing 
        WHERE title LIKE '%$query%' OR genre LIKE '%$query%'
        UNION
        SELECT title, genre FROM coming_soon
        WHERE title LIKE '%$query%' OR genre LIKE '%$query%'
        LIMIT 10
    ";
    $result = $conn->query($sql);

    $suggestions = [];
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row;
    }

    echo json_encode($suggestions);
}

$conn->close();
?>
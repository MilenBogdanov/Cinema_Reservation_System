<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['email'] !== 'adminCinemaIsland@gmail.com') {
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

$conn = new mysqli('localhost', 'root', '', 'registration');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['add_movie'])) {
    $table = $_POST['table'];
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $duration = $_POST['duration'];
    $description = $_POST['description'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($imageFileType, $allowedTypes)) {
            $targetDirectory = 'images/';
            $imageName = basename($_FILES['image']['name']);
            $imagePath = $targetDirectory . $imageName;

            if (!file_exists($targetDirectory)) {
                if (!mkdir($targetDirectory, 0777, true)) {
                    $_SESSION['message'] = "Failed to create directory: " . $targetDirectory;
                    $_SESSION['message_type'] = 'error';
                    header("Location: admin_panel.php");
                    exit;
                }
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                $stmt = $conn->prepare("INSERT INTO $table (title, image_url, genre, duration, description) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('sssds', $title, $imagePath, $genre, $duration, $description);
                $stmt->execute();
                $stmt->close();
                
                $_SESSION['message'] = "Movie added successfully!";
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = "Error uploading the file. Please try again.";
                $_SESSION['message_type'] = 'error';
            }
        } else {
            $_SESSION['message'] = "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
            $_SESSION['message_type'] = 'error';
        }
    } else {
        $_SESSION['message'] = "Please upload an image. Error: " . $_FILES['image']['error'];
        $_SESSION['message_type'] = 'error';
    }
}

if (isset($_POST['delete_movie'])) {
    $table = $_POST['table'];
    $title = $_POST['title'];

    $stmt = $conn->prepare("SELECT image_url FROM $table WHERE title = ?");
    $stmt->bind_param('s', $title);
    $stmt->execute();
    $stmt->bind_result($imagePath);
    $stmt->fetch();
    $stmt->close();

    if (!empty($imagePath) && file_exists($imagePath)) {
        if (unlink($imagePath)) {
            $_SESSION['message'] = "Image deleted successfully!";
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "Failed to delete the image.";
            $_SESSION['message_type'] = 'error';
        }
    }

    $stmt = $conn->prepare("DELETE FROM $table WHERE title = ?");
    $stmt->bind_param('s', $title);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Movie deleted successfully!";
    $_SESSION['message_type'] = 'success';
}

$table = 'now_playing';

if (isset($_POST['table'])) {
    $table = $_POST['table'];
}

$stmt = $conn->prepare("SELECT title FROM $table");
$stmt->execute();
$stmt->bind_result($title);
$titles = [];

while ($stmt->fetch()) {
    $titles[] = $title;
}
$stmt->close();

if (isset($_POST['edit_gallery'])) {
    $cinemaId = $_POST['cinema_id'];
    $cinemaName = $_POST['cinema_name'];
    $establishedYear = $_POST['established_year'];
    $pros = $_POST['pros'];

    $images = [];
    if (!empty($_FILES['images']['name'][0])) {
        $targetDirectory = 'images/';
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            $imageName = basename($_FILES['images']['name'][$key]);
            $imagePath = $targetDirectory . $imageName;
            if (move_uploaded_file($tmpName, $imagePath)) {
                $images[] = $imagePath;
            }
        }
    }
    
    $imagesPath = implode(',', $images);
    $updateQuery = "UPDATE cinemas SET name=?, established_year=?, pros=?, image_path=? WHERE id=?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("sisss", $cinemaName, $establishedYear, $pros, $imagesPath, $cinemaId);
    $updateStmt->execute();
    $updateStmt->close();

    $_SESSION['message'] = "Gallery updated successfully!";
    $_SESSION['message_type'] = 'success';
}

if (isset($_POST['edit_contact'])) {
    $cityId = $_POST['city_id'];
    $phone = $_POST['contact_phone'];
    $address = $_POST['contact_address'];
    $workingHours = isset($_POST['working_hours']) ? $_POST['working_hours'] : '';
    $workingDays = isset($_POST['working_days']) ? $_POST['working_days'] : '';

    // Check if the necessary fields are filled out
    if (empty($phone) || empty($address) || empty($workingHours) || empty($workingDays)) {
        $_SESSION['message'] = "Please fill in all fields.";
        $_SESSION['message_type'] = 'error';
    } else {
        $updateStmt = $conn->prepare("UPDATE contacts SET phone = ?, address = ?, working_hours = ?, working_days = ? WHERE id = ?");
        $updateStmt->bind_param("ssssi", $phone, $address, $workingHours, $workingDays, $cityId);

        if ($updateStmt->execute()) {
            $_SESSION['message'] = "Contact information updated successfully!";
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "Failed to update contact information.";
            $_SESSION['message_type'] = 'error';
        }
        $updateStmt->close();
    }
}

$message = isset($_SESSION['message']) ? $_SESSION['message'] : null;
$messageType = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : null;

unset($_SESSION['message']);
unset($_SESSION['message_type']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="main.css">
    <style>
        h2 {
            color: red;
            font-size: 1.8em;
            text-align: center;
            text-shadow: 1px 1px 0 rgba(255, 255, 255, 1), -1px -1px 0 rgba(255, 255, 255, 1), 1px -1px 0 rgba(255, 255, 255, 1), -1px 1px 0 rgba(255, 255, 255, 1);
            border-top: 2px solid rgba(255, 255, 255, 0.7);
            padding-top: 10px;
        }

        h1 {
            font-size: 2.5em;
            color: #fff;
            text-align: center;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
        }

        .form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        form {
            background-color: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.7);
            padding: 20px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #fff;
        }

        input[type="text"], input[type="number"], textarea, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #444;
            border-radius: 5px;
            background-color: #222;
            color: #fff;
            font-size: 1em;
        }

        button {
            margin-top: 20px;
            padding: 10px 30px;
            font-size: 1em;
            color: #fff;
            background-color: red;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: darkred;
        }

    </style>
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

<div id="messageModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <p id="modalMessage" class=""></p>
    </div>
</div>

<style>
    #messageModal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 1000;
    }

    .modal-content {
        background-color: #000;
        color: #fff;
        width: 400px;
        padding: 20px;
        margin: 100px auto;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.7);
        position: relative;
    }

    .close {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 1.5em;
        color: #fff;
        cursor: pointer;
    }

    .close:hover {
        color: red;
    }

    #modalMessage {
        font-size: 1.2em;
        text-align: center;
    }

    .success {
        color: #28a745;
        font-weight: bold;
        font-size: 1.2em;
        text-align: center;
    }

    .error {
        color: #dc3545;
        font-weight: bold;
        font-size: 1.2em;
        text-align: center;
    }
</style>

<script>
<?php if ($message): ?>
    document.getElementById("messageModal").style.display = "block";
    document.getElementById("modalMessage").innerText = "<?php echo addslashes($message); ?>";

    if ("<?php echo $messageType; ?>" === "success") {
        document.getElementById("modalMessage").classList.add("success");
    } else if ("<?php echo $messageType; ?>" === "error") {
        document.getElementById("modalMessage").classList.add("error");
    }
<?php endif; ?>

function closeModal() {
    document.getElementById("messageModal").style.display = "none";
}
</script>

    <h1>Admin Panel</h1>

    <div class="form-container">
        <form method="post" enctype="multipart/form-data">
            <h2>Add Movie</h2>
            <label>Table:
                <select name="table">
                    <option value="now_playing">Now Playing</option>
                    <option value="coming_soon">Coming Soon</option>
                </select>
            </label>
            <label>Title: <input type="text" name="title" required></label>
            <label>Image: <input type="file" name="image" accept="image/*" required></label>
            <label>Genre: <input type="text" name="genre" required></label>
            <label>Duration: <input type="number" name="duration" required></label>
            <label>Description: <textarea name="description" required></textarea></label><br>
            <button type="submit" name="add_movie">Add Movie</button>
        </form>

        <form method="post">
            <h2>Delete Movie</h2>
            <label>Table:
                <select name="table" onchange="this.form.submit()">
                    <option value="now_playing" <?php echo $table === 'now_playing' ? 'selected' : ''; ?>>Now Playing</option>
                    <option value="coming_soon" <?php echo $table === 'coming_soon' ? 'selected' : ''; ?>>Coming Soon</option>
                </select>
            </label>

            <label>Title:
                <select name="title" required>
                    <option value="">Select a Movie</option>
                    <?php foreach ($titles as $movieTitle): ?>
                        <option value="<?php echo htmlspecialchars($movieTitle); ?>"><?php echo htmlspecialchars($movieTitle); ?></option>
                    <?php endforeach; ?>
                </select>
            </label><br>
            <button type="submit" name="delete_movie">Delete Movie</button>
        </form>

        <form method="post" enctype="multipart/form-data">
            <h2>Edit Gallery</h2>
            <label>Cinema:
                <select name="cinema_id" required>
                    <option value="">Select a Cinema</option>
                    <?php
                    $cinemasStmt = $conn->prepare("SELECT id, name FROM cinemas");
                    $cinemasStmt->execute();
                    $cinemasStmt->bind_result($cinemaId, $cinemaName);

                    while ($cinemasStmt->fetch()): ?>
                        <option value="<?php echo $cinemaId; ?>"><?php echo htmlspecialchars($cinemaName); ?></option>
                    <?php endwhile; ?>
                    <?php $cinemasStmt->close(); ?>
                </select>
            </label>
            <label>Name: <input type="text" name="cinema_name" required></label>
            <label>Established Year: <input type="number" name="established_year" required></label>
            <label>Pros (comma-separated): <textarea name="pros" required></textarea></label>
            <label>Images (Upload new images if needed): <input type="file" name="images[]" multiple accept="image/*"></label><br>
            <button type="submit" name="edit_gallery">Update Cinema</button>
        </form>

        <form method="post">
            <h2>Edit Contact Information</h2>
            <label>Select City:
                <select name="city_id" onchange="this.form.submit()" required>
                    <option value="">Select a City</option>
                    <?php
             
                    $citiesStmt = $conn->prepare("SELECT id, city_name FROM contacts");
                    $citiesStmt->execute();
                    $citiesStmt->bind_result($cityId, $cityName);

                    while ($citiesStmt->fetch()): ?>
                        <option value="<?php echo $cityId; ?>" <?php echo (isset($_POST['city_id']) && $_POST['city_id'] == $cityId) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cityName); ?>
                        </option>
                    <?php endwhile; ?>
                    <?php $citiesStmt->close(); ?>
                </select>
            </label>

            <?php if (isset($_POST['city_id']) && !empty($_POST['city_id'])): ?>
    <?php
    $cityId = $_POST['city_id'];
    $contactStmt = $conn->prepare("SELECT phone, address, working_hours, working_days FROM contacts WHERE id = ?");
    $contactStmt->bind_param("i", $cityId);
    $contactStmt->execute();
    $contactStmt->bind_result($phone, $address, $workingHours, $workingDays);
    $contactStmt->fetch();
    $contactStmt->close();
    ?>

    <label>Phone: <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($phone); ?>" required></label>
    <label>Address: <textarea name="contact_address" required><?php echo htmlspecialchars($address); ?></textarea></label>
    <label>Working Hours: <input type="text" name="working_hours" value="<?php echo htmlspecialchars($workingHours); ?>" required></label>
    <label>Working Days: <input type="text" name="working_days" value="<?php echo htmlspecialchars($workingDays); ?>" required></label>
<?php endif; ?><br>

            <button type="submit" name="edit_contact">Update Contact</button>
        </form>
    </div>
<footer class="footer">
    <p>© 2024 Cinema-Island. All rights reserved.</p>
</footer>
</body>

</html>
<?php

$errors = [];
$successMessage = '';
$firstName = $familyName = $email = $phone = $password = $confirmPassword = '';


$host = 'localhost'; 
$dbUser = 'root'; 
$dbPass = ''; 
$dbName = 'registration';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $firstName = htmlspecialchars(trim($_POST['firstName']));
    $familyName = htmlspecialchars(trim($_POST['familyName']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $password = htmlspecialchars(trim($_POST['password']));
    $confirmPassword = htmlspecialchars(trim($_POST['confirmPassword']));

    if (empty($firstName) || strlen($firstName) > 50) {
        $errors[] = "First name is required and must not exceed 50 characters.";
    }
    if (empty($familyName) || strlen($familyName) > 50) {
        $errors[] = "Last name is required and must not exceed 50 characters.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 30) {
        $errors[] = "Please enter a valid email address (no more than 30 characters).";
    }
    if (empty($phone) || !preg_match('/^[0-9]+$/', $phone) || strlen($phone) > 15) {
        $errors[] = "Phone number is required, must be numeric, and must not exceed 15 characters.";
    }
    if (empty($password) || strlen($password) < 6 || strlen($password) > 30) {
        $errors[] = "Password is required and must be between 6 and 30 characters.";
    }
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        
        $stmt = $conn->prepare("INSERT INTO users (firstName, familyName, email, phone, password) VALUES (?, ?, ?, ?, ?)");

        
        if ($stmt === false) {
            die("Error preparing SQL statement: " . $conn->error);
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("sssss", $firstName, $familyName, $email, $phone, $hashedPassword);

        if ($stmt->execute()) {
            $successMessage = "Registration successful!";
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'index.php'; // Redirect to index.php
                }, 3000); // 3-second delay
            </script>";
        } else {
            $errors[] = "Registration error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="registration.css">
</head>
<body>
    <div class="overlay"></div>
    <div class="registration-container">
        <h1>Registration</h1>
        
        
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif (!empty($successMessage)): ?>
            <div class="success-message" style="color: green; text-align: center; margin-bottom: 20px;">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        
        <p class="help-text">
            If you need help, click <a href="javascript:void(0);" id="helpLink">here</a>.
        </p>

        
        <div id="helpModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Registration Help</h2>
                <p>Here are the limits for the registration fields:</p>
                <ul>
                    <li><strong>First Name:</strong> Maximum 50 characters.</li>
                    <li><strong>Last Name:</strong> Maximum 50 characters.</li>
                    <li><strong>Email:</strong> Must be a valid email address and no more than 30 characters.</li>
                    <li><strong>Phone:</strong> Only numbers, maximum 15 characters.</li>
                    <li><strong>Password:</strong> Between 6 and 30 characters.</li>
                    <li><strong>Confirm Password:</strong> Must match the password field.</li>
                </ul>
            </div>
        </div>

        
        <form id="registrationForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <input type="text" id="firstName" name="firstName" placeholder="First Name" value="<?php echo $firstName; ?>" required maxlength="50">
            <input type="text" id="familyName" name="familyName" placeholder="Last Name" value="<?php echo $familyName; ?>" required maxlength="50">
            <input type="email" id="email" name="email" placeholder="Email" value="<?php echo $email; ?>" required maxlength="30">
            <input type="text" id="phone" name="phone" placeholder="Phone" value="<?php echo $phone; ?>" required maxlength="15">
            <input type="password" id="password" name="password" placeholder="Password" required maxlength="30">
            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required maxlength="30">
            
            
            <div class="checkbox-container">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I accept the terms and privacy policy</label>
            </div>

            <button type="submit">Register</button>
        </form>
    </div>

    
    <script>
        
        var modal = document.getElementById("helpModal");
        var helpLink = document.getElementById("helpLink");
        var span = document.getElementsByClassName("close")[0];

        helpLink.onclick = function() {
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        
        document.getElementById('registrationForm').addEventListener('submit', function() {
            var email = document.getElementById('email').value;
            localStorage.setItem('email', email);
        });

        
        window.addEventListener('load', function() {
            var storedEmail = localStorage.getItem('email');
            if (storedEmail) {
                document.getElementById('email').value = storedEmail;
            }
        });
    </script>
</body>
</html>

<?php

session_start();
$errors = [];
$successMessage = '';

$host = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'registration';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $newPassword = htmlspecialchars(trim($_POST['new_password']));
    $confirmPassword = htmlspecialchars(trim($_POST['confirm_password']));

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($phone) || strlen($phone) > 15) {
        $errors[] = "Please enter a valid phone number (maximum 15 characters).";
    }

    if (empty($newPassword) || strlen($newPassword) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        
        $stmt = $conn->prepare("SELECT email, phone FROM users WHERE email = ? AND phone = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $email, $phone);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
               
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                
                $stmt->close();
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ? AND phone = ?");
                if ($stmt) {
                    $stmt->bind_param("sss", $hashedPassword, $email, $phone);
                    if ($stmt->execute()) {
                        $successMessage = "Password has been successfully changed.";
                    } else {
                        $errors[] = "There was an error updating the password.";
                    }
                } else {
                    $errors[] = "Error preparing the SQL statement.";
                }
            } else {
                $errors[] = "No user is registered with this email and phone number combination.";
            }

            $stmt->close();
        } else {
            $errors[] = "Error preparing the SQL statement.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="forgot_password.css">
</head>
<body>
    <div class="forgot-password-container">
        <div class="forgot-password-box">
            <h1>Forgot Password</h1>
            <p>Enter your email, phone, and new password.</p>

            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php elseif (!empty($successMessage)): ?>
                <div class="success-message">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>

            <form action="forgot_password.php" method="POST">
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                <input type="text" id="phone" name="phone" placeholder="Enter your phone number" required maxlength="15">
                <input type="password" id="new_password" name="new_password" placeholder="New Password" required>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm New Password" required>
                <button type="submit">Submit</button>
            </form>

            <div class="back-to-login">
                <a href="index.php">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>

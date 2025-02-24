<?php
session_start();
$errors = [];
$email = '';

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
    $password = htmlspecialchars(trim($_POST['password']));

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($hashedPassword);
            $stmt->fetch();

            if (password_verify($password, $hashedPassword)) {
              
                $_SESSION['email'] = $email; 
                $_SESSION['loggedin'] = true; 
                header("Location: main.php");
                exit();
            } else {
                $errors[] = "Invalid password.";
            }
        } else {
            $errors[] = "User does not exist.";
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
    <title>Login</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <div class="header">
        <img src="images/logo.png" alt="Logo" class="logo"> 
    </div>

    <div class="login-container">
        <div class="login-box">
            <div class="login-image"></div> 
            <div class="login-content">
                <h1>Welcome Back To Cinema-Island</h1>
                <p>Enter your Details:</p>

                <form action="" method="POST">
                    <input type="email" id="email" name="email" placeholder="Enter your Email" required>

                   
                    <div class="password-container">
                        <input type="password" id="password" name="password" placeholder="Enter your Password" class="password-input" required>
                        <span class="show-hide" id="togglePassword">👁️</span> 
                    </div>
                    
                    <div class="forgot-password">
                        <a href="forgot_password.php">Forgot your password?</a>
                    </div>

                    <button type="submit">Continue</button>
                </form>
                
                <div class="signup-link">
                    Don't have an account? <a href="registration.php">Sign Up</a>
                </div>
            </div>
        </div>
    </div>

    <div id="errorPopup" class="error-popup">
        <div class="error-popup-content">
            <span class="close-popup" id="closePopup">&times;</span>
            <div id="popupMessage"></div>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            this.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
        });

        function showErrorPopup(message) {
            const popupMessage = document.getElementById('popupMessage');
            const errorPopup = document.getElementById('errorPopup');

            popupMessage.textContent = message;
            errorPopup.style.display = 'block';
        }

        document.getElementById('closePopup').addEventListener('click', function () {
            document.getElementById('errorPopup').style.display = 'none';
        });

        window.onclick = function(event) {
            const errorPopup = document.getElementById('errorPopup');
            if (event.target === errorPopup) {
                errorPopup.style.display = 'none';
            }
        }
        <?php if (!empty($errors)): ?>
            const errors = <?php echo json_encode($errors); ?>;
            errors.forEach(error => showErrorPopup(error));
        <?php endif; ?>
    </script>
</body>
</html>
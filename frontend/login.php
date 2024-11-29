<?php
require_once '../backend/db_connection.php';

// Initialize variables
$country_name = $leader_name = $email = $password = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $leader_name = trim($_POST['leader_name']);
    $password = trim($_POST['password']);

    // Check if all fields are filled
    if (empty($leader_name) || empty($password)) {
        $error = "All fields are required.";
    } else {
        try {
            // Prepare and execute the query
            $stmt = $pdo->prepare("SELECT id, country_name, leader_name, password, continent, email_verified FROM users WHERE leader_name = ?");
            $stmt->execute([$leader_name]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Check if email is verified
                if (!$user['email_verified']) {
                    $error = "Please verify your email address before logging in. Check your inbox for the verification link.";
                } else if (password_verify($password, $user['password'])) {
                    // Normal login with existing password
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['country_name'] = $user['country_name'];
                    $_SESSION['leader_name'] = $user['leader_name'];

                    // Redirect based on whether continent is set
                    if ($user['continent'] === null) {
                        header("Location: choose_continent.php");
                    } else {
                        header("Location: home.php");
                    }
                    exit();
                } else {
                    $error = "Invalid leader name or password.";
                }
            } else {
                $error = "Invalid leader name or password.";
            }
        } catch (PDOException $e) {
            $error = "An error occurred. Please try again later.";
            error_log($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nations</title>
    <link rel="stylesheet" type="text/css" href="design/style.css">
</head>
<body>

<div class="container">
    <h1>Login</h1>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST" action="">
        <input type="text" name="leader_name" placeholder="Leader Name" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="submit" value="Login" class="button">
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</div>

</body>
</html>

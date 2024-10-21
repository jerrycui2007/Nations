<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nations";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$leader_name = $password = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $leader_name = trim($_POST['leader_name']);
    $password = trim($_POST['password']);

    // Check if all fields are filled
    if (empty($leader_name) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Prepare and execute the query
        $stmt = $conn->prepare("SELECT id, country_name, leader_name, password FROM users WHERE leader_name = ?");
        $stmt->bind_param("s", $leader_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Password is correct, start a new session
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['country_name'] = $user['country_name'];
                $_SESSION['leader_name'] = $user['leader_name'];

                // Redirect to the game page or dashboard
                header("Location: home.php");
                exit();
            } else {
                $error = "Invalid leader name or password.";
            }
        } else {
            $error = "Invalid leader name or password.";
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

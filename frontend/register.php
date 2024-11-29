<?php
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/gp_functions.php';
require_once '../backend/send_verification_email.php';

// Initialize variables
$country_name = $leader_name = $email = $password = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $country_name = trim($_POST['country_name']);
    $leader_name = trim($_POST['leader_name']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    // Check if all fields are filled
    if (empty($country_name) || empty($leader_name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        try {
            // Check for unique country_name, leader_name, and email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE country_name = ? OR leader_name = ? OR email = ?");
            $stmt->execute([$country_name, $leader_name, $email]);

            if ($stmt->rowCount() > 0) {
                $error = "Country name, leader name, or email already exists.";
            } else {
                // Start a transaction
                $pdo->beginTransaction();

                try {
                    // Insert into users table
                    $verification_token = generateVerificationToken();
                    $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

                    $stmt = $pdo->prepare("
                        INSERT INTO users (country_name, leader_name, email, password, verification_token, token_expiry) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$country_name, $leader_name, $email, $password, $verification_token, $token_expiry]);
                    $user_id = $pdo->lastInsertId();

                    // Send verification email
                    if (!sendVerificationEmail($email, $verification_token)) {
                        throw new Exception("Failed to send verification email");
                    }

                    // Insert into commodities table
                    $stmt = $pdo->prepare("INSERT INTO commodities (id) VALUES (?)");
                    $stmt->execute([$user_id]);

                    // Insert into land table
                    $stmt = $pdo->prepare("INSERT INTO land (id) VALUES (?)");
                    $stmt->execute([$user_id]);

                    // Insert into factories table
                    $stmt = $pdo->prepare("INSERT INTO factories (id) VALUES (?)");
                    $stmt->execute([$user_id]);

                    // Insert into production capacity table
                    $stmt = $pdo->prepare("INSERT INTO production_capacity (id) VALUES (?)");
                    $stmt->execute([$user_id]);

                    // Calculate GP using the calculatePoints function
                    $initial_gp = calculateTotalGP($pdo, $user_id)['total_gp'];

                    // Create defensive division
                    $stmt = $pdo->prepare("INSERT INTO divisions (user_id, name, is_defence) VALUES (?, 'Defence Division', 1)");
                    $stmt->execute([$user_id]);

                    // Insert into buildings table
                    $stmt = $pdo->prepare("INSERT INTO buildings (id) VALUES (?)");
                    $stmt->execute([$user_id]);

                    // Update GP in users table
                    $stmt = $pdo->prepare("UPDATE users SET gp = ? WHERE id = ?");
                    $stmt->execute([$initial_gp, $user_id]);

                    // Insert notification about new nation
                    $notification_message = "The nation <a href='view.php?id=" . $user_id . "'>" . htmlspecialchars($country_name) . "</a> was founded by " . htmlspecialchars($leader_name) . ".";
                    $stmt = $pdo->prepare("INSERT INTO notifications (message, type) VALUES (?, 'New Nation')");
                    $stmt->execute([$notification_message]);

                    $pdo->commit();

                    echo "Registration successful!";
                    header("Location: login.php");
                    exit();
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = "Registration failed. Please try again.";
                    error_log($e->getMessage());
                }
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
    <title>Register - Nations</title>
    <link rel="stylesheet" type="text/css" href="design/style.css">
</head>
<body>

<div class="container">
    <h1>Register</h1>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST" action="">
        <input type="text" name="country_name" placeholder="Country Name" required><br>
        <input type="text" name="leader_name" placeholder="Leader Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="submit" value="Register" class="button">
    </form>
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
</div>

</body>
</html>
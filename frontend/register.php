<?php
global $conn;
require_once '../backend/db_connection.php';

// Initialize variables
$country_name = $leader_name = $email = $password = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $country_name = trim($_POST['country_name']);
    $leader_name = trim($_POST['leader_name']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // Hash the password

    // Check if all fields are filled
    if (empty($country_name) || empty($leader_name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Check for unique country_name, leader_name, and email
        $stmt = $conn->prepare("SELECT * FROM users WHERE country_name = ? OR leader_name = ? OR email = ?");
        $stmt->bind_param("sss", $country_name, $leader_name, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Country name, leader name, or email already exists.";
        } else {
            // Start a transaction
            $conn->begin_transaction();

            try {
                // Prepare and bind for users table
                $stmt = $conn->prepare("INSERT INTO users (country_name, leader_name, email, password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $country_name, $leader_name, $email, $password);

                // Execute and check for errors
                if ($stmt->execute()) {
                    $user_id = $conn->insert_id; // Get the ID of the newly inserted user

                    // Insert into commodities table
                    $stmt_commodities = $conn->prepare("INSERT INTO commodities (id) VALUES (?)");
                    $stmt_commodities->bind_param("i", $user_id);
                    $stmt_commodities->execute();
                    $stmt_commodities->close();

                    // Insert into land table
                    $stmt_land = $conn->prepare("INSERT INTO land (id) VALUES (?)");
                    $stmt_land->bind_param("i", $user_id);
                    $stmt_land->execute();
                    $stmt_land->close();

                    // Insert into factories table
                    $stmt_factories = $conn->prepare("INSERT INTO factories (id) VALUES (?)");
                    $stmt_factories->bind_param("i", $user_id);
                    $stmt_factories->execute();
                    $stmt_factories->close();

                    // Insert into production capacity table
                    $stmt_production_capacity = $conn->prepare("INSERT INTO production_capacity (id) VALUES (?)");
                    $stmt_production_capacity->bind_param("i", $user_id);
                    $stmt_production_capacity->execute();
                    $stmt_production_capacity->close();

                    $conn->commit(); // Commit the transaction

                    echo "Registration successful!";
                    // Redirect to log in or homepage after registration
                    header("Location: login.php");
                    exit();
                } else {
                    throw new Exception("Error: " . $stmt->error);
                }
            } catch (Exception $e) {
                $conn->rollback(); // Rollback the transaction on error
                $error = $e->getMessage();
            }

            $stmt->close();
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

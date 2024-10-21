<?php
session_start();
require_once '../backend/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT country_name, leader_name, population FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch commodities data
$stmt = $conn->prepare("SELECT * FROM commodities WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$commodities = $result->fetch_assoc();
$stmt->close();

// Set points (not in database yet)
$points = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['country_name']); ?> - Nations</title>
    <link rel="stylesheet" type="text/css" href="design/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            padding-bottom: 60px; /* Add padding to accommodate the footer */
        }
        h1 {
            color: #333;
            font-size: 2.5em;
        }
        .nation-info {
            margin-top: 20px;
        }
        .nation-info p {
            margin: 10px 0;
            font-size: 1.2em;
        }
    </style>
</head>
<body>

<h1><?php echo htmlspecialchars($user['country_name']); ?></h1>
<div class="nation-info">
    <p><strong>Leader:</strong> <?php echo htmlspecialchars($user['leader_name']); ?></p>
    <p><strong>Population:</strong> <?php echo number_format($user['population']); ?></p>
    <p><strong>GP:</strong> <?php echo number_format($points); ?></p>
</div>

<?php include 'footer.php'; 
// Close the connection after all queries are done
$conn->close();
?>

</body>
</html>

<?php
global $conn;
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/calculate_points.php';

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
$points = getPointsForUser($conn, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['country_name']); ?> - Nations</title>
    <link rel="stylesheet" type="text/css" href="design/style.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <h1><?php echo htmlspecialchars($user['country_name']); ?></h1>
        <div class="nation-info">
            <p><strong>Leader:</strong> <?php echo htmlspecialchars($user['leader_name']); ?></p>
            <p><strong>Population:</strong> <?php echo number_format($user['population']); ?></p>
            <p><strong>GP:</strong> <?php echo number_format($points); ?></p>
        </div>
    </div>

    <?php 
    include 'footer.php';
    $conn->close();
    ?>
</body>
</html>

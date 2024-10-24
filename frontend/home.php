<?php
global $conn;
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/calculate_points.php';
require_once '../backend/calculate_population_growth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data including commodities and GP
$stmt = $conn->prepare("SELECT u.country_name, u.leader_name, u.population, u.tier, u.gp, c.food, c.power, c.consumer_goods, l.urban_areas 
                        FROM users u 
                        JOIN commodities c ON u.id = c.id 
                        JOIN land l ON u.id = l.id
                        WHERE u.id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Calculate population growth
$population_growth_result = calculatePopulationGrowth($user);
$growth = $population_growth_result['growth'];

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
            padding: 0;
        }
        .content {
            margin-left: 200px; /* Same as sidebar width */
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
    <?php include 'sidebar.php'; ?>
    
    <div class="content">
        <h1><?php echo htmlspecialchars($user['country_name']); ?></h1>
        <div class="nation-info">
            <p><strong>Leader:</strong> <?php echo htmlspecialchars($user['leader_name']); ?></p>
            <p><strong>Population:</strong> <?php echo number_format($user['population']); ?> (<?php echo ($growth >= 0 ? '+' : '') . number_format($growth); ?>)</p>
            <p><strong>Tier:</strong> <?php echo number_format($user['tier']); ?></p>
            <p><strong>GP:</strong> <?php echo number_format($user['gp']); ?></p>
        </div>
    </div>

    <?php 
    include 'footer.php';
    $conn->close();
    ?>
</body>
</html>

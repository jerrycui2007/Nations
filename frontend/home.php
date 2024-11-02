<?php
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/calculate_points.php';
require_once '../backend/calculate_population_growth.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Fetch user data
    $stmt = $pdo->prepare("SELECT u.country_name, u.leader_name, u.population, u.tier, u.gp, 
                           c.food, c.power, c.consumer_goods, l.urban_areas, u.flag 
                           FROM users u 
                           JOIN commodities c ON u.id = c.id 
                           JOIN land l ON u.id = l.id
                           WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate population growth
    $population_growth_result = calculatePopulationGrowth($user);
    $growth = $population_growth_result['growth'];

    // Handle flag update
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_flag'])) {
        $new_flag = trim($_POST['new_flag']);
        
        if (filter_var($new_flag, FILTER_VALIDATE_URL)) {
            $stmt = $pdo->prepare("UPDATE users SET flag = ? WHERE id = ?");
            if ($stmt->execute([$new_flag, $_SESSION['user_id']])) {
                $flag_update_message = "Flag updated successfully!";
                $user['flag'] = $new_flag; // Update the flag in the current page
            } else {
                $flag_update_message = "Error updating flag.";
            }
        } else {
            $flag_update_message = "Invalid URL format.";
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "An error occurred while loading the page.";
}
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
            margin-left: 200px;
            padding: 20px;
            padding-bottom: 60px;
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
        <img src="<?php echo htmlspecialchars($user['flag']); ?>" alt="Flag of <?php echo htmlspecialchars($user['country_name']); ?>" style="width: 100px; height: auto; margin-bottom: 20px;">
        
        <div class="nation-info">
            <p><strong>Leader:</strong> <?php echo htmlspecialchars($user['leader_name']); ?></p>
            <p><strong>Population:</strong> <?php echo number_format($user['population']); ?> (<?php echo ($growth >= 0 ? '+' : '') . number_format($growth); ?>)</p>
            <p><strong>Tier:</strong> <?php echo number_format($user['tier']); ?></p>
            <p><strong>GP:</strong> <?php echo number_format($user['gp']); ?></p>
        </div>

        <!-- Flag Update Form -->
        <h2>Change Flag</h2>
        <?php if (isset($flag_update_message)): ?>
            <p><?php echo htmlspecialchars($flag_update_message); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="new_flag" placeholder="Enter new flag URL" required>
            <button type="submit" class="button">Update Flag</button>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>

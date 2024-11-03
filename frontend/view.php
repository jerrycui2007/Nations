<?php
global $pdo;
session_start();
require_once '../backend/db_connection.php';

// Get nation ID from URL parameter
$nation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($nation_id === 0) {
    header("Location: home.php");
    exit();
}

// Fetch nation data
$stmt = $pdo->prepare("SELECT country_name, leader_name, population, tier, gp, flag 
                       FROM users 
                       WHERE id = ?");
$stmt->execute([$nation_id]);
$nation = $stmt->fetch(PDO::FETCH_ASSOC);

// If nation doesn't exist, redirect to home
if (!$nation) {
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($nation['country_name']); ?> - Nations</title>
    <link rel="stylesheet" type="text/css" href="design/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .header {
            background: url('resources/westberg.png') no-repeat center center;
            background-size: cover;
            padding: 150px 20px;
            color: white;
            position: relative;
            margin-left: 200px;
            width: calc(100% - 200px);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.7);
        }

        .header-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-right: 0px;
        }

        .header-right {
            flex: 1;
            padding-left: 20px;
            border-left: 2px solid rgba(255, 255, 255, 0.5);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .info-group {
            margin-bottom: 20px;
            text-align: center;
        }

        .info-label {
            font-size: 0.9em;
            opacity: 0.8;
            margin-bottom: 5px;
            text-align: center;
        }

        .info-value {
            font-size: 1.8em;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .nation-flag {
            width: 200px;
            height: 120px;
            object-fit: cover;
            margin-bottom: 20px;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .nation-name {
            font-size: 2.5em;
            font-weight: bold;
            text-align: center;
        }

        .tier-icon {
            height: 1em;
            width: auto;
            vertical-align: middle;
        }

        .tier-number {
            color: #FFD700;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <img src="<?php echo htmlspecialchars($nation['flag']); ?>" alt="Nation Flag" class="nation-flag">
                <div class="nation-name"><?php echo htmlspecialchars($nation['country_name']); ?></div>
            </div>
            <div class="header-right">
                <div class="info-group">
                    <div class="info-label">Leader</div>
                    <div class="info-value"><?php echo htmlspecialchars($nation['leader_name']); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Population</div>
                    <div class="info-value">
                        <img src="resources/tier.png" alt="Tier" class="tier-icon">
                        <span class="tier-number"><?php echo $nation['tier']; ?></span>
                        <?php echo number_format($nation['population']); ?>
                    </div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">GP</div>
                    <div class="info-value"><?php echo number_format($nation['gp']); ?></div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>

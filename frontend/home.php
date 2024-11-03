<?php
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/calculate_points.php';
require_once '../backend/calculate_population_growth.php';
require_once 'helpers/resource_display.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Fetch user data
    $stmt = $pdo->prepare("
        SELECT u.country_name, u.leader_name, u.population, u.tier, u.gp, 
               c.food, c.power, c.consumer_goods, l.urban_areas, u.flag 
        FROM users u 
        JOIN commodities c ON u.id = c.id 
        JOIN land l ON u.id = l.id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate population growth
    $population_growth_result = calculatePopulationGrowth($user);
    $growth = $population_growth_result['growth'];

    // Handle flag update
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_flag'])) {
        $new_flag = trim($_POST['new_flag']);
        
        // Check if it's a valid URL
        if (filter_var($new_flag, FILTER_VALIDATE_URL)) {
            // Check if the URL ends with an allowed image extension
            $valid_extensions = array('.jpg', '.jpeg', '.png');
            $is_valid_image = false;
            
            foreach ($valid_extensions as $ext) {
                if (strtolower(substr($new_flag, -strlen($ext))) === $ext) {
                    $is_valid_image = true;
                    break;
                }
            }

            if ($is_valid_image) {
                $stmt = $pdo->prepare("UPDATE users SET flag = ? WHERE id = ?");
                if ($stmt->execute([$new_flag, $_SESSION['user_id']])) {
                    $flag_update_message = "Flag updated successfully!";
                    $user['flag'] = $new_flag;
                } else {
                    $flag_update_message = "Error updating flag.";
                }
            } else {
                $flag_update_message = "Invalid image format. URL must end with .jpg, .jpeg, or .png";
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
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            min-height: 100vh;
            display: flex;
            position: relative;
        }

        .sidebar {
            width: 200px;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            z-index: 1000;
        }

        .main-content {
            flex: 1;
            margin-left: 200px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        .header {
            background: url('resources/westberg.png') no-repeat center center;
            background-size: cover;
            padding: 150px 20px;
            color: white;
            position: relative;
        }

        .content {
            flex: 1;
            padding: 20px;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 10px 0;
            border-top: 1px solid #dee2e6;
            width: 100%;
            margin-left: 0;
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

        .info-value-simple {
            padding-left: 20px;
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

        .panel {
            background: white;
            padding: 20px;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .panel h2 {
            font-size: 1.5em;
            margin-bottom: 10px;
            color: #333;
        }

        .resources-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            margin-left: 0;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <div class="header-content">
                <div class="header-left">
                    <img src="<?php echo htmlspecialchars($user['flag']); ?>" alt="Nation Flag" class="nation-flag">
                    <div class="nation-name"><?php echo htmlspecialchars($user['country_name']); ?></div>
                </div>
                <div class="header-right">
                    <div class="info-group">
                        <div class="info-label">Leader</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['leader_name']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Population</div>
                        <div class="info-value">
                            <img src="resources/tier.png" alt="Tier" class="tier-icon">
                            <span class="tier-number"><?php echo $user['tier']; ?></span>
                            <?php echo number_format($user['population']); ?>
                            <span style="font-size: 0.6em; color: <?php echo ($growth > 0) ? '#28a745' : '#dc3545'; ?>">
                                <?php echo ($growth >= 0 ? '+' : '-') . number_format(abs($growth)); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">GP</div>
                        <div class="info-value"><?php echo formatNumber($user['gp']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="panel">
                <h2>Change Flag</h2>
                <?php if (isset($flag_update_message)): ?>
                    <p><?php echo htmlspecialchars($flag_update_message); ?></p>
                <?php endif; ?>
                <form method="POST" action="" onsubmit="return validateFlagUrl()">
                    <input type="text" name="new_flag" id="new_flag" placeholder="Enter new flag URL" required>
                    <button type="submit" class="button">Update Flag</button>
                </form>
            </div>
        </div>

        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script>
        function validateFlagUrl() {
            const url = document.getElementById('new_flag').value;
            const validExtensions = ['.jpg', '.jpeg', '.png'];
            
            const hasValidExtension = validExtensions.some(ext => 
                url.toLowerCase().endsWith(ext)
            );
            
            if (!hasValidExtension) {
                alert('Invalid image format. URL must end with .jpg, .jpeg, or .png');
                return false;
            }
            
            return true;
        }

        <?php if (isset($flag_update_message)): ?>
            alert("<?php echo addslashes($flag_update_message); ?>");
        <?php endif; ?>
    </script>
</body>
</html>

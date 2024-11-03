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
            padding-left: 0px;
            border-left: 2px solid rgba(255, 255, 255, 0.5);
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

        .info-label {
            font-size: 0.9em;
            opacity: 0.8;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1.8em;
            font-weight: bold;
            margin-bottom: 20px;
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
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <img src="<?php echo htmlspecialchars($user['flag']); ?>" alt="Nation Flag" class="nation-flag">
                <div class="nation-name"><?php echo htmlspecialchars($user['country_name']); ?></div>
            </div>
            <div class="header-right">
                <div class="info-label">Leader</div>
                <div class="info-value"><?php echo htmlspecialchars($user['leader_name']); ?></div>
                
                <div class="info-label">Population</div>
                <div class="info-value">
                    <?php echo number_format($user['population']); ?>
                    <span style="font-size: 0.6em; color: <?php echo ($growth > 0) ? '#28a745' : '#dc3545'; ?>">
                        <?php echo ($growth >= 0 ? '+' : '-') . number_format(abs($growth)); ?>
                    </span>
                </div>
                
                <div class="info-label">GP</div>
                <div class="info-value"><?php echo formatNumber($user['gp']); ?></div>
            </div>
        </div>
    </div>

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

    <?php include 'footer.php'; ?>

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

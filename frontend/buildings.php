<?php
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/building_config.php';
require_once '../backend/resource_config.php';
require_once 'helpers/resource_display.php';
require_once 'helpers/time_display.php';
require_once '../backend/calculate_tier.php';


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's building levels
$stmt = $pdo->prepare("SELECT * FROM buildings WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_buildings = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's resources
$stmt = $pdo->prepare("SELECT * FROM commodities WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_resources = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's land
$stmt = $pdo->prepare("SELECT * FROM land WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_land = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT population FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_population = $stmt->fetch(PDO::FETCH_ASSOC)['population'];
$user_tier = calculateTier($user_population);

function getResourceDisplayName($resource) {
    global $RESOURCE_CONFIG;
    return isset($RESOURCE_CONFIG[$resource]['display_name']) ? $RESOURCE_CONFIG[$resource]['display_name'] : ucwords(str_replace('_', ' ', $resource));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buildings - Nations</title>
    <link rel="stylesheet" type="text/css" href="design/style.css">
    <style>
        body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
        min-height: 100vh;
        }
        .main-content {
            margin-left: 220px;
            padding-bottom: 60px; /* Add space for footer */
        }
        .content {
            padding: 40px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 10px 0;
            border-top: 1px solid #dee2e6;
            position: fixed;
            bottom: 0;
            right: 0;
            width: calc(100% - 220px); /* Viewport width minus sidebar width */
            z-index: 1000;
        }
        h1, h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .smallButton {
            padding: 5px 10px;
            font-size: 14px;
        }
        .building-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .building-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .building-name {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            text-align: left;
        }

        .building-section {
            margin-bottom: 12px;
            text-align: left;
        }

        .building-section-title {
            font-size: 0.9em;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
            text-align: left;
        }

        .building-value {
            display: flex;
            align-items: center;
            gap: 5px;
            text-align: left;
        }

        .upgrade-button {
            width: 100%;
            padding: 8px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .upgrade-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .upgrade-button:not(:disabled):hover {
            background-color: #45a049;
        }

        .ongoing-upgrades-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .upgrade-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .upgrade-title {
            font-size: 1.1em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .upgrade-info {
            color: #666;
            margin: 5px 0;
        }

        .factory-header {
            margin-bottom: 10px;
            text-align: left;
        }

        .factory-name {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            display: block;
            margin-bottom: 5px;
            text-align: left;
        }

        .resource-list {
            margin: 10px 0;
            text-align: left;
        }

        .resource-label {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
            text-align: left;
        }

        .building-description {
            color: #666;
            font-size: 0.9em;
            line-height: 1.4;
            margin: 10px 0;
            padding: 10px;
            background-color: #f8f8f8;
            border-radius: 4px;
            border-left: 3px solid #4CAF50;
        }
    </style>
</head>
<body>
    <?php include 'toast.php'; ?>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content">
            <h1>Buildings</h1>
            <div class="building-grid">
<?php
foreach ($BUILDING_CONFIG as $building_type => $building_data) {
    $current_level = $user_buildings[$building_type] ?? 0;
    $next_level = $current_level + 1;
    $next_level_data = $building_data['levels'][$next_level] ?? null;
    
    echo "<div class='building-card'>";
    echo "<div class='building-name'>{$building_data['name']}</div>";
    
    echo "<div class='building-section'>";
    echo "<div class='building-description'>{$building_data['description']}</div>";
    echo "</div>";
    
    echo "<div class='building-section'>";
    echo "<div class='building-section-title'>CURRENT LEVEL</div>";
    echo "<div class='building-value'>{$current_level}</div>";
    echo "</div>";
    
    if ($next_level_data) {
        echo "<div class='building-section'>";
        echo "<div class='building-section-title'>REQUIRED TIER</div>";
        echo "<div class='building-value'>{$next_level_data['minimum_tier']}</div>";
        echo "</div>";
        
        echo "<div class='building-section'>";
        echo "<div class='building-section-title'>UPGRADE COSTS</div>";
        echo "<div class='building-value'>";
        foreach ($next_level_data['construction_cost'] as $resource => $amount) {
            if ($resource !== 'construction_time') {
                $display_name = getResourceDisplayName($resource);
                $user_amount = $user_resources[$resource] ?? 0;
                $style = $user_amount < $amount ? 'color: #ff4444;' : '';
                echo "<div style='{$style}'>" . getResourceIcon($resource, $display_name) . " " . formatNumber($amount) . "</div>";
            }
        }
        echo "</div></div>";
        
        echo "<div class='building-section'>";
        echo "<div class='building-section-title'>LAND REQUIRED</div>";
        $required_land = $next_level_data['land']['cleared_land'];
        $user_land_amount = $user_land['cleared_land'] ?? 0;
        $land_style = $user_land_amount < $required_land ? 'color: #ff4444;' : '';
        echo "<div class='building-value' style='{$land_style}'>" . getResourceIcon('cleared_land') . formatNumber($required_land) . "</div>";
        echo "</div>";
        
        echo "<div class='building-section'>";
        echo "<div class='building-section-title'>CONSTRUCTION TIME</div>";
        echo "<div class='building-value'>" . formatTimeRemaining($next_level_data['construction_cost']['construction_time']) . "</div>";
        echo "</div>";
        
        // Disable button if requirements not met
        $can_upgrade = true;
        $tier_requirement_met = true;

        // Check tier requirement
        if ($user_tier < $next_level_data['minimum_tier']) {
            $tier_requirement_met = false;
            $can_upgrade = false;
        }

        // Check resource requirements
        foreach ($next_level_data['construction_cost'] as $resource => $amount) {
            if ($resource !== 'construction_time' && ($user_resources[$resource] ?? 0) < $amount) {
                $can_upgrade = false;
                break;
            }
        }
        if ($user_land_amount < $required_land) {
            $can_upgrade = false;
        }

        $button_disabled = $can_upgrade ? '' : 'disabled';
        $button_text = $tier_requirement_met ? 
            "Upgrade to Level {$next_level}" : 
            "REQUIRES TIER {$next_level_data['minimum_tier']}";

        echo "<button class='upgrade-button' {$button_disabled} onclick='upgradeBuilding(\"{$building_type}\")'>{$button_text}</button>";
    } else {
        echo "<div class='building-section'>";
        echo "<div class='building-value'>Maximum level reached</div>";
        echo "</div>";
    }
    
    echo "</div>";
}
?>
    </div>

                <h2>Ongoing Upgrades</h2>
                <div class="ongoing-upgrades-grid">
    <?php
    $stmt = $pdo->prepare("SELECT * FROM building_queue WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    while ($upgrade = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<div class='upgrade-card'>";
        echo "<div class='upgrade-title'>{$BUILDING_CONFIG[$upgrade['building_type']]['name']}</div>";
        echo "<div class='upgrade-info'>Upgrading to Level {$upgrade['level']}</div>";
        echo "<div class='upgrade-info'>" . formatTimeRemaining($upgrade['minutes_left']) . " remaining</div>";
        echo "</div>";
    }

    if ($stmt->rowCount() == 0) {
        echo "<div class='upgrade-card'>";
        echo "<div class='upgrade-info'>No ongoing upgrades</div>";
        echo "</div>";
    }
    ?>
    </div>

        </div>

        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script>
    function upgradeBuilding(buildingType) {
        fetch('../backend/upgrade_building.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `building_type=${buildingType}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                localStorage.setItem('toastMessage', data.message);
                localStorage.setItem('toastType', 'success');
                window.location.reload();
            } else {
                showToast(data.message || 'An error occurred while upgrading the building.', 'error');
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            showToast('An error occurred while processing your request.', 'error');
        });
    }
    </script>
</body>
</html>

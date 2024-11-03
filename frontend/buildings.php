<?php
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/building_config.php';
require_once '../backend/resource_config.php';
require_once 'helpers/resource_display.php';


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's building levels
$stmt = $pdo->prepare("SELECT * FROM buildings WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_buildings = $stmt->fetch(PDO::FETCH_ASSOC);

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
        }
        .content {
            margin-left: 200px; /* Same as sidebar width */
            padding: 20px;
            padding-bottom: 60px; /* Add padding to accommodate the footer */
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
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content">
        <h1>Buildings</h1>
        <?php
        foreach ($BUILDING_CONFIG as $building_type => $building_data) {
            $current_level = $user_buildings[$building_type] ?? 0;
            $next_level = $current_level + 1;
            $next_level_data = $building_data['levels'][$next_level] ?? null;

            
            
            echo "<h2>{$building_data['name']}</h2>";
            echo "<table>";
            echo "<tr><th>Current Level</th><td>{$current_level}</td></tr>";
            
            if ($next_level_data) {
                echo "<tr><th>Required Tier</th><td>{$next_level_data['minimum_tier']}</td></tr>";
                echo "<tr><th>Upgrade Costs</th><td>";
                foreach ($next_level_data['construction_cost'] as $resource => $amount) {
                    if ($resource !== 'construction_time') {
                        $display_name = getResourceDisplayName($resource);
                        echo getResourceIcon($resource, $display_name) . " " . number_format($amount) . "<br>";
                    }
                }
                echo "</td></tr>";
                echo "<tr><th>Land Required</th><td>{$next_level_data['land']['cleared_land']} " . getResourceDisplayName('cleared_land') . "</td></tr>";
                echo "<tr><th>Construction Time</th><td>{$next_level_data['construction_cost']['construction_time']} minutes</td></tr>";
                echo "<tr><td colspan='2' style='text-align: center;'><button class='button smallButton' onclick='upgradeBuilding(\"{$building_type}\")'>Upgrade to Level {$next_level}</button></td></tr>";
            } else {
                echo "<tr><td colspan='2'>Maximum level reached</td></tr>";
            }
            
            echo "</table>";
        }
        echo "<h2>Ongoing Upgrades</h2>";
        echo "<table>";
        echo "<tr><th>Building</th><th>Upgrading to Level</th><th>Time Remaining</th></tr>";

        $stmt = $pdo->prepare("SELECT * FROM building_queue WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        while ($upgrade = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>{$BUILDING_CONFIG[$upgrade['building_type']]['name']}</td>";
            echo "<td>{$upgrade['level']}</td>";
            echo "<td>{$upgrade['minutes_left']} minutes</td>";
            echo "</tr>";
        }

        echo "</table>";
        ?>

    </div>

    <?php include 'footer.php'; ?>

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
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        });
    }
    </script>
</body>
</html>

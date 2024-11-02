<?php
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/resource_config.php';
require_once '../backend/building_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's resources
$stmt = $pdo->prepare("SELECT * FROM commodities WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_resources = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Natural Resources - Nations</title>
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
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content">
        <h1>Natural Resources</h1>
        <table>
            <tr>
                <th>Resource</th>
                <th>Amount</th>
                <th>Type</th>
            </tr>
            <?php
            foreach ($RESOURCE_CONFIG as $resource_key => $resource_data) {
                if (isset($resource_data['is_natural_resource']) && 
                    $resource_data['is_natural_resource'] === true && 
                    isset($user_resources[$resource_key]) && 
                    $user_resources[$resource_key] > 0) {
                    
                    echo "<tr>";
                    echo "<td>{$resource_data['display_name']}</td>";
                    echo "<td>" . number_format($user_resources[$resource_key]) . "</td>";
                    echo "<td>" . ($resource_data['type'] ?? 'Other') . "</td>";
                    echo "</tr>";
                }
            }
            ?>
        </table>

        <h2>Research Buildings</h2>
        <table>
            <tr>
                <th>Building</th>
                <th>Level</th>
                <th>Cost</th>
                <th>Action</th>
            </tr>
            <?php
            // Fetch building levels
            $stmt = $pdo->prepare("SELECT * FROM buildings WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user_buildings = $stmt->fetch(PDO::FETCH_ASSOC);

            foreach ($BUILDING_CONFIG as $building_type => $building_data) {
                $current_level = $user_buildings[$building_type] ?? 0;
                
                if ($current_level > 0) {
                    $cost = $current_level * 1000;
                    echo "<tr>";
                    echo "<td>{$building_data['name']}</td>";
                    echo "<td>{$current_level}</td>";
                    echo "<td>$" . number_format($cost) . "</td>";
                    echo "<td><button class='button smallButton' onclick='gatherResources(\"{$building_type}\")'>Gather Resources</button></td>";
                    echo "</tr>";
                }
            }
            ?>
        </table>

        <script>
        function gatherResources(buildingType) {
            fetch('../backend/gather_resources.php', {
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
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while gathering resources');
            });
        }
        </script>

        <h2>About</h2>
        <p>
            Natural resources are hidden in your territory. Although you gain them each time you expand your borders, you have to hire
            scientists of the right type to discover them and use them. The resources you can discover of each type are dependant on the 
            level of the corresponding building.
        </p>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>

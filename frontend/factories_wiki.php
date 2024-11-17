<?php
require_once 'helpers/resource_display.php';
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/factory_config.php';
require_once '../backend/resource_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function getResourceDisplayName($resourceKey) {
    global $RESOURCE_CONFIG;
    return isset($RESOURCE_CONFIG[$resourceKey]['display_name']) 
        ? $RESOURCE_CONFIG[$resourceKey]['display_name'] 
        : ucfirst($resourceKey);
}

function formatResources($resources) {
    $formatted = [];
    foreach ($resources as $resource) {
        $displayName = getResourceDisplayName($resource['resource']);
        $formatted[] = "{$resource['amount']} {$displayName}";
    }
    return implode(", ", $formatted);
}

function formatResourcesWithIcons($resources) {
    $formatted = [];
    foreach ($resources as $resource) {
        $formatted[] = getResourceIcon($resource['resource']) . " " . number_format($resource['amount']);
    }
    return implode(", ", $formatted);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factories - Nations</title>
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
            padding-bottom: 60px; 
        }
        h1 {
            color: #333;
            font-size: 2.5em;
        }
        .wiki-list {
            margin-top: 20px;
        }
        .wiki-list li {
            margin: 10px 0;
            font-size: 1.2em;
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
        <h1>Factories</h1>
        <p>
            Factories are how your nation produces commodities. There a variety of different factories. Each has their own input, output, and requirements.
        </p>
        <p>
            Factories have a maximum production capacity of 24, and start at 0. This increases by 1 every turn. All factories of the same type have the same production capacity.
            When collecting resources from a factory, you can choose how much production capacity to collect, which will multiply the base input and output of the factory.
            Here is a list of all factories:
        </p>

        <table>
            <tr>
                <th>Name</th>
                <th>Tier</th>
                <th>Input</th>
                <th>Output</th>
                <th>Construction Cost</th>
                <th>Land Requirement</th>
                <th>Construction Time</th>
                <th>GP Value</th>
            </tr>
            <?php
            foreach ($FACTORY_CONFIG as $factory) {
                echo "<tr>";
                echo "<td>{$factory['name']}</td>";
                echo "<td>{$factory['tier']}</td>";
                echo "<td>" . formatResourcesWithIcons($factory['input']) . "</td>";
                echo "<td>" . formatResourcesWithIcons($factory['output']) . "</td>";
                echo "<td>" . formatResourcesWithIcons($factory['construction_cost']) . "</td>";
                echo "<td>{$factory['land']['amount']} " . getResourceDisplayName($factory['land']['type']) . "</td>";
                echo "<td>{$factory['construction_time']} minutes</td>";
                echo "<td>{$factory['gp_value']}</td>";
                echo "</tr>";
            }
            ?>
        </table>

        <h2>See also: </h2>
        <ul class="wiki-list">
            <li><a href="turns_wiki.php">Turns</a></li>
        </ul>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>

<?php
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turns - Nations</title>
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
        <h1>Turns</h1>
        <p>
            Turns are basic unit of time. There are three types of turns: minute turns, hourly turns, and daily turns.
            However, the term "turn" is general refers to hourly turns if not specificed.
        </p>
        <h2>
            Minute Turns
        </h2>
        <p>
            Minute turns are the smallest unit of time. They are used for most actions that happen every turn.
        </p>
        <h2>
            Hourly Turns
        </h2>
        <p>
            Hourly turns are where most things happen. Here is a table of all the things that happen on an hourly turn:
        </p>
        <table>
            <tbody><tr>
                <th>Event</th>
                <th>Details</th>
            </tr>
            <tr>
                <td>Population Growth</td>
                <td>Grows by 1% (see <a href="population_wiki.php">Population</a>) for more details</td>
            </tr>
            <tr>
                <td>Income</td>
                <td>round(Population / 100), only if your nation has sufficient food, power, and consumer goods</td>
            </tr>
            <tr>
                <td>Power</td>
                <td>Decreases by round(Population / 1,000)</td>
            </tr>
            <tr>
                <td>Consumer Goods</td>
                <td>Decreases by round(Population / 5,000)</td>
            </tr>
            <tr>
                <td>Factories</td>
                <td>Production capacity increases by 1 (max 24)</td>
            </tr>
           
        </tbody></table>
        <h2>
                Daily Turns
            </h2>
            <p>
                Daily turns are when things are reset, such as expand borders cooldown.
            </p>

        <h2>See also: </h2>
        <ul class="wiki-list">
            <li><a href="factories_wiki.php">Factories</a></li>
            <li><a href="population_wiki.php">Population</a></li>
        </ul>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>

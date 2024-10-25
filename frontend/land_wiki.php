<?php
session_start();
require_once '../backend/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Land - Nations</title>
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
        <h1>Land</h1>
        <p>
            Land is used by your nation for construction. These are the types of land: cleared land, urban areas, used land, forest, mountain, river, lake, grassland, jungle, desert, and tundra.
            When you create your nation, you start with 50 Cleared Land, 60 Urban Areas, 25 Forest, 5 Rivers, and 5 Lakes.
        </P>
        <p>
            You can gain more land by expaning your borders. The cost of expanding your borders is a base cost of $5000, 1,000 Food, 1,000 Building Materials, and 1,000 Consumer Goods.
            However, these costs are multiplied by (Population / 50,000). After expanding your borders, you will receive a number of land of randomly assorted types equal to  round(Population / 2,000).
            All land types are discoverable, except for Used Land and Urban Areas.
            You can only expand your boders once every daily turn.
        </p>
        <p>
            To grow your population, you will need sufficent Urban Areas. It costs $500 to convert one Cleared Land to Urban Area. You must have 1 Urban Area for every 1,000 Population, or else your 
            population will not grow, or decrease if it is less than that.
        <p>
            Most land types cannot be used directly, and must be converted to Cleared Land first. Here is a table of the costs:
        </p>
        <table>
            <tbody><tr>
                <th>Land Type</th>
                <th>Cost to Convert to Cleared Land</th>
            </tr>
            <tr>
                <td>Forest</td>
                <td>$100</td>
            </tr>
            <tr>
                <td>Mountain</td>
                <td>Cannot convert</td>
            </tr>
            <tr>
                <td>River</td>
                <td>Cannot convert</td>
            </tr>
            <tr>
                <td>Lake</td>
                <td>Cannot convert</td>
            </tr>
            <tr>
                <td>Grassland</td>
                <td>$100</td>
            </tr>
            <tr>
                <td>Jungle</td>
                <td>$300</td>
            </tr>
            <tr>
                <td>Desert</td>
                <td>$500</td>
            </tr>
            <tr>
                <td>Tundra</td>
                <td>$500</td>
            </tr>
        </tbody></table>
        <h2>See also: </h2>
        <ul class="wiki-list">
            <li><a href="population_wiki.php">Population</a></li>
            <li><a href="factories_wiki.php">Factories</a></li>
        </ul>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
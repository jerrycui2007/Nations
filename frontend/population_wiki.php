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
    <title>Population - Nations</title>
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
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content">
        <h1>Population</h1>
        <p>
            Your nation starts with 50,000 population, and will grow automatically. The growth rate is 1%, which means that every turn, 
            your population will increase by 1% of its current value. However, if your Power and Consumer Goods are zero, your population will not grow
            and stay stagnant. And if your Food is zero, then your population will decrease by 1% of its current value instead.
        </p>
        <p>
            In addition, you will require 1 Urban Area for every 1000 population, or else your population will not grow.
            If you have more population than your Urban Areas can support, you will lose population until it matches the limit.
        </p>
        <p>
            Population also impacts your resource consumption, tier and GP.
        </p>
        <h2>See also: </h4>
        <ul class="wiki-list">
            <li><a href="tiers_wiki.php">Tiers</a></li>
            <li><a href="resource_consumption_wiki.php">Resource Consumption</a></li>
            <li><a href="gp_wiki.php">GP</a></li>
        </ul>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
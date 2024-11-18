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
    <title>Tiers - Nations</title>
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
        <h1>Tiers</h1>
        <p>
            Depending on your population, you will be in a certain tier. Each tier unlocks new features and mechanics. 
            There are 10 tiers in total. Here are the tiers:
        </P>
        <table>
            <tr>
                <th>Tier</th>
                <th>Population</th>
            </tr>
            <tr>
                <td>1</td>
                <td>0 - 74,999</td>
            </tr>
            <tr>
                <td>2</td>
                <td>75,000 - 249,999</td>
            </tr>
            <tr>
                <td>3</td>
                <td>250,000 - 499,999</td>
            </tr>
            <tr>
                <td>4</td>
                <td>500,000 - 999,999</td>
            </tr>
            <tr>
                <td>5</td>
                <td>1,000,000 - 1,999,999</td>
            </tr>
            <tr>
                <td>6</td>
                <td>2,000,000 - 4,999,999</td>
            </tr>
            <tr>
                <td>7</td>
                <td>5,000,000 - 9,999,999</td>
            </tr>
            <tr>
                <td>8</td>
                <td>10,000,000 - 17,999,999</td>
            </tr>
            <tr>
                <td>9</td>
                <td>18,000,000 - 74,999,999</td>
            </tr>
            <tr>
                <td>10</td>
                <td>75,000,000</td>
            </tr>
        </table>
        <p>
            Currently, there is a cap of 74,999 population per nation, as tier 2 is not yet implemented.
        </p
        <h2>See also: </h2>
        <ul class="wiki-list">
            <li><a href="population_wiki.php">Population</a></li>
        </ul>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
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
    <title>GP - Nations</title>
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
        <h1>GP</h1>
        <p>
            GP stands for Greatness Points. It is a measure of how powerful your country is, and used in rankings. GP is calculated
            every turn, as well as after every action that impacts your GP. 
        </P>
        <p>
            Your GP is calculated by finding the sum of the following categories:
        </p>
        <table>
            <tr>
                <th>Category</th>
                <th>Formula</th>
            </tr>
            <tr>
                <td>Population</td>
                <td>round(Population / 1000)</td>
            </tr>
            <tr>
                <td>Land</td>
                <td>Total Land</td>
            </tr>
            <tr>
                <td>Industrial</td>
                <td>Each factory has a GP value. For details, see the <a href="factories_wiki.php">factories</a> page.</td>
            </tr>
        </table>
        <h2>See also: </h2>
        <ul class="wiki-list">
            <li><a href="population_wiki.php">Population</a></li>
            <li><a href="land_wiki.php">Land</a></li>
            <li><a href="factories_wiki.php">Factories</a></li>
        </ul>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
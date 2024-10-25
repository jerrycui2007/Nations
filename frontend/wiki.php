<?php
session_start();
require_once '../backend/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get all PHP files in the wiki folder
$wiki_files = glob("wiki/*.php");

// Sort the files alphabetically
sort($wiki_files);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Wiki - Nations</title>
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
        <h1>Game Wiki</h1>
        <p>Welcome to the game wiki. Here you can find information about various game features and mechanics.</p>
        
        <ul class="wiki-list">
            <li><a href="population_wiki.php">Population</a></li>
        </ul>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
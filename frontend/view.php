<?php
global $conn;
session_start();
require_once '../backend/db_connection.php';

// Get nation ID from URL parameter
$nation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($nation_id === 0) {
    header("Location: home.php");
    exit();
}

// Fetch nation data
$stmt = $conn->prepare("SELECT country_name, leader_name, population, tier, gp 
                       FROM users 
                       WHERE id = ?");
$stmt->bind_param("i", $nation_id);
$stmt->execute();
$nation = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If nation doesn't exist, redirect to home
if (!$nation) {
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($nation['country_name']); ?> - Nations</title>
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
            width: 200px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content">
        <h1><?php echo htmlspecialchars($nation['country_name']); ?></h1>
        
        <table>
            <tr>
                <th>Leader</th>
                <td><?php echo htmlspecialchars($nation['leader_name']); ?></td>
            </tr>
            <tr>
                <th>Population</th>
                <td><?php echo number_format($nation['population']); ?></td>
            </tr>
            <tr>
                <th>Tier</th>
                <td><?php echo number_format($nation['tier']); ?></td>
            </tr>
            <tr>
                <th>GP</th>
                <td><?php echo number_format($nation['gp']); ?></td>
            </tr>
        </table>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>

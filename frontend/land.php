<?php
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/calculate_points.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT country_name, leader_name, population FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch land data
$stmt = $conn->prepare("SELECT * FROM land WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$land = $result->fetch_assoc();
$stmt->close();

$points = getPointsForUser($conn, $_SESSION['user_id']);

// Calculate total land
$total_land = array_sum(array_slice($land, 1)); // Sum all land types, excluding the 'id' column
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Land Management - <?php echo htmlspecialchars($user['country_name']); ?></title>
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
        <table>
            <tr>
                <th>Land Type</th>
                <th>Amount</th>
            </tr>
            <tr>
                <td>Total Land</td>
                <td><?php echo number_format($total_land); ?></td>
            </tr>
            <?php
            $land_types = ['cleared_land', 'urban_areas', 'forest', 'mountain', 'river', 'lake', 'grassland', 'jungle', 'desert', 'tundra'];
            foreach ($land_types as $type) {
                echo "<tr>";
                echo "<td>" . ucwords(str_replace('_', ' ', $type)) . "</td>";
                echo "<td>" . number_format($land[$type]) . "</td>";
                echo "</tr>";
            }
            ?>
        </table>
        
        <h2>About</h2>
        <p>
            This table shows the distribution of land types in your country. Most constructions will require Cleared Land to build.
            You can get Cleared Land by clearing the different types of lands.
            You will also need to convert Cleared Land to Urban Areas (1000 people per Urban Area), or your population will not grow.
        </p>

        <h2>Land Conversion Costs</h2>
        <table>
            <tr>
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
        </table>

        <h2>Other Costs</h2>
        <table>
            <tr>
                <th>Action</th>
                <th>Cost</th>
            </tr>
            <tr>
                <td>Convert Cleared Land to Urban Areas</td>
                <td>$500</td>
            </tr>
            <tr>
                <td>Expand borders</td>
                <td>
                    $5000<br>
                    1000 Food<br>
                    1600 Building Materials<br>
                    1000 Consumer Goods
                </td>
            </tr>
        </table>
    </div>

    <?php 
    include 'footer.php';
    $conn->close();
    ?>
</body>
</html>

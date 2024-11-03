<?php
global $pdo;
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/calculate_points.php';
require_once 'helpers/resource_display.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$stmt = $pdo->prepare("SELECT country_name, leader_name, population FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$multiplier = max(1, $user['population'] / 50000);
$money_cost = round(5000 * $multiplier);
$resource_cost = round(1000 * $multiplier);

// Fetch land data
$stmt = $pdo->prepare("SELECT * FROM land WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$land = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate total land
$total_land = array_sum(array_slice($land, 1)); // Sum all land types, excluding the 'id' column

// Define land types
$land_types = ['cleared_land', 'urban_areas', 'used_land','forest', 'mountain', 'river', 'lake', 'grassland', 'jungle', 'desert', 'tundra'];
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
        .smallButton {
            padding: 5px 10px;
        }
    </style>
    
    <script>
        function convertLand(landType) {
            const amount = document.getElementById(`${landType}-convert`).value;
            if (amount <= 0) {
                alert("Please enter a valid amount to convert to Cleared Land.");
                return;
            }

            fetch('../backend/convert_land.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `land_type=${landType}&amount=${amount}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                alert('An error occurred while processing your request. Check the console for more details.');
            });
        }

        function buildUrbanAreas() {
            const amount = document.getElementById('urban-areas-build').value;
            if (amount <= 0) {
                alert("Please enter a valid amount to build Urban Areas.");
                return;
            }

            fetch('../backend/build_urban_areas.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `amount=${amount}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'An error occurred while processing your request.');
                    console.error('Error details:', data.error_details);
                }
            })
            .catch((error) => {
                console.error('Fetch error:', error);
                alert('An error occurred while processing your request. Check the console for more details.');
            });
        }

        function expandBorders() {
            fetch('../backend/expand_borders.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let message = "You have expanded your borders and gained:\n";
                    for (const [landType, amount] of Object.entries(data.newLand)) {
                        if (amount > 0) {
                            message += `${amount} ${landType.replace('_', ' ')}\n`;
                        }
                    }
                    message += `\nNew Greatness Points: ${data.newGP.toLocaleString()}`;
                    alert(message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Not enough resources to expand borders.');
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                alert('An error occurred while processing your request. Check the console for more details.');
            });
        }
    </script>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content">
        <h1>Land</h1>
        <table>
            <tr>
                <th>Land Type</th>
                <th>Amount</th>
                <th>Convert</th>
            </tr>
            <tr>
                <td>Total Land</td>
                <td id="total-land"><?php echo formatNumber($total_land); ?></td>
                <td></td>
            </tr>
            <?php
            $convertible_types = ['forest', 'grassland', 'jungle', 'desert', 'tundra'];
            foreach ($land_types as $type) {
                echo "<tr>";
                echo "<td>" . getResourceIcon($type) . ucwords(str_replace('_', ' ', $type)) . "</td>";
                echo "<td id='{$type}-amount'>" . formatNumber($land[$type]) . "</td>";
                if (in_array($type, $convertible_types)) {
                    echo "<td>";
                    echo "<input type='number' id='{$type}-convert' min='0' max='{$land[$type]}' style='width: 80px;'>";
                    echo "<button onclick='convertLand(\"{$type}\")' class='button smallButton'>Convert to Cleared Land</button>";
                    echo "</td>";
                } elseif ($type === 'urban_areas') {
                    echo "<td>";
                    echo "<input type='number' id='urban-areas-build' min='0' max='{$land['cleared_land']}' style='width: 80px;'>";
                    echo "<button onclick='buildUrbanAreas()' class='button smallButton'>Build Urban Areas</button>";
                    echo "</td>";
                } else {
                    echo "<td></td>";
                }
                echo "</tr>";
            }
            ?>
        </table>

        <button onclick="expandBorders()" class="button">Expand Borders</button>

        <h2>About</h2>
        <p>
            This table shows the distribution of land types in your country. Most constructions will require Cleared Land to build.
            You can get Cleared Land by clearing the different types of lands.
            You will also need to convert Cleared Land to Urban Areas (1000 people per Urban Area), or your population will not grow.
        </p>
        <p>
            After using land, it will be converted to Used Land, regardless of what type it was.
        </p>

        <h2>Land Conversion Costs</h2>
        <table>
            <tr>
                <th>Land Type</th>
                <th>Cost to Convert to Cleared Land</th>
            </tr>
            <tr>
                <td><?php echo getResourceIcon('forest') . 'Forest'; ?></td>
                <td><?php echo getResourceIcon('money') . number_format(100); ?></td>
            </tr>
            <tr>
                <td><?php echo getResourceIcon('mountain') . 'Mountain'; ?></td>
                <td>Cannot convert</td>
            </tr>
            <tr>
                <td><?php echo getResourceIcon('river') . 'River'; ?></td>
                <td>Cannot convert</td>
            </tr>
            <tr>
                <td><?php echo getResourceIcon('lake') . 'Lake'; ?></td>
                <td>Cannot convert</td>
            </tr>
            <tr>
                <td><?php echo getResourceIcon('grassland') . 'Grassland'; ?></td>
                <td><?php echo getResourceIcon('money') . number_format(100); ?></td>
            </tr>
            <tr>
                <td><?php echo getResourceIcon('jungle') . 'Jungle'; ?></td>
                <td><?php echo getResourceIcon('money') . number_format(300); ?></td>
            </tr>
            <tr>
                <td><?php echo getResourceIcon('desert') . 'Desert'; ?></td>
                <td><?php echo getResourceIcon('money') . number_format(500); ?></td>
            </tr>
            <tr>
                <td><?php echo getResourceIcon('tundra') . 'Tundra'; ?></td>
                <td><?php echo getResourceIcon('money') . number_format(500); ?></td>
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
                <td><?php echo getResourceIcon('money') . number_format(500); ?></td>
            </tr>
            <tr>
                <td>Expand borders</td>
                <td>
                    <?php echo getResourceIcon('money') . number_format($money_cost); ?><br>
                    <?php echo getResourceIcon('food') . number_format($resource_cost); ?><br>
                    <?php echo getResourceIcon('building_materials') . number_format($resource_cost); ?><br>
                    <?php echo getResourceIcon('consumer_goods') . number_format($resource_cost); ?>
                </td>
            </tr>
        </table>
    </div>

    <?php 
    include 'footer.php';
    ?>

</body>
</html>

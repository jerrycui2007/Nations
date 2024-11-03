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

// Define convertible land types
$convertible_types = ['forest', 'grassland', 'jungle', 'desert', 'tundra'];

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
$land_types = ['cleared_land', 'urban_areas', 'used_land', 'forest', 'mountain', 'river', 'lake', 'grassland', 'jungle', 'desert', 'tundra'];
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
            min-height: 100vh;
        }

        .main-content {
            margin-left: 220px;
            padding-bottom: 60px; /* Add space for footer */
        }

        .content {
            padding: 40px;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 10px 0;
            border-top: 1px solid #dee2e6;
            position: fixed;
            bottom: 0;
            right: 0;
            width: calc(100% - 220px); /* Viewport width minus sidebar width */
            z-index: 1000;
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

        .land-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .land-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
        }

        .land-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .land-name {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .land-amount {
            background: #4CAF50;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .land-action {
            margin-top: 15px;
        }

        .land-input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .cost-section {
            margin: 10px 0;
        }

        .cost-label {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
            text-align: left;
        }

        .cost-value {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-wrap: wrap;
        }

        .cost-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-right: 10px;
            margin-bottom: 5px;
        }

        .action-button {
            width: 100%;
            padding: 8px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .action-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .info-card {
            grid-column: 1 / -1;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .expand-borders-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }

        .expand-borders-card .action-button {
            margin-top: auto;
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
    
    <div class="main-content">
        <div class="content">
        <h1>Land</h1>
        
        <div class="info-card">
            <div class="land-name">Total Land: <?php echo formatNumber($total_land); ?></div>
        </div>

        <div class="land-grid">
            <div class="expand-borders-card">
                <div class="land-header">
                    <div class="land-name">Expand Borders</div>
                </div>
                <div class="cost-section">
                    <div class="cost-label">EXPANSION COST</div>
                    <div class="cost-value">
                        <span class="cost-item"><?php echo getResourceIcon('money') . formatNumber($money_cost); ?></span>
                        <span class="cost-item"><?php echo getResourceIcon('food') . formatNumber($resource_cost); ?></span>
                        <span class="cost-item"><?php echo getResourceIcon('building_materials') . formatNumber($resource_cost); ?></span>
                        <span class="cost-item"><?php echo getResourceIcon('consumer_goods') . formatNumber($resource_cost); ?></span>
                    </div>
                </div>
                <button onclick="expandBorders()" class="action-button">Expand Borders</button>
            </div>

            <?php foreach ($land_types as $type): 
                $is_convertible = in_array($type, $convertible_types);
                $is_urban = ($type === 'urban_areas');
            ?>
                <div class="land-card">
                    <div class="land-header">
                        <div class="land-name">
                            <?php echo getResourceIcon($type) . ucwords(str_replace('_', ' ', $type)); ?>
                        </div>
                        <div class="land-amount"><?php echo formatNumber($land[$type]); ?></div>
                    </div>

                    <?php if ($is_convertible): ?>
                        <div class="cost-section">
                            <div class="cost-label">CONVERSION COST</div>
                            <div class="cost-value">
                                <?php 
                                $cost = 0;
                                switch($type) {
                                    case 'forest': case 'grassland': $cost = 100; break;
                                    case 'jungle': $cost = 300; break;
                                    case 'desert': case 'tundra': $cost = 500; break;
                                }
                                echo getResourceIcon('money') . formatNumber($cost);
                                ?>
                            </div>
                        </div>
                        <div class="land-action">
                            <input type="number" 
                                class="land-input" 
                                id="<?php echo $type; ?>-convert" 
                                min="0" 
                                max="<?php echo $land[$type]; ?>" 
                                placeholder="Amount to convert">
                            <button class="action-button" 
                                    onclick="convertLand('<?php echo $type; ?>')">
                                Convert to Cleared Land
                            </button>
                        </div>
                    <?php elseif ($is_urban): ?>
                        <div class="cost-section">
                            <div class="cost-label">BUILD COST</div>
                            <div class="cost-value">
                                <?php echo getResourceIcon('money') . formatNumber(500); ?>
                            </div>
                        </div>
                        <div class="land-action">
                            <input type="number" 
                                class="land-input" 
                                id="urban-areas-build" 
                                min="0" 
                                max="<?php echo $land['cleared_land']; ?>" 
                                placeholder="Amount to build">
                            <button class="action-button" 
                                    onclick="buildUrbanAreas()">
                                Build Urban Areas
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="info-card">
            <h2>About</h2>
            <p>This table shows the distribution of land types in your country. Most constructions will require Cleared Land to build.
            You can get Cleared Land by clearing the different types of lands.
            You will also need to convert Cleared Land to Urban Areas (1000 people per Urban Area), or your population will not grow.</p>
            <p>After using land, it will be converted to Used Land, regardless of what type it was.</p>
        </div>
    </div>

        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>
</body>
</html>

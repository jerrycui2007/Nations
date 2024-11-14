<?php
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/resource_config.php';
require_once '../backend/building_config.php';
require_once 'helpers/resource_display.php';
require_once 'toast.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's resources
$stmt = $pdo->prepare("SELECT * FROM commodities WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_resources = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Natural Resources - Nations</title>
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
            flex: 1;
            margin-left: 200px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            padding-bottom: 60px;
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
            width: calc(100% - 200px);
            z-index: 1000;
            margin-left: 200px;
        }
        h1, h2 {
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
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .resource-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .resource-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .resource-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            text-align: left;
        }

        .resource-name {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            text-align: left;
        }

        .resource-amount {
            background: #4CAF50;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .resource-type {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
            text-align: left;
        }

        .building-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .building-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .building-header {
            margin-bottom: 15px;
            text-align: left;
        }

        .building-name {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            text-align: left;
        }

        .building-level {
            color: #666;
            margin: 10px 0;
            text-align: left;
        }

        .building-cost {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 10px 0;
            text-align: left;
        }

        .gather-button {
            width: 100%;
            padding: 8px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .gather-button:hover {
            background-color: #45a049;
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .toast {
            background: white;
            border-radius: 4px;
            padding: 12px 24px;
            margin-bottom: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transform: translateX(120%);
            transition: transform 0.3s ease;
        }

        .toast.success {
            border-left: 4px solid #4CAF50;
        }

        .toast.error {
            border-left: 4px solid #dc3545;
        }

        .toast.show {
            transform: translateX(0);
        }

        .no-results-message {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
            grid-column: 1 / -1;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin: 10px 0;
        }

        select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin-left: 10px;
            min-width: 150px;
        }

        label {
            font-weight: bold;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content">
            <h1>Natural Resources</h1>

<?php
// Get unique resource types
$resource_types = [];
foreach ($RESOURCE_CONFIG as $resource_key => $resource_data) {
    if (isset($resource_data['is_natural_resource']) && 
        $resource_data['is_natural_resource'] === true && 
        isset($resource_data['type'])) {
        $resource_types[$resource_data['type']] = true;
    }
}
$resource_types = array_keys($resource_types);
sort($resource_types);
?>

<div style="margin-bottom: 20px; text-align: left;">
    <label for="resource-filter">Filter by type: </label>
    <select id="resource-filter" onchange="filterResources(this.value)">
        <option value="">All Types</option>
        <?php foreach ($resource_types as $type): ?>
            <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
        <?php endforeach; ?>
    </select>
</div>

            <div class="resource-grid">
                <?php
                foreach ($RESOURCE_CONFIG as $resource_key => $resource_data) {
                    if (isset($resource_data['is_natural_resource']) && 
                        $resource_data['is_natural_resource'] === true && 
                        isset($user_resources[$resource_key]) && 
                        $user_resources[$resource_key] > 0) {
                        
                        echo "<div class='resource-card'>";
                        echo "<div class='resource-header'>";
                        echo "<div class='resource-name'>" . getResourceIcon($resource_key) . " {$resource_data['display_name']}</div>";
                        echo "<div class='resource-amount'>" . formatNumber($user_resources[$resource_key]) . "</div>";
                        echo "</div>";
                        echo "<div class='resource-type'>Type: " . ($resource_data['type'] ?? 'Other') . "</div>";
                        echo "<div class='resource-type'>Tier: " . ($resource_data['tier'] ?? 'N/A') . "</div>";
                        echo "</div>";
                    }
                }
                ?>
            </div>

            <h2>Research Buildings</h2>
            <div class="building-grid">
                <?php
                // Fetch building levels
                $stmt = $pdo->prepare("SELECT * FROM buildings WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user_buildings = $stmt->fetch(PDO::FETCH_ASSOC);

                foreach ($BUILDING_CONFIG as $building_type => $building_data) {
                    $current_level = $user_buildings[$building_type] ?? 0;
                    
                    if ($current_level > 0) {
                        $cost = $current_level * 1000;
                        $can_afford = ($user_resources['money'] ?? 0) >= $cost;
                        
                        echo "<div class='building-card'>";
                        echo "<div class='building-header'>";
                        echo "<div class='building-name'>{$building_data['name']}</div>";
                        echo "<div class='building-level'>Level: {$current_level}</div>";
                        echo "<div class='building-cost'>";
                        echo "<span style='color: " . ($can_afford ? '#333' : '#dc3545') . "'>" . 
                             getResourceIcon('money') . formatNumber($cost) . 
                             "</span>";
                        echo "</div>";
                        echo "<td><button class='gather-button' " . 
                             ($can_afford ? '' : 'disabled') . 
                             " onclick='gatherResources(\"{$building_type}\")'>Gather Resources</button></td>";
                        echo "</div>";
                        echo "</div>";
                    }
                }
                ?>
            </div>

            <script>
            function gatherResources(buildingType) {
                fetch('../backend/gather_resources.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `building_type=${buildingType}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        localStorage.setItem('toastMessage', data.message);
                        localStorage.setItem('toastType', 'success');
                        window.location.reload();
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while gathering resources', 'error');
                });
            }

            function filterResources(type) {
                const resourceCards = document.querySelectorAll('.resource-card');
                let visibleCount = 0;
                
                resourceCards.forEach(card => {
                    const cardType = card.querySelector('.resource-type').textContent.replace('Type: ', '');
                    if (!type || cardType === type) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Add "no results" message if needed
                const existingMsg = document.querySelector('.no-results-message');
                if (existingMsg) {
                    existingMsg.remove();
                }
                
                if (visibleCount === 0) {
                    const message = document.createElement('div');
                    message.className = 'no-results-message';
                    message.textContent = 'No resources found for this type';
                    document.querySelector('.resource-grid').appendChild(message);
                }
            }
            </script>

            <h2>About</h2>
            <p>
                Natural resources are hidden in your territory. Although you gain them each time you expand your borders, you have to hire
                scientists of the right type to discover them and use them. The resources you can discover of each type are dependant on the 
                level of the corresponding building.
            </p>
        </div>

        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>
</body>
</html>

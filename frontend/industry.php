<?php

session_start();
require_once '../backend/db_connection.php';
require_once '../backend/factory_config.php';
require_once '../backend/resource_config.php';
require_once 'helpers/resource_display.php';
require_once 'helpers/time_display.php';


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch factory and production capacity data for the user
$user_id = $_SESSION['user_id'];

// Build dynamic query based on factory_config
$factory_columns = array_keys($FACTORY_CONFIG);
$factory_select = implode(', ', array_map(function($type) { return "f.$type"; }, $factory_columns));
$capacity_select = implode(', ', array_map(function($type) { return "p.$type AS {$type}_capacity"; }, $factory_columns));

$stmt = $pdo->prepare("
    SELECT $factory_select, $capacity_select
    FROM factories f
    JOIN production_capacity p ON f.id = p.id
    WHERE f.id = ?
");
$stmt->execute([$user_id]);
$factories = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch factories under construction
$stmt = $pdo->prepare("SELECT factory_type, minutes_left FROM factory_queue WHERE id = ?");
$stmt->execute([$user_id]);
$factories_under_construction = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Industry - Nations</title>
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
        .resource-icon {
            width: 16px;
            height: 16px;
            vertical-align: middle;
            margin-right: 4px;
        }
        .factory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .factory-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .factory-name {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            text-align: left;
        }

        .factory-section {
            margin-bottom: 12px;
            text-align: left;
        }

        .factory-section-title {
            font-size: 0.9em;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
            text-align: left;
        }

        .factory-value {
            display: flex;
            align-items: center;
            gap: 5px;
            text-align: left;
        }

        .build-button {
            width: 100%;
            padding: 8px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .build-button:hover {
            background-color: #45a049;
        }

        .build-time {
            text-align: center;
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }

        .factory-collection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .factory-collection-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
        }

        .factory-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .factory-amount {
            background: #ff4444;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .progress-bar {
            height: 4px;
            background: #ddd;
            margin-bottom: 15px;
            border-radius: 2px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 0.3s ease;
        }

        .collection-input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .resource-list {
            margin: 10px 0;
            text-align: left;
        }

        .resource-label {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
            text-align: left;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content">
            <h1>Industry</h1>
            <div class="factory-collection-grid">
                <?php foreach ($factories as $factory_type => $amount): ?>
                    <?php if (strpos($factory_type, '_capacity') === false && $amount > 0): 
                        $capacity_key = $factory_type . '_capacity';
                        $capacity = $factories[$capacity_key];
                        $factory_data = $FACTORY_CONFIG[$factory_type];
                        $progress_percent = ($capacity / 24) * 100;
                        
                        // Calculate color gradient from red to green based on progress
                        $hue = ($progress_percent / 100) * 120; // 0 = red, 120 = green
                        $progress_color = "hsl({$hue}, 70%, 45%)";
                    ?>
                        <div class="factory-collection-card">
                            <div class="factory-header">
                                <div class="factory-name"><?php echo $factory_data['name']; ?></div>
                                <div class="factory-amount"><?php echo $amount; ?></div>
                            </div>

                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $progress_percent; ?>%; background-color: <?php echo $progress_color; ?>"></div>
                            </div>

                            <input type="number" 
                                   class="collection-input" 
                                   id="<?php echo $factory_type; ?>-collect"
                                   min="1" 
                                   max="<?php echo $capacity; ?>" 
                                   value="<?php echo $capacity; ?>"
                                   <?php echo $capacity == 0 ? 'disabled' : ''; ?>>

                            <div class="resource-list">
                                <div class="resource-label">INPUT</div>
                                <div class="factory-value">
                                    <?php 
                                    foreach ($factory_data['input'] as $index => $input): 
                                        echo getResourceIcon($input['resource']) . " " . formatNumber($input['amount'] * $amount * $capacity);
                                        if ($index < count($factory_data['input']) - 1) echo "  ";
                                    endforeach; 
                                    ?>
                                </div>
                            </div>

                            <div class="resource-list">
                                <div class="resource-label">OUTPUT</div>
                                <div class="factory-value">
                                    <?php 
                                    foreach ($factory_data['output'] as $index => $output): 
                                        echo getResourceIcon($output['resource']) . " " . formatNumber($output['amount'] * $amount * $capacity);
                                        if ($index < count($factory_data['output']) - 1) echo "  ";
                                    endforeach; 
                                    ?>
                                </div>
                            </div>

                            <button class="build-button" 
                                    onclick="collectResource('<?php echo $factory_type; ?>')"
                                    <?php echo $capacity == 0 ? 'disabled' : ''; ?>>
                                COLLECT
                            </button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php
                echo "<h2>Construct New Factories</h2>";
                echo "<div class='factory-grid'>";
                
                foreach ($FACTORY_CONFIG as $factory_type => $factory) {
                    echo "<div class='factory-card'>";
                    echo "<div class='factory-name'>{$factory['name']}</div>";
                    
                    echo "<div class='factory-section'>";
                    echo "<div class='factory-section-title'>LAND USAGE</div>";
                    echo "<div class='factory-value'>" . getResourceIcon($factory['land']['type']) . " {$factory['land']['amount']}</div>";
                    echo "</div>";
                    
                    echo "<div class='factory-section'>";
                    echo "<div class='factory-section-title'>COSTS</div>";
                    echo "<div class='factory-value'>";
                    foreach ($factory['construction_cost'] as $index => $cost) {
                        echo getResourceIcon($cost['resource']) . " " . formatNumber($cost['amount']);
                        if ($index < count($factory['construction_cost']) - 1) echo "  ";
                    }
                    echo "</div>";
                    echo "</div>";
                    
                    echo "<div class='factory-section'>";
                    echo "<div class='factory-section-title'>INPUT</div>";
                    echo "<div class='factory-value'>";
                    foreach ($factory['input'] as $index => $input) {
                        echo getResourceIcon($input['resource']) . " " . formatNumber($input['amount']);
                        if ($index < count($factory['input']) - 1) echo "  ";
                    }
                    echo "</div>";
                    echo "</div>";
                    
                    echo "<div class='factory-section'>";
                    echo "<div class='factory-section-title'>OUTPUT</div>";
                    echo "<div class='factory-value'>";
                    foreach ($factory['output'] as $index => $output) {
                        echo getResourceIcon($output['resource']) . " " . formatNumber($output['amount']);
                        if ($index < count($factory['output']) - 1) echo "  ";
                    }
                    echo "</div>";
                    echo "</div>";
                    
                    echo "<button class='build-button' onclick='buildFactory(\"{$factory_type}\")'>BUILD</button>";
                    echo "<div class='build-time'>" . formatTimeRemaining($factory['construction_time']) . "</div>";
                    echo "</div>";
                }
                
                echo "</div>";
            ?>

            <h2>Factories Under Construction</h2>
                <table>
                    <tr>
                        <th>Factory Type</th>
                        <th>Time Remaining</th>
                    </tr>
                    <?php if (empty($factories_under_construction)): ?>
                        <tr>
                            <td colspan="2">No factories currently under construction.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($factories_under_construction as $factory): ?>
                            <tr>
                                <td><?php echo ucfirst(str_replace('_', ' ', $factory['factory_type'])); ?></td>
                                <td><?php echo formatTimeRemaining($factory['minutes_left']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>


            <h2>About</h2>
            <p>
                This page lists your factories and their production capacity.
                You can collect resources from your factories by clicking the "Collect" button.
                The input and output of each factory is also shown.
                Factory capacity is updated every hour, to a maximum of 24. You can choose how much capacity you want to collect from each factory.
            </p>

        </div>

        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script>
    function collectResource(factoryType) {
        const inputElement = document.getElementById(`${factoryType}-collect`);
        const amount = parseInt(inputElement.value);
        const maxCapacity = parseInt(inputElement.max);

        if (amount < 1 || amount > maxCapacity) {
            alert(`Please enter a value between 1 and ${maxCapacity}.`);
            return;
        }

        fetch('../backend/collect_resource.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `factory_type=${factoryType}&amount=${amount}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
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

    function updateInputOutput(factoryType) {
        const inputElement = document.getElementById(`${factoryType}-collect`);
        const inputValue = parseInt(inputElement.value) || 0;
        const maxCapacity = parseInt(inputElement.max);
        const factoryCard = inputElement.closest('.factory-collection-card');
        const factoryAmount = parseInt(factoryCard.querySelector('.factory-amount').textContent);
        
        if (inputValue > maxCapacity) {
            inputElement.value = maxCapacity;
        }

        const collectButton = factoryCard.querySelector('button');

        // Disable input and button if capacity is zero
        if (maxCapacity === 0) {
            inputElement.disabled = true;
            collectButton.disabled = true;
            inputElement.style.backgroundColor = '#f0f0f0';
            collectButton.style.backgroundColor = '#f0f0f0';
            collectButton.style.cursor = 'not-allowed';
        } else {
            inputElement.disabled = false;
            collectButton.disabled = false;
            inputElement.style.backgroundColor = '';
            collectButton.style.backgroundColor = '';
            collectButton.style.cursor = '';
        }

        // Get factory config data
        fetch('../backend/get_factory_config.php?type=' + factoryType)
            .then(response => response.json())
            .then(config => {
                // Update input values
                config.input.forEach((input, index) => {
                    const amount = input.amount * inputValue * factoryAmount;
                    const valueContainer = document.querySelector(`span[data-value-container='${factoryType}-input']`);
                    if (valueContainer) {
                        valueContainer.textContent = formatNumber(amount);
                    }
                });

                // Update output values
                config.output.forEach((output, index) => {
                    const amount = output.amount * inputValue * factoryAmount;
                    const valueContainer = document.querySelector(`span[data-value-container='${factoryType}-output']`);
                    if (valueContainer) {
                        valueContainer.textContent = formatNumber(amount);
                    }
                });
            });
    }

    function buildFactory(factoryType) {
        fetch('../backend/build_factory.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `factory_type=${factoryType}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
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

    // Add number formatting function
    function formatNumber(number) {
        if (number < 1000) {
            return number.toLocaleString();
        } else if (number < 1000000) {
            return (number / 1000).toFixed(1) + 'k';
        } else if (number < 1000000000) {
            return (number / 1000000).toFixed(1) + 'm';
        } else if (number < 1000000000000) {
            return (number / 1000000000).toFixed(1) + 'b';
        } else {
            return (number / 1000000000000).toFixed(1) + 't';
        }
    }

    // Add event listeners to all input fields
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('input[type="number"]');
        inputs.forEach(input => {
            const factoryType = input.id.replace('-collect', '');
            input.addEventListener('input', () => updateInputOutput(factoryType));
            // Call updateInputOutput initially to set the correct state
            updateInputOutput(factoryType);
        });
    });
    </script>
</body>
</html>

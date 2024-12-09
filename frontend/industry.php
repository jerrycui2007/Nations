<?php

session_start();
require_once '../backend/db_connection.php';
require_once '../backend/factory_config.php';
require_once '../backend/resource_config.php';
require_once 'helpers/resource_display.php';
require_once 'helpers/time_display.php';
require_once '../backend/calculate_tier.php';

// Get user's current tier
$stmt = $pdo->prepare("SELECT population FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_population = $stmt->fetch(PDO::FETCH_ASSOC)['population'];
$user_tier = calculateTier($user_population);

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

// Fetch user's current resources
$stmt = $pdo->prepare("SELECT * FROM commodities c JOIN land l ON c.id = l.id WHERE c.id = ?");
$stmt->execute([$user_id]);
$user_resources = $stmt->fetch(PDO::FETCH_ASSOC);

function getFactoryOutputResources($FACTORY_CONFIG, $RESOURCE_CONFIG) {
    $output_resources = [];
    
    // First add all non-natural resources from resource config
    foreach ($RESOURCE_CONFIG as $resource => $config) {
        if (isset($config['is_natural_resource']) && $config['is_natural_resource'] === false) {
            $output_resources[$resource] = $config['display_name'] ?? ucfirst(str_replace('_', ' ', $resource));
        }
    }
    
    // Then add any additional resources from factory outputs
    foreach ($FACTORY_CONFIG as $factory) {
        if (isset($factory['output'])) {
            foreach ($factory['output'] as $output) {
                $resource = $output['resource'];
                if (!isset($output_resources[$resource]) && 
                    (!isset($RESOURCE_CONFIG[$resource]['is_natural_resource']) || 
                     $RESOURCE_CONFIG[$resource]['is_natural_resource'] === false)) {
                    $output_resources[$resource] = $RESOURCE_CONFIG[$resource]['display_name'] ?? 
                                                 ucfirst(str_replace('_', ' ', $resource));
                }
            }
        }
    }
    return $output_resources;
}

// Get the output resources
$output_resources = getFactoryOutputResources($FACTORY_CONFIG, $RESOURCE_CONFIG);

// Debug output - remove after confirming
error_log("Available output resources: " . print_r($output_resources, true));

// Add this helper function at the top of the file
function getResourceAmount($user_resources, $resource_key) {
    // Convert to lowercase for consistency
    $resource_key = strtolower($resource_key);
    
    // Try different possible column names
    $possible_keys = [
        $resource_key,
        "`{$resource_key}`",
        strtoupper($resource_key),
        str_replace(' ', '_', $resource_key)
    ];

    foreach ($possible_keys as $key) {
        if (isset($user_resources[$key])) {
            return $user_resources[$key];
        }
    }

    // If no match found, return 0
    return 0;
}
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

        .build-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .build-button:not(:disabled):hover {
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
            margin-bottom: 10px;
            text-align: left;
        }

        .factory-name {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            display: block;
            margin-bottom: 5px;
            text-align: left;
        }

        .factory-amount {
            background: #ff4444;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            display: inline-block;
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

        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            visibility: hidden;
        }

        .popup-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 90%;
            position: relative;
            transform: scale(0.7);
            transition: transform 0.3s ease;
        }

        .popup-active {
            visibility: visible;
        }

        .popup-active .popup-content {
            transform: scale(1);
        }

        .popup-button {
            margin-top: 15px;
            padding: 8px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .popup-button:hover {
            background-color: #45a049;
        }

        .resource-summary {
            margin: 15px 0;
            text-align: left;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 4px;
        }

        .resource-summary-title {
            font-weight: bold;
            color: #666;
            margin-bottom: 8px;
        }

        .resource-summary-item {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 5px 0;
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

        /* Add these new styles */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background-color: #333;
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            margin-bottom: 10px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .toast.success {
            background-color: #4CAF50;
        }

        .toast.error {
            background-color: #f44336;
        }

        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }

        .demolish-button {
            width: 100%;
            padding: 8px;
            background-color: white;  /* Changed to white background */
            color: #dc3545;          /* Red text */
            border: 1px solid #dc3545;  /* Red border */
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            font-size: 0.9em;        /* Match collect button text size */
            transition: all 0.3s ease;
        }

        .demolish-button:hover {
            background-color: #dc3545;  /* Red background on hover */
            color: white;              /* White text on hover */
        }

        .demolish-button:disabled {
            background-color: #cccccc;
            border-color: #cccccc;
            color: white;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .factory-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .factory-card[data-factory-tier] {
            position: relative;
        }

        .factory-card .build-button:disabled {
            cursor: not-allowed;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content">
            <h1>Industry</h1>
            <div style="margin-bottom: 20px; text-align: left;">
                <label for="collect-filter">Filter by output: </label>
                <select id="collect-filter" onchange="filterFactories('collect')">
                    <option value="">All</option>
                    <?php foreach ($output_resources as $resource => $display_name): ?>
                        <option value="<?php echo $resource; ?>"><?php echo $display_name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
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
                        <div class="factory-collection-card" data-factory-tier="<?php echo $factory_data['tier']; ?>">
                            <div class="factory-header">
                                <span class="factory-name"><?php echo $factory_data['name']; ?></span>
                                <span class="factory-amount"><?php echo $amount; ?></span>
                            </div>

                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $progress_percent; ?>%; background-color: <?php echo $progress_color; ?>"></div>
                            </div>

                            <input type="number" 
                                class="collection-input" 
                                id="<?php echo $factory_type; ?>-collect"
                                data-factory-amount="<?php echo $amount; ?>"
                                min="1" 
                                max="<?php echo $capacity; ?>" 
                                value="<?php echo $capacity; ?>"
                                oninput="updateInputOutput('<?php echo $factory_type; ?>')"
                                <?php echo $capacity == 0 ? 'disabled' : ''; ?>>

                            <div class="resource-list">
                                <div class="resource-label">INPUT</div>
                                <div class="factory-value">
                                    <?php 
                                    foreach ($factory_data['input'] as $index => $input): 
                                        $base_amount = $input['amount'] * $amount * 24;
                                        $required_amount = $input['amount'] * $amount * $capacity;
                                        $hourly_amount = $input['amount'] * $amount;
                                        $current_amount = getResourceAmount($user_resources, $input['resource']);
                                        $has_enough = $current_amount >= $required_amount;
                                        $style = $has_enough ? '' : 'color: #dc3545;';
                                        echo '<span style="' . $style . '" ' . 
                                             'data-base-amount="' . $base_amount . '" ' .
                                             'data-current-amount="' . $current_amount . '" ' .
                                             'data-hourly-amount="' . $hourly_amount . '">' . 
                                             getResourceIcon($input['resource']) . " " . 
                                             formatNumber($required_amount) . 
                                             '</span>';
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
                                        $base_amount = $output['amount'] * $amount * 24;
                                        $output_amount = $output['amount'] * $amount * $capacity;
                                        echo '<span data-base-amount="' . $base_amount . '">' . 
                                            getResourceIcon($output['resource']) . " " . 
                                            formatNumber($output_amount) . 
                                            '</span>';
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

                            <button class="demolish-button" 
                                    onclick="demolishFactory('<?php echo $factory_type; ?>', <?php echo htmlspecialchars(json_encode($factory_data), ENT_QUOTES, 'UTF-8'); ?>)">
                                DEMOLISH FACTORY
                            </button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php
                echo "<h2>Construct New Factories</h2>";
                echo "<div style='margin-bottom: 20px; text-align: left;'>";
                echo "<label for='construct-filter'>Filter by output: </label>";
                echo "<select id='construct-filter' onchange='filterFactories('construct')'>";
                echo "<option value=''>All</option>";
                foreach ($output_resources as $resource => $display_name) {
                    echo "<option value='$resource'>$display_name</option>";
                }
                echo "</select>";
                echo "</div>";
                echo "<div class='factory-grid'>";
                
                foreach ($FACTORY_CONFIG as $factory_type => $factory) {
                    echo "<div class='factory-card' data-factory-tier='" . $factory['tier'] . "'>";
                    echo "<div class='factory-name'>{$factory['name']}</div>";
                    
                    // Add input field for amount
                    echo "<input type='number' 
                               class='collection-input' 
                               id='{$factory_type}-build-amount' 
                               min='1' 
                               value='1' 
                               oninput='updateConstructionCosts(\"{$factory_type}\")'>";

                    echo "<div class='factory-section'>";
                    echo "<div class='factory-section-title'>LAND USAGE</div>";
                    echo "<div class='factory-value'>";
                    $current_land = getResourceAmount($user_resources, $factory['land']['type']);
                    $has_enough_land = $current_land >= $factory['land']['amount'];
                    $style = $has_enough_land ? '' : 'color: #dc3545;';
                    echo '<span style="' . $style . '" ' . 
                         'data-base-amount="' . $factory['land']['amount'] . '" ' .
                         'data-current-amount="' . $current_land . '">' . 
                         getResourceIcon($factory['land']['type']) . " " . 
                         formatNumber($factory['land']['amount']) . 
                         '</span>';
                    echo "</div>";
                    echo "</div>";
                    
                    echo "<div class='factory-section'>";
                    echo "<div class='factory-section-title'>COSTS</div>";
                    echo "<div class='factory-value'>";
                    foreach ($factory['construction_cost'] as $index => $cost):
                        $current_amount = getResourceAmount($user_resources, $cost['resource']);
                        $required_amount = $cost['amount']; // Base amount for single factory
                        $has_enough = $current_amount >= $required_amount;
                        $style = $has_enough ? '' : 'color: #dc3545;';
                        echo '<span style="' . $style . '" ' . 
                             'data-base-amount="' . $cost['amount'] . '" ' .
                             'data-current-amount="' . $current_amount . '">' . 
                             getResourceIcon($cost['resource']) . " " . 
                             formatNumber($required_amount) . 
                             '</span>';
                        if ($index < count($factory['construction_cost']) - 1) echo "  ";
                    endforeach;
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
            <div class="factory-collection-grid">
                <?php if (empty($factories_under_construction)): ?>
                    <div class="factory-collection-card">
                        <div class="factory-header">
                            <span class="factory-name">No factories under construction</span>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($factories_under_construction as $factory): ?>
                        <div class="factory-collection-card">
                            <div class="factory-header">
                                <span class="factory-name"><?php echo $FACTORY_CONFIG[$factory['factory_type']]['name']; ?></span>
                            </div>
                            <div class="factory-section">
                                <div class="factory-section-title">TIME REMAINING</div>
                                <div class="factory-value"><?php echo formatTimeRemaining($factory['minutes_left']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

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
    function showToast(message, type = 'success') {
        // Create toast container if it doesn't exist
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;

        // Add toast to container
        container.appendChild(toast);

        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);

        // Remove toast after 5 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // Update the collectResource function
    function collectResource(factoryType) {
        if (window.isCollecting) return;
        window.isCollecting = true;
        
        const inputElement = document.getElementById(`${factoryType}-collect`);
        const amount = parseInt(inputElement.value);
        const maxCapacity = parseInt(inputElement.max);

        if (amount < 1 || amount > maxCapacity) {
            showToast(`Please enter a value between 1 and ${maxCapacity}.`, 'error');
            window.isCollecting = false;
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
                localStorage.setItem('toastMessage', data.message);
                localStorage.setItem('toastType', 'success');
                window.location.reload();
            } else {
                showToast(data.message, 'error');
                window.isCollecting = false;
            }
        })
        .catch(error => {
            showToast('An error occurred', 'error');
            window.isCollecting = false;
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const message = localStorage.getItem('toastMessage');
        const type = localStorage.getItem('toastType');
        
        if (message) {
            showToast(message, type);
            localStorage.removeItem('toastMessage');
            localStorage.removeItem('toastType');
        }
    });

    function buildFactory(factoryType) {
        const inputElement = document.getElementById(`${factoryType}-build-amount`);
        const amount = parseInt(inputElement.value) || 1;

        // Add validation for negative numbers
        if (amount < 1) {
            showToast('Please enter a positive number of factories to build.', 'error');
            return;
        }

        fetch('../backend/build_factory.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `factory_type=${factoryType}&amount=${amount}`
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
        .catch((error) => {
            console.error('Error:', error);
            showToast('An error occurred while processing your request.', 'error');
        });
    }

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

    function filterFactories(section) {
        const filterSelect = document.getElementById(`${section}-filter`);
        const filter = filterSelect.value;
        const filterDisplayName = filter ? filterSelect.options[filterSelect.selectedIndex].text : '';
        
        // Define grid elements and message text
        const collectGrid = document.querySelector('.factory-collection-grid');
        const constructGrid = document.querySelector('.factory-grid');
        const noResultsMsg = filter ? 
            `No factories found that produce ${filterDisplayName}` : 
            'No factories found';
        
        if (section === 'collect') {
            const cards = collectGrid.querySelectorAll('.factory-collection-card');
            let visibleCount = 0;
            
            cards.forEach(card => {
                if (!filter) {
                    card.style.display = '';
                    visibleCount++;
                    return;
                }
                
                const outputSection = Array.from(card.getElementsByClassName('resource-list')).find(section => 
                    section.querySelector('.resource-label')?.textContent.trim() === 'OUTPUT'
                );
                
                if (outputSection && outputSection.querySelector(`img[src*="${filter}_icon"]`)) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Remove existing message if it exists
            const existingMsg = collectGrid.querySelector('.no-results-message');
            if (existingMsg) {
                existingMsg.remove();
            }
            
            // Add message if no visible cards
            if (visibleCount === 0) {
                const message = document.createElement('div');
                message.className = 'no-results-message';
                message.textContent = noResultsMsg;
                collectGrid.appendChild(message);
            }
            
        } else if (section === 'construct') {
            const cards = constructGrid.querySelectorAll('.factory-card');
            let visibleCount = 0;
            
            cards.forEach(card => {
                if (!filter) {
                    card.style.display = '';
                    visibleCount++;
                    return;
                }
                
                const outputSections = Array.from(card.getElementsByClassName('factory-section'));
                const outputSection = outputSections.find(section => 
                    section.querySelector('.factory-section-title')?.textContent.trim() === 'OUTPUT'
                );
                
                if (outputSection && outputSection.querySelector(`.factory-value img[src*="${filter}_icon"]`)) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Remove existing message if it exists
            const existingMsg = constructGrid.querySelector('.no-results-message');
            if (existingMsg) {
                existingMsg.remove();
            }
            
            // Add message if no visible cards
            if (visibleCount === 0) {
                const message = document.createElement('div');
                message.className = 'no-results-message';
                message.textContent = noResultsMsg;
                constructGrid.appendChild(message);
            }
        }
    }

    // Debugging
    document.addEventListener('DOMContentLoaded', function() {
        ['collect', 'construct'].forEach(section => {
            const select = document.getElementById(`${section}-filter`);
            select.addEventListener('change', function() {
                console.log(`${section} filter changed to: ${this.value}`);
                filterFactories(section);
            });
        });
    });

    function updateInputOutput(factoryType) {
        const inputElement = document.getElementById(`${factoryType}-collect`);
        const amount = parseInt(inputElement.value) || 0;
        const factoryAmount = parseInt(inputElement.getAttribute('data-factory-amount')) || 0;

        const card = inputElement.closest('.factory-collection-card');
        
        const resourceLists = card.querySelectorAll('.resource-list');
        const inputSection = Array.from(resourceLists).find(section => 
            section.querySelector('.resource-label')?.textContent.trim() === 'INPUT'
        );
        const outputSection = Array.from(resourceLists).find(section => 
            section.querySelector('.resource-label')?.textContent.trim() === 'OUTPUT'
        );

        if (inputSection) {
            const inputValues = inputSection.querySelectorAll('.factory-value span');
            inputValues.forEach(span => {
                const baseAmount = parseInt(span.getAttribute('data-base-amount'));
                const currentAmount = parseInt(span.getAttribute('data-current-amount'));
                const hourlyAmount = parseInt(span.getAttribute('data-hourly-amount'));
                const newAmount = (baseAmount * amount) / 24;
                const icon = span.querySelector('img').outerHTML;
                
                // Check if user has enough resources for the collection
                const hasEnough = currentAmount >= newAmount;
                span.style.color = hasEnough ? '' : '#dc3545';
                
                span.innerHTML = `${icon} ${formatNumber(newAmount)}`;
            });
        }

        if (outputSection) {
            const outputValues = outputSection.querySelectorAll('.factory-value span');
            outputValues.forEach(span => {
                const baseAmount = parseInt(span.getAttribute('data-base-amount'));
                const newAmount = (baseAmount * amount) / 24;
                const icon = span.querySelector('img').outerHTML;
                span.innerHTML = `${icon} ${formatNumber(newAmount)}`;
            });
        }
    }

    function convertLand(landType) {
        const amount = document.getElementById(`${landType}-convert`).value;
        if (amount <= 0) {
            showToast("Please enter a valid amount to convert to Cleared Land.", "error");
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
                showToast(data.message, "error");
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            showToast('An error occurred while processing your request.', "error");
        });
    }

    function buildUrbanAreas() {
        const amount = document.getElementById('urban-areas-build').value;
        if (amount <= 0) {
            showToast("Please enter a valid amount to build Urban Areas.", "error");
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
                showToast(data.message || 'An error occurred while processing your request.', "error");
                console.error('Error details:', data.error_details);
            }
        })
        .catch((error) => {
            console.error('Fetch error:', error);
            showToast('An error occurred while processing your request.', "error");
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
                showExpansionPopup(data);
            } else {
                showToast(data.message || 'Not enough resources to expand borders.', "error");
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            showToast('An error occurred while processing your request.', "error");
        });
    }

    function formatNumberDisplay(number) {
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

    function updateConstructionCosts(factoryType) {
        const inputElement = document.getElementById(`${factoryType}-build-amount`);
        const amount = parseInt(inputElement.value) || 1;
        
        const card = inputElement.closest('.factory-card');
        
        // Update land usage
        const landSection = card.querySelector('.factory-section:nth-child(3)');
        const landSpan = landSection.querySelector('.factory-value span');
        const landBaseAmount = parseInt(landSpan.getAttribute('data-base-amount'));
        const landCurrentAmount = parseInt(landSpan.getAttribute('data-current-amount'));
        const newLandAmount = landBaseAmount * amount;
        const landIcon = landSpan.querySelector('img').outerHTML;
        landSpan.innerHTML = `${landIcon} ${formatNumberDisplay(newLandAmount)}`;
        landSpan.style.color = landCurrentAmount >= newLandAmount ? '' : '#dc3545';
        
        // Update costs
        const costSection = card.querySelector('.factory-section:nth-child(4)');
        const costSpans = costSection.querySelectorAll('.factory-value span');
        costSpans.forEach(span => {
            const baseAmount = parseInt(span.getAttribute('data-base-amount'));
            const currentAmount = parseInt(span.getAttribute('data-current-amount'));
            const newAmount = baseAmount * amount;
            const icon = span.querySelector('img').outerHTML;
            span.innerHTML = `${icon} ${formatNumberDisplay(newAmount)}`;
            // Update the color based on whether the user can afford the total cost
            span.style.color = currentAmount >= newAmount ? '' : '#dc3545';
        });
    }

    function demolishFactory(factoryType, factoryData) {
        if (!confirm('Are you sure you want to demolish this factory? You will receive:\n' +
                    '- 50% of resource costs refunded\n' +
                    '- 100% of land returned\n' +
                    'This action cannot be undone.')) {
            return;
        }

        fetch('../backend/demolish_factory.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `factory_type=${factoryType}`
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
            showToast('An error occurred while demolishing the factory.', 'error');
        });
    }

    function checkTierRequirements() {
        const userTier = <?php echo $user_tier; ?>;
        const factoryCards = document.querySelectorAll('.factory-card');
        
        factoryCards.forEach(card => {
            const factoryTier = parseInt(card.dataset.factoryTier);
            const buildButton = card.querySelector('.build-button');
            const buildInput = card.querySelector('input[type="number"]');
            
            if (factoryTier > userTier) {
                if (buildButton) {
                    buildButton.disabled = true;
                    buildButton.style.backgroundColor = '#cccccc';
                    buildButton.textContent = `REQUIRES TIER ${factoryTier}`;
                }
                if (buildInput) {
                    buildInput.disabled = true;
                }
                
                // Add grey overlay to the whole card
                card.style.opacity = '0.7';
            }
        });
    }

    // Add this to your existing DOMContentLoaded event listener
    document.addEventListener('DOMContentLoaded', function() {
        checkTierRequirements();
        // ... your existing event listeners ...
    });
    </script>
</body>
</html>

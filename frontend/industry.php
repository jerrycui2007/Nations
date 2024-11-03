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
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content">
            <h1>Industry</h1>
            <table>
                <tr>
                    <th>Factory Type</th>
                    <th>Amount</th>
                    <th>Production Capacity</th>
                    <th>Input</th>
                    <th>Output</th>
                </tr>
                <?php foreach ($factories as $factory_type => $amount): ?>
                    <?php 
                    if (strpos($factory_type, '_capacity') === false && $amount > 0): 
                        $capacity_key = $factory_type . '_capacity';
                        $capacity = $factories[$capacity_key];
                        
                        // Get input/output from factory config
                        $factory_data = $FACTORY_CONFIG[$factory_type];
                        $inputs = array_map(function($input) use ($amount, $capacity, $RESOURCE_CONFIG) {
                            return [
                                'resource' => $input['resource'],
                                'display_name' => $RESOURCE_CONFIG[$input['resource']]['display_name'],
                                'amount' => $input['amount'] * $amount * $capacity
                            ];
                        }, $factory_data['input']);

                        $outputs = array_map(function($output) use ($amount, $capacity, $RESOURCE_CONFIG) {
                            return [
                                'resource' => $output['resource'],
                                'display_name' => $RESOURCE_CONFIG[$output['resource']]['display_name'],
                                'amount' => $output['amount'] * $amount * $capacity
                            ];
                        }, $factory_data['output']);
                    ?>
                        <tr>
                            <td><?php echo $factory_data['name']; ?></td>
                            <td><?php echo $amount; ?></td>
                            <td>
                                <?php 
                                $isDisabled = $capacity == 0 ? 'disabled' : '';
                                ?>
                                <input type="number" id="<?php echo $factory_type; ?>-collect" 
                                       min="1" max="<?php echo $capacity; ?>" 
                                       style="width: 60px;" <?php echo $isDisabled; ?>>
                                / <?php echo $capacity; ?>
                                <button class="button smallButton" 
                                        onclick="collectResource('<?php echo $factory_type; ?>')" 
                                        <?php echo $isDisabled; ?>>
                                    Collect
                                </button>
                            </td>
                            <td>
                                <?php 
                                foreach ($inputs as $input) {
                                    echo getResourceIcon($input['resource']) . 
                                         " <span data-value-container='{$factory_type}-input'>" . 
                                         formatNumber($input['amount']) . 
                                         "</span><br>";
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                foreach ($outputs as $output) {
                                    echo getResourceIcon($output['resource']) . 
                                         " <span data-value-container='{$factory_type}-output'>" . 
                                         formatNumber($output['amount']) . 
                                         "</span><br>";
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
            <?php
                echo "<h2>Construct New Factories</h2>";
                echo "<table>";
                echo "<tr><th>Factory Name</th><th>Input</th><th>Output</th><th>Construction Costs</th><th>Land Requirements</th><th>Construction Time</th><th>Action</th></tr>";

                foreach ($FACTORY_CONFIG as $factory_type => $factory) {
                    echo "<tr>";
                    echo "<td>{$factory['name']}</td>";
                    echo "<td>" . implode("<br>", array_map(function($input) use ($RESOURCE_CONFIG) {
                        return getResourceIcon($input['resource']) . " " . number_format($input['amount']);
                    }, $factory['input'])) . "</td>";
                    echo "<td>" . implode("<br>", array_map(function($output) use ($RESOURCE_CONFIG) {
                        return getResourceIcon($output['resource']) . " " . number_format($output['amount']);
                    }, $factory['output'])) . "</td>";
                    echo "<td>" . implode("<br>", array_map(function($cost) use ($RESOURCE_CONFIG) {
                        return getResourceIcon($cost['resource']) . " " . number_format($cost['amount']);
                    }, $factory['construction_cost'])) . "</td>";
                    echo "<td>" . 
                         getResourceIcon($factory['land']['type']) . 
                         number_format($factory['land']['amount']) . 
                         "</td>";
                    echo "<td>" . formatTimeRemaining($factory['construction_time']) . "</td>";
                    echo "<td><button class='button smallButton' onclick='buildFactory(\"{$factory_type}\")'>Build</button></td>";
                    echo "</tr>";
                }

                echo "</table>";
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
        const factoryAmount = parseInt(inputElement.closest('tr').querySelector('td:nth-child(2)').textContent);
        
        if (inputValue > maxCapacity) {
            inputElement.value = maxCapacity;
        }

        const inputRow = inputElement.closest('tr');
        const collectButton = inputRow.querySelector('button');

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

    // Add number formatting function
    function formatNumber(number) {
        if ($number < 1000) {
            return number_format($number);
        } elseif ($number < 1000000) {
            return number_format($number / 1000, 1) . 'k';
        } elseif ($number < 1000000000) {
            return number_format($number / 1000000, 1) . 'm';
        } elseif ($number < 1000000000000) {
            return number_format($number / 1000000000, 1) . 'b';
        } else {
            return number_format($number / 1000000000000, 1) . 't';
        }
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

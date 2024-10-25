<?php
session_start();
require_once '../backend/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch factory and production capacity data for the user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT f.farm, f.windmill, f.quarry, f.sandstone_quarry, f.sawmill, f.automobile_factory, 
           p.farm AS farm_capacity, p.windmill AS windmill_capacity, p.quarry AS quarry_capacity, 
           p.sandstone_quarry AS sandstone_quarry_capacity, p.sawmill AS sawmill_capacity, 
           p.automobile_factory AS automobile_factory_capacity
    FROM factories f
    JOIN production_capacity p ON f.id = p.id
    WHERE f.id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$factories = $result->fetch_assoc();
$stmt->close();
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
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
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
                    
                    // Calculate input and output based on factory type
                    $inputs = [];
                    $outputs = [];
                    if ($factory_type === 'farm') {
                        $inputs[] = ['resource' => 'Money', 'amount' => $capacity * 7 * $amount];
                        $outputs[] = ['resource' => 'Food', 'amount' => $capacity * $amount];
                    }
                    elseif ($factory_type === 'windmill') {
                        $inputs[] = ['resource' => 'Money', 'amount' => $capacity * 2 * $amount];
                        $outputs[] = ['resource' => 'Power', 'amount' => $capacity * $amount];
                    }
                    elseif ($factory_type === 'quarry' || $factory_type === 'sandstone_quarry' || $factory_type === 'sawmill') {
                        $inputs[] = ['resource' => 'Money', 'amount' => $capacity * 7 * $amount];
                        $outputs[] = ['resource' => 'Building Materials', 'amount' => $capacity * $amount];
                    }
                    elseif ($factory_type === 'automobile_factory') {
                        $inputs[] = ['resource' => 'Money', 'amount' => $capacity * 12 * $amount];
                        $inputs[] = ['resource' => 'Power', 'amount' => $capacity * 10 * $amount];
                        $inputs[] = ['resource' => 'Metal', 'amount' => $capacity * $amount];
                        $outputs[] = ['resource' => 'Consumer Goods', 'amount' => $capacity * 6 * $amount];
                    }
                ?>
                    <tr>
                        <td><?php echo ucfirst(str_replace('_', ' ', $factory_type)); ?></td>
                        <td><?php echo $amount; ?></td>
                        <td>
                            <?php 
                            $capacity = $factories[$capacity_key];
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
                                echo $input['amount'] . ' ' . $input['resource'] . '<br>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            foreach ($outputs as $output) {
                                echo $output['amount'] . ' ' . $output['resource'] . '<br>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </table>

        <h2>Construct New Factories</h2>
        <table>
            <tr>
                <th>Factory Name</th>
                <th>Input</th>
                <th>Output</th>
                <th>Construction Costs</th>
                <th>Land Requirements</th>
                <th>Construction Time</th>
                <th>Action</th>
            </tr>
            <tr>
                <td>Farm</td>
                <td>$7</td>
                <td>1 Food</td>
                <td>$500</td>
                <td>5 Cleared Land</td>
                <td>30 minutes</td>
                <td><button class="button smallButton" onclick="buildFactory('<?php echo 'farm'; ?>')">Build</button></td>
            </tr>
            <tr>
                <td>Windmill</td>
                <td>$2</td>
                <td>1 Power</td>
                <td>$250</td>
                <td>5 Cleared Land</td>
                <td>30 minutes</td>
                <td><button class="button smallButton" onclick="buildFactory('<?php echo 'windmill'; ?>')">Build</button></td>
            </tr>
            <tr>
                <td>Quarry</td>
                <td>$7</td>
                <td>1 Building Material</td>
                <td>$1,000</td>
                <td>5 Mountains</td>
                <td>30 minutes</td>
                <td><button class="button smallButton" onclick="buildFactory('<?php echo 'quarry'; ?>')">Build</button></td>
            </tr>
            <tr>
                <td>Sandstone Quarry</td>
                <td>$7</td>
                <td>1 Building Material</td>
                <td>$1,000</td>
                <td>5 Desert</td>
                <td>30 minutes</td>
                <td><button class="button smallButton" onclick="buildFactory('<?php echo 'sandstone_quarry'; ?>')">Build</button></td>
            </tr>
            <tr>
                <td>Sawmill</td>
                <td>$7</td>
                <td>1 Building Material</td>
                <td>$1,000</td>
                <td>5 Forest</td>
                <td>30 minutes</td>
                <td><button class="button smallButton" onclick="buildFactory('<?php echo 'sawmill'; ?>')">Build</button></td>
            </tr>
            <tr>
                <td>Automobile Factory</td>
                <td>$12<br>10 Power<br>1 Metal</td>
                <td>6 Consumer Goods</td>
                <td>$5,000<br>1,000 Building Materials<br>100 Metal</td>
                <td>5 Cleared Land</td>
                <td>30 minutes</td>
                <td><button class="button smallButton" onclick="buildFactory('<?php echo 'automobile_factory'; ?>')">Build</button></td>
            </tr>
        </table>


        <h2>About</h2>
        <p>
            This page lists your factories and their production capacity.
            You can collect resources from your factories by clicking the "Collect" button.
            The input and output of each factory is also shown.
            Factory capacity is updated every hour, to a maximum of 24. You can choose how much capacity you want to collect from each factory.
        </p>

    </div>

    <?php include 'footer.php'; 
    $conn->close();
    ?>
    
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
        
        // Ensure the input value doesn't exceed the max capacity
        if (inputValue > maxCapacity) {
            inputElement.value = maxCapacity;
        }

        const inputRow = inputElement.closest('tr');
        const inputCell = inputRow.cells[3];
        const outputCell = inputRow.cells[4];
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

        // Update input and output based on factory type
        // Update input and output based on factory type
        if (factoryType === 'farm') {
            inputCell.innerHTML = `$${inputValue * 7 * factoryAmount}`;
            outputCell.innerHTML = `${inputValue * factoryAmount} Food`;
        }
        else if (factoryType === 'windmill') {
            inputCell.innerHTML = `$${inputValue * 2 * factoryAmount}`;
            outputCell.innerHTML = `${inputValue * factoryAmount} Power`;
        }
        else if (factoryType === 'quarry' || factoryType === 'sandstone_quarry' || factoryType === 'sawmill') {
            inputCell.innerHTML = `$${inputValue * 7 * factoryAmount}`;
            outputCell.innerHTML = `${inputValue * factoryAmount} Building Materials`;
        }
        else if (factoryType === 'automobile_factory') {
            inputCell.innerHTML = `$${inputValue * 12 * factoryAmount}<br>${inputValue * 10 * factoryAmount} Power<br>${inputValue * factoryAmount} Metal`;
            outputCell.innerHTML = `${inputValue * 6 * factoryAmount} Consumer Goods`;
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

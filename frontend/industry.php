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
                        $inputs[] = ['resource' => 'money', 'amount' => $capacity * 7 * $amount];
                        $outputs[] = ['resource' => 'food', 'amount' => $capacity * $amount];
                    }
                    elseif ($factory_type === 'windmill') {
                        $inputs[] = ['resource' => 'money', 'amount' => $capacity * 2 * $amount];
                        $outputs[] = ['resource' => 'power', 'amount' => $capacity * $amount];
                    }
                    elseif ($factory_type === 'quarry' || $factory_type === 'sandstone_quarry' || $factory_type === 'sawmill') {
                        $inputs[] = ['resource' => 'money', 'amount' => $capacity * 7 * $amount];
                        $outputs[] = ['resource' => 'building_materials', 'amount' => $capacity * $amount];
                    }
                    elseif ($factory_type === 'automobile_factory') {
                        $inputs[] = ['resource' => 'money', 'amount' => $capacity * 12 * $amount];
                        $inputs[] = ['resource' => 'power', 'amount' => $capacity * 10 * $amount];
                        $inputs[] = ['resource' => 'metal', 'amount' => $capacity * $amount];
                        $outputs[] = ['resource' => 'consumer_goods', 'amount' => $capacity * 6 * $amount];
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
        if (factoryType === 'farm') {
            inputCell.innerHTML = `${inputValue * 7 * factoryAmount} money`;
            outputCell.innerHTML = `${inputValue * factoryAmount} food`;
        }
        else if (factoryType === 'windmill') {
            inputCell.innerHTML = `${inputValue * 2 * factoryAmount} money`;
            outputCell.innerHTML = `${inputValue * factoryAmount} power`;
        }
        else if (factoryType === 'quarry' || factoryType === 'sandstone_quarry' || factoryType === 'sawmill') {
            inputCell.innerHTML = `${inputValue * 7 * factoryAmount} money`;
            outputCell.innerHTML = `${inputValue * factoryAmount} building materials`;
        }
        else if (factoryType === 'automobile_factory') {
            inputCell.innerHTML = `${inputValue * 12 * factoryAmount} money<br>${inputValue * 10 * factoryAmount} power<br>${inputValue * factoryAmount} metal`;
            outputCell.innerHTML = `${inputValue * 6 * factoryAmount} consumer goods`;
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
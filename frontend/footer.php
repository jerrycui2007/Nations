<?php
global $conn;
require_once '../backend/calculate_income.php';

// Fetch user data including population
$stmt = $conn->prepare("SELECT u.id, u.population, c.* FROM users u JOIN commodities c ON u.id = c.id WHERE u.id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

// Calculate income
$income_result = calculateIncome($user_data);

// Define an array of resources to display
$resources = [
    'money' =>               'Money',
    'food' =>                'Food',
    'power' =>               'Power',
    'building_materials' =>  'Building Materials',
    'consumer_goods' =>      'Consumer Goods',
    'metal' =>               'Metal',
    'ammunition' =>          'Ammunition',
    'fuel' =>                'Fuel',
    'uranium' =>             'Uranium',
    'whz' =>                 'WhZ'
];
?>

<footer>
    <div class="resources-container">
        <?php foreach ($resources as $key => $name): ?>
            <div class="resource">
                <span class="resource-name" <?php if ($key === 'money'): ?>id="money-label" data-tooltip="Income: $<?php echo number_format($income_result['increase']); ?> per hour"<?php endif; ?>><?php echo $name; ?>:</span>
                <span class="resource-value"><?php echo number_format($user_data[$key]); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</footer>

<style>
    footer {
        background-color: #f8f9fa;
        padding: 10px 0;
        position: fixed;
        bottom: 0;
        width: 100%;
        border-top: 1px solid #dee2e6;
        z-index: 999;
    }
    .resources-container {
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
        max-width: 1200px;
        margin: 0 auto;
    }
    .resource {
        margin: 5px 10px;
        font-size: 0.9em;
    }
    .resource-name {
        font-weight: bold;
        margin-right: 5px;
    }
    #money-label {
        position: relative;
        cursor: pointer;
    }
    #money-label::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background-color: #f0f0f0;
        color: #333;
        padding: 5px 10px;
        border-radius: 3px;
        white-space: nowrap;
        opacity: 0;
        transition: opacity 0.3s, visibility 0.3s;
        visibility: hidden;
        pointer-events: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        z-index: 2000;
    }
    #money-label:hover::after {
        opacity: 1;
        visibility: visible;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const moneyLabel = document.getElementById('money-label');
    
    moneyLabel.addEventListener('mouseenter', function() {
        fetch('get_income_info.php')
            .then(response => response.json())
            .then(data => {
                moneyLabel.setAttribute('data-tooltip', `Income: $${data.income_increase} per turn`);
            })
            .catch(error => console.error('Error:', error));
    });
});
</script>


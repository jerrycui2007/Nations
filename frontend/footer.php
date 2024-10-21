<?php
global $conn;
require_once '../backend/calculate_income.php';
require_once '../backend/calculate_food_consumption.php';
require_once '../backend/calculate_power_consumption.php';
require_once '../backend/calculate_consumer_goods_consumption.php';

// Fetch user data including population
$stmt = $conn->prepare("SELECT u.id, u.population, c.* FROM users u JOIN commodities c ON u.id = c.id WHERE u.id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

// Calculate income and food consumption
$income_result = calculateIncome($user_data);
$food_consumption_result = calculateFoodConsumption($user_data);
$power_consumption_result = calculatePowerConsumption($user_data);
$consumer_goods_consumption_result = calculateConsumerGoodsConsumption($user_data);

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
                <span class="resource-name" 
                    <?php if ($key === 'money'): ?>
                        id="money-label" 
                        data-tooltip="Income: $<?php echo number_format($income_result['increase']); ?> per turn"
                    <?php elseif ($key === 'food'): ?>
                        id="food-label" 
                        data-tooltip="Consumption: <?php echo number_format($food_consumption_result['consumption']); ?> per turn"
                    <?php elseif ($key === 'power'): ?>
                        id="power-label" 
                        data-tooltip="Consumption: <?php echo number_format($power_consumption_result['consumption']); ?> per turn"
                    <?php elseif ($key === 'consumer_goods'): ?>
                        id="consumer-goods-label" 
                        data-tooltip="Consumption: <?php echo number_format($consumer_goods_consumption_result['consumption']); ?> per turn"
                    <?php endif; ?>
                ><?php echo $name; ?>:</span>
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
    .resource-name[data-tooltip] {
        position: relative;
        cursor: pointer;
    }
    .resource-name[data-tooltip]::after {
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
    .resource-name[data-tooltip]:hover::after {
        opacity: 1;
        visibility: visible;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const resourceLabels = document.querySelectorAll('.resource-name[data-tooltip]');
    
    resourceLabels.forEach(label => {
        label.addEventListener('mouseenter', function() {
            const resourceType = this.id.replace('-label', '');
            fetch(`get_${resourceType}_info.php`)
                .then(response => response.json())
                .then(data => {
                    if (data.consumption !== undefined) {
                        this.setAttribute('data-tooltip', `Consumption: ${data.consumption} per turn`);
                    } else if (data.income_increase !== undefined) {
                        this.setAttribute('data-tooltip', `Income: $${data.income_increase} per turn`);
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });
});
</script>

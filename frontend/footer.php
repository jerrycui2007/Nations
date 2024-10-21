<?php
// Fetch commodities data
global $conn;
$stmt = $conn->prepare("SELECT * FROM commodities WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$commodities = $result->fetch_assoc();
$stmt->close();

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
                <span class="resource-name"><?php echo $name; ?>:</span>
                <span class="resource-value"><?php echo number_format($commodities[$key]); ?></span>
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
</style>

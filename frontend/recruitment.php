<?php
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/unit_config.php';
require_once '../backend/building_config.php';
require_once '../backend/resource_config.php';
require_once 'helpers/resource_display.php';
require_once 'helpers/time_display.php';
require_once '../backend/calculate_tier.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Get unique unit types for filter
$unit_types = array_unique(array_map(function($unit) {
    return $unit['type'];
}, $UNIT_CONFIG));
sort($unit_types);

// Fetch user's resources
$stmt = $pdo->prepare("SELECT * FROM commodities WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_resources = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's buildings
$stmt = $pdo->prepare("SELECT * FROM buildings WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_buildings = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch daily units
$stmt = $pdo->prepare("SELECT unit_type FROM daily_unit WHERE id = 1");
$stmt->execute();
$daily_unit = $stmt->fetch(PDO::FETCH_ASSOC);
$daily_unit_type = $daily_unit ? $daily_unit['unit_type'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Military Recruitment - Nations</title>
    <link rel="stylesheet" type="text/css" href="design/style.css">
    <style>
        .main-content {
            margin-left: 220px;
            padding: 15px;
        }

        .content {
            max-width: 1400px;
            margin: 0 auto;
        }

        .unit-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 35px;
            padding: 15px 0;
        }

        .unit-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .unit-content {
            flex: 1 0 auto;
            display: flex;
            flex-direction: column;
        }

        .unit-name {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }

        .unit-stat {
            margin-bottom: 4px;
            position: relative;
            height: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .combat-stat {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
            height: 16px;
        }

        .unit-stat-label {
            color: #666;
            font-size: 0.8em;
            width: 35px;
            text-align: right;
            min-width: 35px;
            display: inline-block;
            margin-right: 8px;
        }

        .unit-stat-bar-container {
            width: calc(100% - 43px); /* 35px label + 8px gap */
            height: 100%;
            background: #eee;
            border-radius: 3px;
            overflow: hidden;
            position: relative;
        }

        .unit-stat-bar {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
        }

        .unit-stat-content {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 1px 6px;
            display: flex;
            align-items: center;
            font-size: 0.8em;
            color: white;
        }

        .stat-firepower { background-color: #ff4444; }
        .stat-armour { background-color: #4444ff; }
        .stat-maneuver { background-color: #ff9933; }
        .stat-hp { background-color: #44bb44; }
        .stat-hp-low { background-color: #ffc107; }
        .stat-hp-dead { background-color: #dc3545; }

        .unit-stat.upkeep-stat {
            background: none;
            color: #666;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .unit-stat.upkeep-stat .unit-stat-label {
            color: #666;
            margin-right: 8px;
        }

        .special-abilities {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .special-ability {
            font-style: italic;
            color: #666;
            margin-top: 5px;
        }

        .build-button {
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .build-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .filter-section {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .filter-label {
            color: #666;
            font-size: 0.9em;
            text-transform: uppercase;
        }

        #unit-filter {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-width: 200px;
            font-size: 0.9em;
        }

        .unit-type {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
            margin-top: -10px;
            font-style: italic;
        }

        .recruitment-time {
            text-align: center;
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }

        .unit-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .unit-content {
            flex: 1 0 auto;
            display: flex;
            flex-direction: column;
        }

        .unit-actions {
            margin-top: auto;
            flex-shrink: 0;
        }

        .unit-stats-container {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .toast {
            background-color: #333;
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            margin-top: 10px;
            opacity: 0;
            transition: opacity 0.3s ease-in;
        }

        .toast.success {
            background-color: #4CAF50;
        }

        .toast.error {
            background-color: #f44336;
        }

        .toast.show {
            opacity: 1;
        }

        .recruitment-queue-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
            margin-bottom: 40px;
        }

        .queue-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .queue-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .queue-info {
            color: #666;
            font-size: 0.9em;
        }

        h2 {
            margin-top: 40px;
            margin-bottom: 20px;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 10px 0;
            border-top: 1px solid #dee2e6;
            position: fixed;
            bottom: 0;
            right: 0;
            width: calc(100% - 220px);
            z-index: 1000;
        }

        .limited-time-badge {
            display: inline-block;
            background-color: #ff9933;
            color: white;
            font-size: 0.7em;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 8px;
            vertical-align: middle;
            font-weight: normal;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content">
            <h1>Military Recruitment</h1>
            
            <div class="filter-section">
                <label for="unit-filter" class="filter-label">Filter by type:</label>
                <select id="unit-filter" onchange="filterUnits()">
                    <option value="">All</option>
                    <?php foreach ($unit_types as $type): ?>
                        <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="unit-grid">
                <?php foreach ($UNIT_CONFIG as $unit_key => $unit): ?>
                    <?php
                    // Skip units that aren't recruitable and aren't the daily unit
                    error_log("Checking unit: " . $unit_key . " - Recruitable: " . ($unit['recruitable'] ? 'true' : 'false'));
                    if ((!isset($unit['recruitable']) || $unit['recruitable'] === false) && $unit_key !== $daily_unit_type) {
                        continue;
                    }
                    
                    // Check building requirements
                    $building_requirements_met = true;
                    foreach ($unit['building_requirements'] as $building => $required_level) {
                        $current_level = $user_buildings[$building] ?? 0;
                        if ($current_level < $required_level) {
                            $building_requirements_met = false;
                            break;
                        }
                    }
                    ?>
                    <div class="unit-card" data-unit-type="<?php echo $unit['type']; ?>">
                        <div class="unit-content">
                            <div class="unit-name">
                                <?php echo $unit['name']; ?>
                                <?php if ($unit_key === $daily_unit_type && (!isset($unit['recruitable']) || $unit['recruitable'] === false)): ?>
                                    <span class="limited-time-badge">Limited Time</span>
                                <?php endif; ?>
                            </div>
                            <div class="unit-type"><?php echo $unit['type']; ?></div>
                            
                            <div class="unit-stats-container">
                                <div class="unit-stat">
                                    <span class="unit-stat-label">FIR:</span>
                                    <div class="unit-stat-bar-container">
                                        <div class="unit-stat-bar stat-firepower" style="width: <?php echo min(($unit['firepower'] / 20) * 100, 100); ?>%"></div>
                                        <div class="unit-stat-content">
                                            <span><?php echo $unit['firepower']; ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="unit-stat">
                                    <span class="unit-stat-label">DEF:</span>
                                    <div class="unit-stat-bar-container">
                                        <div class="unit-stat-bar stat-armour" style="width: <?php echo min(($unit['armour'] / 20) * 100, 100); ?>%"></div>
                                        <div class="unit-stat-content">
                                            <span><?php echo $unit['armour']; ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="unit-stat">
                                    <span class="unit-stat-label">MAN:</span>
                                    <div class="unit-stat-bar-container">
                                        <div class="unit-stat-bar stat-maneuver" style="width: <?php echo min(($unit['maneuver'] / 20) * 100, 100); ?>%"></div>
                                        <div class="unit-stat-content">
                                            <span><?php echo $unit['maneuver']; ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="unit-stat">
                                    <span class="unit-stat-label">HP:</span>
                                    <div class="unit-stat-bar-container">
                                        <div class="unit-stat-bar stat-hp" style="width: 100%"></div>
                                        <div class="unit-stat-content">
                                            <span><?php echo $unit['hp']; ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="unit-stat upkeep-stat">
                                    <span class="unit-stat-label">Upkeep:</span>
                                    <span>
                                        <?php 
                                        $upkeep_strings = [];
                                        foreach ($unit['upkeep'] as $resource => $amount) {
                                            $upkeep_strings[] = getResourceIcon($resource) . formatNumber($amount);
                                        }
                                        echo implode(' ', $upkeep_strings) ?: 'None';
                                        ?>
                                    </span>
                                </div>

                                <div class="unit-stat upkeep-stat">
                                    <span class="unit-stat-label">Cost:</span>
                                    <span>
                                        <?php 
                                        $cost_strings = [];
                                        foreach ($unit['recruitment_cost'] as $resource => $amount) {
                                            $current_amount = $user_resources[$resource] ?? 0;
                                            $has_enough = $current_amount >= $amount;
                                            $style = $has_enough ? '' : 'color: #ff4444;';
                                            $cost_strings[] = '<span style="' . $style . '">' . 
                                                            getResourceIcon($resource) . formatNumber($amount) .
                                                            '</span>';
                                        }
                                        echo implode(' ', $cost_strings) ?: 'None';
                                        ?>
                                    </span>
                                </div>

                                <?php if (!empty($unit['special_abilities'])): ?>
                                    <div class="special-abilities">
                                        <div class="unit-stat-label">Special Abilities:</div>
                                        <?php foreach ($unit['special_abilities'] as $ability): ?>
                                            <div class="special-ability"><?php echo $ability; ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="unit-actions">
                            <?php if (!$building_requirements_met): ?>
                                <?php
                                $missing_requirements = [];
                                foreach ($unit['building_requirements'] as $building => $required_level) {
                                    $current_level = $user_buildings[$building] ?? 0;
                                    if ($current_level < $required_level) {
                                        $building_name = strtoupper($BUILDING_CONFIG[$building]['name']);
                                        $missing_requirements[] = "$building_name LEVEL $required_level";
                                    }
                                }
                                ?>
                                <button class="build-button" disabled>
                                    REQUIRES <?php echo implode(', ', $missing_requirements); ?>
                                </button>
                            <?php else: ?>
                                <button class="build-button" onclick="recruitUnit('<?php echo $unit_key; ?>')">
                                    RECRUIT
                                </button>
                            <?php endif; ?>
                            <div class="recruitment-time">
                                <?php echo formatTimeRemaining($unit['recruitment_time']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h2>Recruitment Queue</h2>
            <div class="recruitment-queue-grid">
                <?php
                $stmt = $pdo->prepare("SELECT * FROM unit_queue WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);

                while ($unit = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<div class='queue-card'>";
                    echo "<div class='queue-title'>{$UNIT_CONFIG[$unit['unit_type']]['name']}</div>";
                    echo "<div class='queue-info'>" . formatTimeRemaining($unit['minutes_left']) . " remaining</div>";
                    echo "</div>";
                }

                if ($stmt->rowCount() == 0) {
                    echo "<div class='queue-card'>";
                    echo "<div class='queue-info'>No units being recruited</div>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <div class="toast-container"></div>

    <script>
    function filterUnits() {
        const filterValue = document.getElementById('unit-filter').value.toLowerCase();
        const units = document.querySelectorAll('.unit-card');
        
        units.forEach(unit => {
            const unitType = unit.getAttribute('data-unit-type').toLowerCase();
            if (!filterValue || unitType === filterValue) {
                unit.style.display = 'flex';
            } else {
                unit.style.display = 'none';
            }
        });
    }

    function showToast(message, type = 'success') {
        const toastContainer = document.querySelector('.toast-container');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        
        toastContainer.appendChild(toast);
        
        // Trigger reflow
        toast.offsetHeight;
        
        // Add show class
        toast.classList.add('show');
        
        // Remove toast after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toastContainer.removeChild(toast);
            }, 300);
        }, 3000);
    }

    function recruitUnit(unitType) {
        fetch('../backend/recruit_unit.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `unit_type=${unitType}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                localStorage.setItem('toastMessage', data.message);
                localStorage.setItem('toastType', 'success');
                window.location.reload();
            } else {
                showToast(data.message, "error");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while recruiting the unit', "error");
        });
    }

    // Add this to check for stored toast messages when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        const message = localStorage.getItem('toastMessage');
        const type = localStorage.getItem('toastType');
        
        if (message) {
            showToast(message, type);
            localStorage.removeItem('toastMessage');
            localStorage.removeItem('toastType');
        }
    });
    </script>
</body>
</html> 
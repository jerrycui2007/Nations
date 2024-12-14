<?php
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/unit_config.php';
require_once '../backend/equipment_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get unit ID from URL parameter
$unit_id = $_GET['unit_id'] ?? 0;

// Fetch unit and its division's combat status
$stmt = $pdo->prepare("
    SELECT u.*, d.in_combat as division_in_combat,
           e1.equipment_id as e1_id, e1.name as e1_name, e1.type as e1_type, e1.rarity as e1_rarity, e1.is_foil as e1_foil,
           e2.equipment_id as e2_id, e2.name as e2_name, e2.type as e2_type, e2.rarity as e2_rarity, e2.is_foil as e2_foil,
           e3.equipment_id as e3_id, e3.name as e3_name, e3.type as e3_type, e3.rarity as e3_rarity, e3.is_foil as e3_foil,
           e4.equipment_id as e4_id, e4.name as e4_name, e4.type as e4_type, e4.rarity as e4_rarity, e4.is_foil as e4_foil
    FROM units u
    LEFT JOIN divisions d ON u.division_id = d.division_id
    LEFT JOIN equipment e1 ON u.equipment_1_id = e1.equipment_id
    LEFT JOIN equipment e2 ON u.equipment_2_id = e2.equipment_id
    LEFT JOIN equipment e3 ON u.equipment_3_id = e3.equipment_id
    LEFT JOIN equipment e4 ON u.equipment_4_id = e4.equipment_id
    WHERE u.unit_id = ? AND u.player_id = ?
");
$stmt->execute([$unit_id, $_SESSION['user_id']]);
$unit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$unit) {
    header("Location: military.php");
    exit();
}

$division_in_combat = $unit['division_in_combat'] ?? false;

$unit_type = strtolower(str_replace(' ', '_', $unit['name']));
$unit_type = strtolower(str_replace('-', '_', $unit_type));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($unit['custom_name']); ?> - Nations</title>
    <link rel="stylesheet" type="text/css" href="design/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            min-height: 100vh;
            display: flex;
            position: relative;
        }

        .sidebar {
            width: 200px;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            z-index: 1000;
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

        .header {
            background: url('resources/<?php echo $unit_type; ?>.png') no-repeat center center;
            background-size: cover;
            padding: 200px 20px;
            color: white;
            position: relative;
            background-color: #2c3e50;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.7);
        }

        .header-left {
            flex: 1;
        }

        .unit-name {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .unit-type {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .content {
            flex: 1;
            padding: 20px;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 10px 0;
            border-top: 1px solid #dee2e6;
            width: calc(100% - 200px);
            position: fixed;
            bottom: 0;
            right: 0;
            z-index: 0;
            margin-left: 200px;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            padding: 20px;
            align-items: start;
        }

        .content-left, .content-right {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            height: auto;
            min-height: min-content;
        }

        .unit-stats-container {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            flex: 1;
            height: auto;
            min-height: min-content;
        }

        .unit-stat {
            margin-bottom: 4px;
            position: relative;
            height: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
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
            width: 200px;
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

        .stat-level { background-color: #ffc107; }
        .stat-firepower { background-color: #dc3545; }
        .stat-armour { background-color: #0d6efd; }
        .stat-maneuver { background-color: #fd7e14; }
        .stat-hp { background-color: #44bb44; }
        .stat-hp-low { background-color: #ffc107; }
        .stat-hp-dead { background-color: #dc3545; }
        .stat-xp { background-color: #9933ff; }

        .buff-list {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .buff-item {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 4px;
            line-height: 1.4;
            word-wrap: break-word;
            padding-right: 10px;
            white-space: pre-wrap;
            position: relative;
            padding-left: 20px;
        }

        .buff-item:before {
            content: "•";
            position: absolute;
            left: 8px;
        }

        .buff-stat {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            position: relative;
            display: flex;
            flex-direction: column;
            height: auto;
            min-height: min-content;
        }

        .buff-content {
            width: 100%;
            text-align: left;
            margin-top: 20px;
            padding-bottom: 20px;
            height: auto;
            min-height: min-content;
        }

        .buff-stat .unit-stat-label {
            position: absolute;
            top: 10px;
        }

        .equipment-slots {
            width: 100%;
            padding: 20px;
            margin-top: 20px;
        }

        .equipment-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .equipment-slot {
            background: white;
            border-radius: 8px;
            min-height: 250px;
            position: relative;
            padding: 15px;
        }

        .empty-slot {
            height: 100%;
            border: 2px dashed #ccc;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }

        .slot-type {
            color: #666;
            margin-bottom: 15px;
            font-size: 1.1em;
            text-align: center;
        }

        .add-equipment-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 2px solid #4CAF50;
            color: #4CAF50;
            font-size: 1.2em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .add-equipment-btn:hover:not([disabled]) {
            background: #4CAF50;
            color: white;
        }

        .add-equipment-btn[disabled] {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .unequip-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .unequip-btn i {
            font-size: 0.8em;
        }

        .equipment-header {
            padding: 15px 20px 15px 15px;
            margin-bottom: 0;
            position: relative;
            z-index: 5;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding-right: 100px;
        }

        .equipment-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .equipment-modal-content {
            position: relative;
            background: white;
            margin: 50px auto;
            padding: 20px;
            width: 90%;
            max-width: 1200px;
            max-height: 80vh;
            overflow-y: auto;
            border-radius: 8px;
        }

        .equip-button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 15px;
        }

        .equip-button:hover {
            background-color: #45a049;
        }

        .equip-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .equipment-modal .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .available-equipment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .equipment-slot .equipment-card {
            height: 100%;
            border-radius: 8px;
        }

        .equipment-slot .equipment-header {
            padding: 15px;
        }

        .equipment-slot .buff-list {
            padding: 10px 15px;
        }

        .equipment-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .equipment-header {
            padding: 15px 20px;
            margin-bottom: 0;
            position: relative;
            z-index: 5;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        /* Rarity styles */
        .rarity-common .equipment-header { background: transparent; }
        .rarity-uncommon .equipment-header { background: #2a9d38; color: white; }
        .rarity-rare .equipment-header { background: #0066cc; color: white; }
        .rarity-epic .equipment-header { background: #9933cc; color: white; }
        .rarity-legendary .equipment-header { background: #ff9900; color: white; }

        .rarity-uncommon .equipment-type,
        .rarity-rare .equipment-type,
        .rarity-epic .equipment-type,
        .rarity-legendary .equipment-type {
            color: rgba(255, 255, 255, 0.9);
        }

        .buff-list {
            position: relative;
            z-index: 4;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 4px;
            padding: 10px;
        }

        .buff-item {
            padding: 5px 0;
            color: #444;
        }

        .equipment-name-container {
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: flex-start;
            width: 100%;
        }

        .equipment-name {
            font-weight: bold;
            font-size: 1.1em;
        }

        .equipment-type {
            font-size: 0.9em;
            color: #666;
        }

        .equipment-content {
            padding: 15px;
            position: relative;
            z-index: 3;
            border-radius: 0 0 8px 8px;
        }

        .has-foil .equipment-content {
            background: url('resources/foil.gif');
            background-size: cover;
        }

        .rename-equipment-btn {
            background: none;
            border: none;
            color: inherit;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 0.8em;
            border-radius: 4px;
            opacity: 0.7;
            transition: opacity 0.3s;
        }

        .rename-equipment-btn:hover {
            opacity: 1;
            background: rgba(0, 0, 0, 0.1);
        }

        .equipment-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .equipment-content {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .buff-list {
            flex-grow: 1;
            margin-bottom: 0;
            list-style: none;
            padding: 10px;
        }

        .buff-item {
            padding: 5px 0;
            color: #444;
            list-style-type: none;
        }

        .buff-item::before {
            content: none;
        }

        .equip-button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin-top: auto;
        }

        .equip-button:hover {
            background-color: #45a049;
        }

        .equip-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        #loading-indicator {
            width: 100%;
            padding: 20px;
            text-align: center;
            color: #666;
        }

        .equipment-modal-content {
            position: relative;
            background: white;
            margin: 50px auto;
            padding: 20px;
            width: 90%;
            max-width: 1200px;
            max-height: 80vh;
            overflow-y: auto;
            border-radius: 8px;
            scroll-behavior: smooth;
        }

    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <div class="header-content">
                <div class="header-left">
                    <div class="unit-name"><?php echo htmlspecialchars($unit['custom_name']); ?></div>
                    <div class="unit-type"><?php echo strtoupper(htmlspecialchars($unit['name'])); ?></div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="content-grid">
                <div class="content-left">
                    <div class="unit-stats-container">
                        <!-- Level -->
                        <div class="unit-stat">
                            <span class="unit-stat-label">LVL:</span>
                            <div class="unit-stat-bar-container">
                                <div class="unit-stat-bar stat-level" style="width: <?php echo min(($unit['level'] / 15) * 100, 100); ?>%"></div>
                                <div class="unit-stat-content">
                                    <span><?php echo $unit['level']; ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Firepower -->
                        <div class="unit-stat">
                            <span class="unit-stat-label">FIR:</span>
                            <div class="unit-stat-bar-container">
                                <div class="unit-stat-bar stat-firepower" style="width: <?php echo min(($unit['firepower'] / 20) * 100, 100); ?>%"></div>
                                <div class="unit-stat-content">
                                    <span><?php echo $unit['firepower']; ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Armour -->
                        <div class="unit-stat">
                            <span class="unit-stat-label">DEF:</span>
                            <div class="unit-stat-bar-container">
                                <div class="unit-stat-bar stat-armour" style="width: <?php echo min(($unit['armour'] / 20) * 100, 100); ?>%"></div>
                                <div class="unit-stat-content">
                                    <span><?php echo $unit['armour']; ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Maneuver -->
                        <div class="unit-stat">
                            <span class="unit-stat-label">MAN:</span>
                            <div class="unit-stat-bar-container">
                                <div class="unit-stat-bar stat-maneuver" style="width: <?php echo min(($unit['maneuver'] / 20) * 100, 100); ?>%"></div>
                                <div class="unit-stat-content">
                                    <span><?php echo $unit['maneuver']; ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- HP -->
                        <div class="unit-stat">
                            <span class="unit-stat-label">HP:</span>
                            <div class="unit-stat-bar-container">
                                <div class="unit-stat-bar <?php 
                                    if ($unit['hp'] <= 0) echo 'stat-hp-dead';
                                    elseif ($unit['hp'] < $unit['max_hp'] / 2) echo 'stat-hp-low';
                                    else echo 'stat-hp';
                                ?>" style="width: <?php echo ($unit['hp'] / $unit['max_hp']) * 100; ?>%"></div>
                                <div class="unit-stat-content">
                                    <span><?php echo $unit['hp'] . '/' . $unit['max_hp']; ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- XP -->
                        <div class="unit-stat">
                            <span class="unit-stat-label">XP:</span>
                            <div class="unit-stat-bar-container">
                                <?php
                                $current_level = $unit['level'];
                                $next_level = $current_level + 1;
                                $xp_for_next_level = $LEVEL_CONFIG[$next_level] ?? $LEVEL_CONFIG[count($LEVEL_CONFIG)];
                                $xp_percentage = ($unit['xp'] / $xp_for_next_level) * 100;
                                ?>
                                <div class="unit-stat-bar stat-xp" style="width: <?php echo min($xp_percentage, 100); ?>%"></div>
                                <div class="unit-stat-content">
                                    <span><?php echo $unit['xp'] . '/' . $xp_for_next_level; ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Buffs -->
                        <?php
                        // Initialize array to store all buffs
                        $all_buffs = [];

                        // Add any existing buffs from the unit
                        if (!empty($unit['buff_descriptions'])) {
                            $buffs = array_filter(explode(',', $unit['buff_descriptions']));
                            foreach ($buffs as $buff) {
                                if (trim($buff)) {
                                    $all_buffs[] = trim($buff);
                                }
                            }
                        }

                        // Add buffs from equipped items
                        for ($slot = 1; $slot <= 4; $slot++) {
                            if ($unit["e{$slot}_id"]) {
                                $stmt = $pdo->prepare("
                                    SELECT eb.buff_type, eb.value, b.description as buff_description
                                    FROM equipment_buffs eb
                                    LEFT JOIN buffs b ON eb.value = b.buff_id
                                    WHERE eb.equipment_id = ?
                                ");
                                $stmt->execute([$unit["e{$slot}_id"]]);
                                $equipment_buffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($equipment_buffs as $buff) {
                                    switch ($buff['buff_type']) {
                                        case 'Firepower':
                                            $all_buffs[] = '+' . $buff['value'] . ' Firepower';
                                            break;
                                        case 'Armour':
                                            $all_buffs[] = '+' . $buff['value'] . ' Armour';
                                            break;
                                        case 'Maneuver':
                                            $all_buffs[] = '+' . $buff['value'] . ' Maneuver';
                                            break;
                                        case 'Health':
                                            $all_buffs[] = '+' . $buff['value'] . '% max health';
                                            break;
                                        case 'Buff':
                                            if ($buff['buff_description']) {
                                                $all_buffs[] = $buff['buff_description'];
                                            }
                                            break;
                                    }
                                }
                            }
                        }

                        // Display buffs if any exist
                        if (!empty($all_buffs)): ?>
                            <div class="unit-stat buff-stat">
                                <span class="unit-stat-label">Buffs:</span>
                                <div class="buff-content">
                                    <?php 
                                    $all_buffs = array_unique($all_buffs);
                                    foreach ($all_buffs as $buff) {
                                        echo "<div class='buff-item'>" . htmlspecialchars($buff) . "</div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="content-right">
                    <div class="unit-details">
                        <!-- Left side: Existing unit stats -->
                        <div class="unit-stats">
                            <!-- Your existing unit stats code -->
                        </div>

                        <!-- Right side: Equipment slots -->
                        <div class="equipment-slots">
                            <h3>Equipment</h3>
                            <div class="equipment-grid">
                                <?php
                                // Get unit type from database to determine equipment slot types
                                $unit_type = $unit['type']; // Assuming this comes from your unit query
                                $equipment_types = $TYPE_CONFIG[$unit_type];
                                
                                for ($slot = 1; $slot <= 4; $slot++) {
                                    $equipment_id = $unit["e{$slot}_id"];
                                    $slot_type = $equipment_types[$slot];
                                    ?>
                                    <div class="equipment-slot" data-slot="<?php echo $slot; ?>">
                                        <?php if ($equipment_id): ?>
                                            <?php
                                            // Create equipment item array for the card component
                                            $item = [
                                                'equipment_id' => $equipment_id,
                                                'name' => $unit["e{$slot}_name"],
                                                'type' => $unit["e{$slot}_type"],
                                                'rarity' => $unit["e{$slot}_rarity"],
                                                'is_foil' => $unit["e{$slot}_foil"],
                                                'buffs' => [],
                                                'show_rename' => false
                                            ];

                                            // Fetch equipment buffs
                                            $stmt = $pdo->prepare("
                                                SELECT eb.buff_type, eb.value, b.description as buff_description
                                                FROM equipment_buffs eb
                                                LEFT JOIN buffs b ON eb.value = b.buff_id
                                                WHERE eb.equipment_id = ?
                                            ");
                                            $stmt->execute([$equipment_id]);
                                            $buffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            
                                            foreach ($buffs as $buff) {
                                                $item['buffs'][] = [
                                                    'buff_type' => $buff['buff_type'],
                                                    'value' => $buff['value'],
                                                    'description' => $buff['buff_description']
                                                ];
                                            }
                                            ?>
                                            <!-- Display equipped item -->
                                            <?php include 'components/equipment_card.php'; ?>
                                            <button class="unequip-btn" 
                                                    onclick="unequipItem(<?php echo $equipment_id; ?>, <?php echo $unit['unit_id']; ?>, <?php echo $slot; ?>)"
                                                    <?php echo $division_in_combat ? 'disabled title="Cannot modify equipment while in combat"' : ''; ?>>
                                                <i class="fas fa-times"></i> Unequip
                                            </button>
                                        <?php else: ?>
                                            <!-- Empty slot -->
                                            <div class="empty-slot">
                                                <div class="slot-type"><?php echo htmlspecialchars($slot_type); ?></div>
                                                <button class="add-equipment-btn" 
                                                        onclick="showEquipmentSelector(<?php echo $unit['unit_id']; ?>, <?php echo $slot; ?>, '<?php echo $slot_type; ?>')"
                                                        <?php echo $division_in_combat ? 'disabled title="Cannot modify equipment while in combat"' : ''; ?>>
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <<!-- Equipment Selection Modal -->
                    <div id="equipmentSelectorModal" class="equipment-modal">
                        <div class="equipment-modal-content">
                            <span class="close" onclick="closeEquipmentSelector()">&times;</span>
                            <h2>Select Equipment</h2>
                            <div id="available-equipment" class="available-equipment-grid"></div>
                            <div id="loading-indicator" style="display: none; text-align: center; padding: 20px;">
                                Loading more equipment...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let isLoading = false;
        let hasMoreItems = true;

        function showEquipmentSelector(unitId, slot, slotType) {
            const modal = document.getElementById('equipmentSelectorModal');
            const equipmentContainer = document.getElementById('available-equipment');
            const unitLevel = <?php echo $unit['level']; ?>; // Add this line to get unit level
            
            // Reset pagination variables
            currentPage = 1;
            isLoading = false;
            hasMoreItems = true;
            
            // Clear existing content
            equipmentContainer.innerHTML = '';
            
            // Show modal
            modal.style.display = 'block';
            
            // Load initial items
            loadMoreEquipment(unitId, slot, slotType, unitLevel);
            
            // Add scroll event listener
            const modalContent = modal.querySelector('.equipment-modal-content');
            modalContent.addEventListener('scroll', () => {
                if (shouldLoadMore(modalContent)) {
                    loadMoreEquipment(unitId, slot, slotType, unitLevel);
                }
            });
        }

        function shouldLoadMore(element) {
            return !isLoading && 
                hasMoreItems && 
                (element.scrollHeight - element.scrollTop - element.clientHeight < 100);
        }

        function loadMoreEquipment(unitId, slot, slotType, unitLevel) {
            if (isLoading || !hasMoreItems) return;
            
            isLoading = true;
            const loadingIndicator = document.getElementById('loading-indicator');
            loadingIndicator.style.display = 'block';
            
            fetch(`../backend/get_available_equipment.php?type=${encodeURIComponent(slotType)}&unit_level=${unitLevel}&page=${currentPage}&per_page=10`)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
                        });
                    }
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Raw response:', text);
                            throw new Error(`JSON parse error: ${e.message}`);
                        }
                    });
                })
                .then(data => {
                    const equipmentContainer = document.getElementById('available-equipment');
                    
                    // Update hasMoreItems based on server response
                    hasMoreItems = data.has_more;
                    
                    if (data.equipment.length === 0) {
                        loadingIndicator.style.display = 'none';
                        return;
                    }
                    
                    // Process equipment items
                    Promise.all(data.equipment.map(item => {
                        return fetch('../backend/get_equipment_card.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(item)
                        })
                        .then(response => response.text())
                        .then(html => {
                            const equipmentDiv = document.createElement('div');
                            equipmentDiv.innerHTML = html;
                            const equipButton = document.createElement('button');
                            equipButton.className = 'equip-button';
                            equipButton.onclick = () => equipItem(item.equipment_id, unitId, slot);
                            equipButton.textContent = 'Equip';
                            equipmentDiv.querySelector('.equipment-card').appendChild(equipButton);
                            return equipmentDiv;
                        });
                    })).then(elements => {
                        elements.forEach(element => equipmentContainer.appendChild(element));
                        currentPage++;
                        isLoading = false;
                        loadingIndicator.style.display = 'none';
                        
                        // If we have more items but no scrollbar, load more
                        const modalContent = document.querySelector('.equipment-modal-content');
                        if (hasMoreItems && modalContent.scrollHeight <= modalContent.clientHeight) {
                            loadMoreEquipment(unitId, slot, slotType);
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading equipment:', error);
                    loadingIndicator.style.display = 'none';
                    isLoading = false;
                    alert('Error loading equipment. Check console for details.');
                });
        }

        function closeEquipmentSelector() {
            document.getElementById('equipmentSelectorModal').style.display = 'none';
        }

        function equipItem(equipmentId, unitId, slot) {
            fetch('../backend/equip_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `equipment_id=${equipmentId}&unit_id=${unitId}&slot=${slot}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            });
        }

        function unequipItem(equipmentId, unitId, slot) {
            fetch('../backend/unequip_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `equipment_id=${equipmentId}&unit_id=${unitId}&slot=${slot}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
    </script>
</body>
</html> 

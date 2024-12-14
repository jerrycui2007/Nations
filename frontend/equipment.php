<?php
session_start();
require_once '../backend/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's equipment with buffs
$stmt = $pdo->prepare("
    SELECT e.*, eb.buff_type, eb.value, b.description as buff_description, b.target,
           CASE WHEN (u.equipment_1_id = e.equipment_id OR 
                     u.equipment_2_id = e.equipment_id OR 
                     u.equipment_3_id = e.equipment_id OR 
                     u.equipment_4_id = e.equipment_id) 
                THEN u.unit_id 
                ELSE NULL 
           END as equipped_unit_id
    FROM equipment e
    LEFT JOIN equipment_buffs eb ON e.equipment_id = eb.equipment_id
    LEFT JOIN buffs b ON eb.value = b.buff_id AND eb.buff_type = 'Buff'
    LEFT JOIN units u ON (e.equipment_id = u.equipment_1_id OR 
                         e.equipment_id = u.equipment_2_id OR 
                         e.equipment_id = u.equipment_3_id OR 
                         e.equipment_id = u.equipment_4_id)
    WHERE e.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$equipment_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group equipment buffs by equipment_id
$equipment = [];
foreach ($equipment_data as $row) {
    if (!isset($equipment[$row['equipment_id']])) {
        $equipment[$row['equipment_id']] = [
            'equipment_id' => $row['equipment_id'],
            'name' => $row['name'],
            'rarity' => $row['rarity'],
            'type' => $row['type'],
            'is_foil' => $row['is_foil'],
            'buffs' => [],
            'show_rename' => true,
            'equipped' => !is_null($row['equipped_unit_id'])
        ];
    }
    if ($row['buff_type']) {
        // Skip if we've already added this buff
        $buff_exists = false;
        foreach ($equipment[$row['equipment_id']]['buffs'] as $existing_buff) {
            if ($existing_buff['buff_type'] === $row['buff_type'] && 
                $existing_buff['value'] === $row['value']) {
                $buff_exists = true;
                break;
            }
        }
        
        if (!$buff_exists) {
            $equipment[$row['equipment_id']]['buffs'][] = [
                'buff_type' => $row['buff_type'],
                'value' => $row['value'],
                'description' => $row['buff_description'],
                'target' => $row['target']
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment - Nations</title>
    <link rel="stylesheet" type="text/css" href="design/style.css">
    <style>
        .main-content {
            margin-left: 220px;
            padding: 15px;
            padding-bottom: 60px;
        }

        .content {
            max-width: 1400px;
            margin: 0 auto;
        }

        .equipment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px 0;
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

        .rarity-common .equipment-header {
            background: transparent;
        }

        .rarity-uncommon .equipment-header {
            background: #2a9d38;
            color: white;
        }

        .rarity-rare .equipment-header {
            background: #0066cc;
            color: white;
        }

        .rarity-epic .equipment-header {
            background: #6a1b9a;
            color: white;
        }

        .rarity-legendary .equipment-header {
            background: #e65100;
            color: white;
        }

        .equipment-name {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .equipment-type {
            font-size: 0.9em;
            color: #666;
            transition: color 0.2s;
            margin-top: 5px;
            align-self: flex-start;
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

        .footer {
            background-color: #f8f9fa;
            padding: 10px 0;
            border-top: 1px solid #dee2e6;
            width: calc(100% - 200px);
            position: fixed;
            bottom: 0;
            right: 0;
            z-index: 6;
            margin-left: 200px;
        }

        .rarity-uncommon .equipment-type,
        .rarity-rare .equipment-type,
        .rarity-epic .equipment-type,
        .rarity-legendary .equipment-type,
        .rarity-uncommon .foil-indicator,
        .rarity-rare .foil-indicator,
        .rarity-epic .foil-indicator,
        .rarity-legendary .foil-indicator {
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

        .foil-indicator {
            font-size: 0.8em;
            color: #666;
            font-style: italic;
            margin-top: 5px;
            position: relative;
        }

        .equipment-name-container {
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: flex-start;
            width: 100%;
        }

        .rename-equipment-btn {
            background: none;
            border: 1px solid currentColor;
            color: inherit;
            cursor: pointer;
            font-size: 0.8em;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
            opacity: 0.8;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .rename-equipment-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
            opacity: 1;
        }

        .rename-equipment-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .rename-equipment-btn i {
            font-size: 0.9em;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 25px;
            border: none;
            width: 90%;
            max-width: 400px;
            border-radius: 8px;
            position: relative;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .modal h2 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 1.4em;
        }

        .form-group {
            margin-bottom: 20px;
            width: 100%;
            box-sizing: border-box;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4CAF50;
        }

        .submit-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            transition: background-color 0.3s;
        }

        .submit-button:hover {
            background-color: #45a049;
        }

        .close {
            position: absolute;
            right: 15px;
            top: 10px;
            color: #aaa;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: #333;
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

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .filter-label {
            color: #666;
            font-size: 0.9em;
            text-transform: uppercase;
        }

        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            font-size: 0.9em;
            color: #333;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 1em;
            min-width: 200px;
        }

        select:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 2px rgba(74, 175, 80, 0.2);
        }

        select:hover {
            border-color: #4CAF50;
        }

        select[multiple] {
            height: auto;
            padding: 0;
            background-image: none;
        }

        select[multiple] option {
            padding: 8px 10px;
        }

        select[multiple] option:checked {
            background: linear-gradient(0deg, #4CAF50 0%, #4CAF50 100%);
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content">
            <h1>Equipment</h1>
            
            <div class="filter-section">
                <div class="filter-group">
                    <label for="type-filter" class="filter-label">Equipment Type:</label>
                    <select id="type-filter">
                        <option value="">All Types</option>
                        <option value="Infantry Accessory">Infantry Accessory</option>
                        <option value="Body Armour">Body Armour</option>
                        <option value="Infantry Weapon">Infantry Weapon</option>
                        <option value="Battle Juice">Battle Juice</option>
                        <option value="Heavy Accessory">Heavy Accessory</option>
                        <option value="Crew">Crew</option>
                        <option value="Engine">Engine</option>
                        <option value="Ammunition">Ammunition</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="rarity-filter" class="filter-label">Rarity:</label>
                    <select id="rarity-filter">
                        <option value="">All Rarities</option>
                        <option value="Common">Common</option>
                        <option value="Uncommon">Uncommon</option>
                        <option value="Rare">Rare</option>
                        <option value="Epic">Epic</option>
                        <option value="Legendary">Legendary</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="equipped-filter" class="filter-label">Status:</label>
                    <select id="equipped-filter">
                        <option value="all">All</option>
                        <option value="equipped">Equipped</option>
                        <option value="unequipped">Unequipped</option>
                    </select>
                </div>
            </div>
            
            <div class="equipment-grid">
                <?php foreach ($equipment as $item): ?>
                    <?php include 'components/equipment_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>
    <div id="renameEquipmentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRenameEquipmentModal()">&times;</span>
            <h2>Rename Equipment</h2>
            <form id="renameEquipmentForm">
                <input type="hidden" id="equipmentIdForRename" name="equipmentIdForRename">
                <div class="form-group">
                    <label for="newEquipmentName">New Equipment Name:</label>
                    <input type="text" id="newEquipmentName" name="newEquipmentName" required maxlength="50" pattern="[^'&quot;]*" title="Quotation marks are not allowed">
                </div>
                <button type="submit" class="submit-button">Rename Equipment</button>
            </form>
        </div>
    </div>
    <script>
    function showRenameEquipmentModal(equipmentId, currentName) {
        const modal = document.getElementById('renameEquipmentModal');
        const equipmentIdInput = document.getElementById('equipmentIdForRename');
        const newNameInput = document.getElementById('newEquipmentName');
        
        equipmentIdInput.value = equipmentId;
        newNameInput.value = currentName;
        modal.style.display = "block";
    }

    function closeRenameEquipmentModal() {
        const modal = document.getElementById('renameEquipmentModal');
        modal.style.display = "none";
    }

    document.getElementById('renameEquipmentForm').onsubmit = function(e) {
        e.preventDefault();
        const equipmentId = document.getElementById('equipmentIdForRename').value;
        const newName = document.getElementById('newEquipmentName').value;
        
        if (newName.includes('"') || newName.includes("'")) {
            showToast("Equipment name cannot contain quotation marks", "error");
            return;
        }
        
        fetch('../backend/rename_equipment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `equipment_id=${equipmentId}&new_name=${encodeURIComponent(newName)}`
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
            showToast('An error occurred while renaming the equipment', "error");
        });
    };

    document.addEventListener('DOMContentLoaded', function() {
        const typeFilter = document.getElementById('type-filter');
        const rarityFilter = document.getElementById('rarity-filter');
        const equippedFilter = document.getElementById('equipped-filter');
        
        function applyFilters() {
            const selectedType = typeFilter.value;
            const selectedRarity = rarityFilter.value;
            const equippedStatus = equippedFilter.value;
            
            const equipmentCards = document.querySelectorAll('.equipment-card');
            
            equipmentCards.forEach(card => {
                const type = card.getAttribute('data-type');
                const rarity = card.getAttribute('data-rarity');
                const isEquipped = card.getAttribute('data-equipped') === 'true';
                
                let showCard = true;
                
                // Type filter
                if (selectedType && type !== selectedType) {
                    showCard = false;
                }
                
                // Rarity filter
                if (selectedRarity && rarity !== selectedRarity) {
                    showCard = false;
                }
                
                // Equipped status filter
                if (equippedStatus !== 'all') {
                    if (equippedStatus === 'equipped' && !isEquipped) {
                        showCard = false;
                    }
                    if (equippedStatus === 'unequipped' && isEquipped) {
                        showCard = false;
                    }
                }
                
                card.style.display = showCard ? '' : 'none';
            });
        }
        
        typeFilter.addEventListener('change', applyFilters);
        rarityFilter.addEventListener('change', applyFilters);
        equippedFilter.addEventListener('change', applyFilters);
    });
    </script>
</body>
</html>
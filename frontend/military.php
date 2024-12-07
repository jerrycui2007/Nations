<?php
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/unit_config.php';
require_once '../backend/building_config.php';
require_once '../backend/resource_config.php';
require_once 'helpers/resource_display.php';
require_once 'helpers/time_display.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's divisions
$stmt = $pdo->prepare("
    SELECT d.* 
    FROM divisions d 
    WHERE d.user_id = ? 
    ORDER BY d.name
");
$stmt->execute([$_SESSION['user_id']]);
$divisions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's units with their buffs
$stmt = $pdo->prepare("
    SELECT u.*, GROUP_CONCAT(b.description) as buff_descriptions 
    FROM units u 
    LEFT JOIN buffs b ON u.unit_id = b.unit_id 
    WHERE u.player_id = ? 
    GROUP BY u.unit_id
");
$stmt->execute([$_SESSION['user_id']]);
$units = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group units by division_id
$units_by_division = [];
foreach ($units as $unit) {
    $division_id = $unit['division_id'];
    if (!isset($units_by_division[$division_id])) {
        $units_by_division[$division_id] = [];
    }
    $units_by_division[$division_id][] = $unit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Military Overview - Nations</title>
    <link rel="stylesheet" type="text/css" href="design/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
            padding: 12px 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            height: auto;
            min-height: min-content;
            position: relative;
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

        .unit-content {
            flex: 1 0 auto;
            display: flex;
            flex-direction: column;
            height: auto;
            min-height: min-content;
        }

        .unit-name {
            font-size: 0.9em;
            margin-bottom: 15px;
            color: #666;
        }

        .unit-custom-name {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-weight: bold;
            gap: 8px;
            justify-content: flex-start;
        }

        .unit-level {
            font-size: 0.9em;
            color: #4CAF50;
            margin-bottom: 10px;
        }

        .unit-xp {
            font-size: 0.8em;
            color: #666;
            margin-bottom: 15px;
        }

        .unit-stat {
            margin-bottom: 4px;
            position: relative;
            height: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
            width: 100%;
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

        .stat-firepower { background-color: #ff4444; }
        .stat-armour { background-color: #4444ff; }
        .stat-maneuver { background-color: #ff9933; }
        .stat-hp { background-color: #44bb44; }
        .stat-hp-low { background-color: #ffc107; }
        .stat-hp-dead { background-color: #dc3545; }
        .stat-xp { background-color: #9933ff; }

        .unit-level-box {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #4CAF50;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .unit-stat.upkeep-stat {
            justify-content: center;
            width: 100%;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .unit-stat.upkeep-stat .unit-stat-label {
            color: #666;
            font-size: 0.8em;
            width: 35px;
            text-align: right;
            min-width: 35px;
            display: inline-block;
            margin-right: 8px;
        }

        .unit-stat.upkeep-stat .upkeep-content {
            width: 200px;  /* Match width of stat bars */
            text-align: left;
        }

        .division-section {
            margin-bottom: 40px;
        }

        .division-section h2 {
            color: #333;
            font-size: 1.5em;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        .no-units {
            color: #666;
            font-style: italic;
            padding: 20px;
            text-align: center;
            background: white;
            border-radius: 8px;
            border: 1px solid #ddd;
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

        .buff-item {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 4px;
            line-height: 1.4;
            word-wrap: break-word;
            padding-right: 10px;
        }

        .disband-button {
            width: 100%;
            padding: 8px;
            background-color: transparent;
            color: #dc3545;
            border: 1px solid #dc3545;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 15px;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }

        .disband-button:hover {
            background-color: #dc3545;
            color: white;
        }

        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .toast {
            background: white;
            border-radius: 4px;
            padding: 12px 24px;
            margin: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            font-size: 0.9em;
        }

        .toast.success {
            border-left: 4px solid #4CAF50;
        }

        .toast.error {
            border-left: 4px solid #dc3545;
        }

        .create-division-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
            font-size: 1em;
        }

        .create-division-button:hover {
            background-color: #45a049;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 25px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .submit-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        .submit-button:hover {
            background-color: #45a049;
        }

        .rename-division-btn {
            background: none;
            border: none;
            color: #4CAF50;
            cursor: pointer;
            font-size: 0.8em;
            margin-left: 10px;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .rename-division-btn:hover {
            background-color: rgba(74, 175, 80, 0.1);
        }

        .division-section h2 {
            display: flex;
            align-items: center;
        }

        .move-division-button {
            width: 100%;
            padding: 8px;
            background-color: transparent;
            color: #4CAF50;
            border: 1px solid #4CAF50;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 15px;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }

        .move-division-button:hover {
            background-color: #4CAF50;
            color: white;
        }

        .unit-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            font-size: 0.9em;
            color: #333;
            cursor: pointer;
            appearance: none; /* Removes default browser styling */
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 1em;
        }

        .form-group select:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 2px rgba(74, 175, 80, 0.2);
        }

        .form-group select:hover {
            border-color: #4CAF50;
        }

        .form-group select option {
            padding: 10px;
        }

        .form-group select option:disabled {
            color: #999;
            background-color: #f5f5f5;
            font-style: italic;
        }

        .modal h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 1.5em;
        }

        .move-division-button:disabled,
        .disband-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            border-color: #999;
            color: #999;
        }

        .move-division-button:disabled:hover,
        .disband-button:disabled:hover {
            background-color: transparent;
            color: #999;
        }

        .disband-division-btn {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 0.8em;
            margin-left: 10px;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .disband-division-btn:hover {
            background-color: rgba(220, 53, 69, 0.1);
        }

        .rename-unit-btn {
            background: none;
            border: none;
            color: #4CAF50;
            cursor: pointer;
            font-size: 0.8em;
            padding: 2px 5px;
            border-radius: 4px;
            transition: background-color 0.3s;
            margin-left: 5px;
            flex: 0 0 auto;
        }

        .rename-unit-btn:hover {
            background-color: rgba(74, 175, 80, 0.1);
        }

        .unit-name {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .unit-custom-name {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-weight: bold;
            gap: 8px;
            justify-content: flex-start;
        }

        .unit-name {
            margin-bottom: 10px;
            color: #666;
            font-size: 0.9em;
        }

        .rename-unit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            color: #999;
        }

        .rename-unit-btn:disabled:hover {
            background-color: transparent;
        }

        .peacekeeping-btn {
            background: none;
            border: none;
            color: #4CAF50;
            cursor: pointer;
            font-size: 0.8em;
            margin-left: 10px;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .peacekeeping-btn:hover {
            background-color: rgba(76, 175, 80, 0.1);
        }

        .combat-icon {
            color: #dc3545;
            text-decoration: none;
            margin-left: 8px;
        }

        .combat-icon:hover {
            color: #bb2d3b;
        }

        .fa-crossed-swords {
            color: #dc3545;
            margin-left: 8px;
        }

        .tab-container {
            margin-top: 20px;
        }

        .tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }

        .tab-button {
            padding: 10px 20px;
            border: 1px solid #dee2e6;
            border-bottom: none;
            background-color: #f8f9fa;
            cursor: pointer;
            border-radius: 5px 5px 0 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-button:hover {
            background-color: #e9ecef;
        }

        .tab-button.active {
            background-color: white;
            border-bottom: 2px solid #4CAF50;
            color: #4CAF50;
        }

        .tab-content {
            display: none;
            padding: 20px;
            background: white;
            border-radius: 0 0 5px 5px;
        }

        .tab-content.active {
            display: block;
        }

        .division-header {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .mobilization-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .mobilization-status {
            font-size: 0.9em;
            color: #666;
        }

        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .status-badge.demobilized {
            background-color: #dc3545;
            color: white;
        }

        .status-badge.mobilizing {
            background-color: #ffc107;
            color: black;
        }

        .status-badge.mobilized {
            background-color: #28a745;
            color: white;
        }

        .mobilize-btn, .demobilize-btn, .cancel-mobilize-btn {
            background: none;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8em;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .mobilize-btn {
            color: #28a745;
        }

        .mobilize-btn:hover {
            background-color: rgba(40, 167, 69, 0.1);
        }

        .demobilize-btn {
            color: #dc3545;
        }

        .demobilize-btn:hover {
            background-color: rgba(220, 53, 69, 0.1);
        }

        .cancel-mobilize-btn {
            color: #ffc107;
        }

        .cancel-mobilize-btn:hover {
            background-color: rgba(255, 193, 7, 0.1);
        }

        .unit-card-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .unit-card-link:hover {
            transform: translateY(-2px);
            transition: transform 0.2s ease;
        }

        .unit-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .unit-card-link:hover .unit-card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .unit-name-link {
            text-decoration: none;
            color: inherit;
            text-align: left;
            flex: 0 1 auto;
        }

        .unit-name-link:hover {
            text-decoration: underline;
            color: #4CAF50;
        }

        .unit-custom-name {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-weight: bold;
            gap: 8px;
            justify-content: flex-start;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content">
            <h1>Military Overview</h1>
            
            <button id="createDivisionBtn" class="create-division-button">Create New Division</button>

            <!-- Create Division Modal -->
            <div id="createDivisionModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Create New Division</h2>
                    <form id="createDivisionForm">
                        <div class="form-group">
                            <label for="divisionName">Division Name:</label>
                            <input type="text" id="divisionName" name="divisionName" required pattern="[^'&quot;]*" title="Quotation marks are not allowed">
                        </div>
                        <button type="submit" class="submit-button">Create Division</button>
                    </form>
                </div>
            </div>

            <!-- Rename Division Modal -->
            <div id="renameDivisionModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeRenameModal()">&times;</span>
                    <h2>Rename Division</h2>
                    <form id="renameDivisionForm">
                        <input type="hidden" id="divisionId" name="divisionId">
                        <div class="form-group">
                            <label for="newDivisionName">New Division Name:</label>
                            <input type="text" id="newDivisionName" name="newDivisionName" required maxlength="50" pattern="[^'&quot;]*" title="Quotation marks are not allowed">
                        </div>
                        <button type="submit" class="submit-button">Rename Division</button>
                    </form>
                </div>
            </div>

            <!-- Move Division Modal -->
            <div id="moveDivisionModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeMoveDivisionModal()">&times;</span>
                    <h2>Move Unit to Division</h2>
                    <form id="moveDivisionForm">
                        <input type="hidden" id="unitId" name="unitId">
                        <div class="form-group">
                            <label for="newDivisionId">Select Division:</label>
                            <select id="newDivisionId" name="newDivisionId" required>
                                <option value="0">Reserves</option>
                                <?php foreach ($divisions as $division): ?>
                                    <?php 
                                    $unit_count = isset($units_by_division[$division['division_id']]) 
                                        ? count($units_by_division[$division['division_id']]) 
                                        : 0;
                                    $disabled = $unit_count >= 15 ? 'disabled' : '';
                                    ?>
                                    <option value="<?php echo $division['division_id']; ?>" <?php echo $disabled; ?>>
                                        <?php echo htmlspecialchars($division['name']); ?> 
                                        (<?php echo $unit_count; ?>/15 units)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="submit-button">Move Unit</button>
                    </form>
                </div>
            </div>

            <!-- Rename Unit Modal -->
            <div id="renameUnitModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeRenameUnitModal()">&times;</span>
                    <h2>Rename Unit</h2>
                    <form id="renameUnitForm">
                        <input type="hidden" id="unitIdForRename" name="unitIdForRename">
                        <div class="form-group">
                            <label for="newUnitName">New Unit Name:</label>
                            <input type="text" id="newUnitName" name="newUnitName" required maxlength="50" pattern="[^'&quot;]*" title="Quotation marks are not allowed">
                        </div>
                        <button type="submit" class="submit-button">Rename Unit</button>
                    </form>
                </div>
            </div>

            <div class="tab-container">
                <div class="tabs">
                    <?php foreach ($divisions as $index => $division): ?>
                        <button class="tab-button" 
                                onclick="openTab(event, 'division-<?php echo $division['division_id']; ?>')"
                                data-tab-id="division-<?php echo $division['division_id']; ?>">
                            <?php if ($division['is_defence']): ?>
                                <i class="fas fa-shield-alt" style="color: #4444ff;" title="Defensive Division"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($division['name']); ?>
                            <?php if ($division['in_combat']): ?>
                                <i class="fa-solid fa-crosshairs" style="color: #dc3545;" title="Division in Combat"></i>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                    <button class="tab-button" 
                            onclick="openTab(event, 'reserves')"
                            data-tab-id="reserves">Reserves</button>
                </div>

                <?php foreach ($divisions as $index => $division): ?>
                    <div id="division-<?php echo $division['division_id']; ?>" 
                         class="tab-content">
                        <div class="division-header">
                            <button class="rename-division-btn" 
                                    onclick="showRenameModal(<?php echo $division['division_id']; ?>, '<?php echo addslashes($division['name']); ?>')">
                                <i class="fas fa-edit"></i> Rename
                            </button>
                            <?php if (!$division['is_defence'] && !$division['in_combat']): ?>
                                <?php 
                                $unit_count = isset($units_by_division[$division['division_id']]) 
                                    ? count($units_by_division[$division['division_id']]) 
                                    : 0;
                                ?>
                                <button class="disband-division-btn" 
                                        onclick="disbandDivision(<?php echo $division['division_id']; ?>, '<?php echo addslashes($division['name']); ?>')">
                                    <i class="fas fa-trash-alt"></i> Disband
                                </button>
                                <?php if ($division['mobilization_state'] === 'mobilized'): ?>
                                    <button class="peacekeeping-btn" 
                                            onclick="sendPeacekeeping(<?php echo $division['division_id']; ?>, '<?php echo addslashes($division['name']); ?>')"
                                            <?php echo $unit_count === 0 ? 'disabled title="Cannot send empty division"' : ''; ?>>
                                        <i class="fas fa-dove"></i> Send on Peacekeeping Mission
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                            <div class="mobilization-controls">
                                <div class="mobilization-status">
                                    Status: 
                                    <span class="status-badge <?php echo $division['mobilization_state']; ?>">
                                        <?php echo ucfirst($division['mobilization_state']); ?>
                                    </span>
                                </div>
                                <?php if ($division['mobilization_state'] === 'demobilized'): ?>
                                    <button class="mobilize-btn" onclick="toggleMobilization(<?php echo $division['division_id']; ?>, 'mobilizing')">
                                        <i class="fas fa-play"></i> Mobilize
                                    </button>
                                <?php elseif ($division['mobilization_state'] === 'mobilizing'): ?>
                                    <button class="cancel-mobilize-btn" onclick="toggleMobilization(<?php echo $division['division_id']; ?>, 'demobilized')">
                                        <i class="fas fa-stop"></i> Cancel
                                    </button>
                                <?php else: ?>
                                    <?php if (!$division['is_defence']): ?>
                                        <button class="demobilize-btn" onclick="toggleMobilization(<?php echo $division['division_id']; ?>, 'demobilized')">
                                            <i class="fas fa-power-off"></i> Demobilize
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="unit-grid">
                            <?php 
                            if (isset($units_by_division[$division['division_id']])) {
                                foreach ($units_by_division[$division['division_id']] as $unit) {
                                    include 'components/unit_card.php';
                                }
                            } else {
                                echo "<p class='no-units'>No units assigned to this division</p>";
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div id="reserves" class="tab-content">
                    <div class="unit-grid">
                        <?php 
                        if (isset($units_by_division[0])) {
                            foreach ($units_by_division[0] as $unit) {
                                include 'components/unit_card.php';
                            }
                        } else {
                            echo "<p class='no-units'>No units in reserves</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>
    <script>
        function disbandUnit(unitId, unitName) {
            if (!confirm(`Are you sure you want to disband ${unitName}?`)) {
                return;
            }

            fetch('../backend/disband_unit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `unit_id=${encodeURIComponent(unitId)}`
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
                showToast('An error occurred while disbanding the unit', "error");
            });
        }

        function showToast(message, type = "success") {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            
            const container = document.querySelector('.toast-container');
            if (!container) {
                const newContainer = document.createElement('div');
                newContainer.className = 'toast-container';
                document.body.appendChild(newContainer);
                newContainer.appendChild(toast);
            } else {
                container.appendChild(toast);
            }
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Check for stored toast messages when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            const message = localStorage.getItem('toastMessage');
            const type = localStorage.getItem('toastType');
            
            if (message) {
                showToast(message, type);
                localStorage.removeItem('toastMessage');
                localStorage.removeItem('toastType');
            }
        });

        // Modal functionality
        const modal = document.getElementById('createDivisionModal');
        const btn = document.getElementById('createDivisionBtn');
        const span = document.getElementsByClassName('close')[0];
        const form = document.getElementById('createDivisionForm');

        btn.onclick = function() {
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            const renameModal = document.getElementById('renameDivisionModal');
            const createModal = document.getElementById('createDivisionModal');
            const renameUnitModal = document.getElementById('renameUnitModal');
            const moveDivisionModal = document.getElementById('moveDivisionModal');
            
            if (event.target == renameModal) {
                renameModal.style.display = "none";
            }
            if (event.target == createModal) {
                createModal.style.display = "none";
            }
            if (event.target == renameUnitModal) {
                renameUnitModal.style.display = "none";
            }
            if (event.target == moveDivisionModal) {
                moveDivisionModal.style.display = "none";
            }
        };

        form.onsubmit = function(e) {
            e.preventDefault();
            const divisionName = document.getElementById('divisionName').value;
            
            if (divisionName.includes('"') || divisionName.includes("'")) {
                showToast("Division name cannot contain quotation marks", "error");
                return;
            }
            
            fetch('../backend/create_division.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `name=${encodeURIComponent(divisionName)}`
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
                showToast('An error occurred while creating the division', "error");
            });
        }

        function showRenameModal(divisionId, currentName) {
            const modal = document.getElementById('renameDivisionModal');
            const divisionIdInput = document.getElementById('divisionId');
            const newNameInput = document.getElementById('newDivisionName');
            
            divisionIdInput.value = divisionId;
            newNameInput.value = currentName;
            modal.style.display = "block";
        }

        function closeRenameModal() {
            const modal = document.getElementById('renameDivisionModal');
            modal.style.display = "none";
        }

        document.getElementById('renameDivisionForm').onsubmit = function(e) {
            e.preventDefault();
            const divisionId = document.getElementById('divisionId').value;
            const newName = document.getElementById('newDivisionName').value;
            
            if (newName.includes('"') || newName.includes("'")) {
                showToast("Division name cannot contain quotation marks", "error");
                return;
            }
            
            fetch('../backend/rename_division.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `division_id=${divisionId}&new_name=${encodeURIComponent(newName)}`
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
                showToast('An error occurred while renaming the division', "error");
            });
        };

        function showMoveDivisionModal(unitId, unitName, unitType) {
            const modal = document.getElementById('moveDivisionModal');
            const unitIdInput = document.getElementById('unitId');
            
            unitIdInput.value = unitId;
            modal.style.display = "block";
        }

        function closeMoveDivisionModal() {
            const modal = document.getElementById('moveDivisionModal');
            modal.style.display = "none";
        }

        document.getElementById('moveDivisionForm').onsubmit = function(e) {
            e.preventDefault();
            const unitId = document.getElementById('unitId').value;
            const newDivisionId = document.getElementById('newDivisionId').value;
            
            fetch('../backend/move_unit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `unit_id=${unitId}&division_id=${newDivisionId}`
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
                showToast('An error occurred while moving the unit', "error");
            });
        };

        function disbandDivision(divisionId, divisionName) {
            if (!confirm(`Are you sure you want to disband ${divisionName}? All units will be moved to reserves.`)) {
                return;
            }

            fetch('../backend/disband_division.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `division_id=${divisionId}`
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
                showToast('An error occurred while disbanding the division', "error");
            });
        }

        function showRenameUnitModal(unitId, currentName) {
            console.log('showRenameUnitModal called with:', {
                unitId: unitId,
                currentName: currentName,
                typeofCurrentName: typeof currentName
            });

            try {
                const modal = document.getElementById('renameUnitModal');
                console.log('Modal element:', modal);

                const unitIdInput = document.getElementById('unitIdForRename');
                console.log('Unit ID input element:', unitIdInput);

                const newNameInput = document.getElementById('newUnitName');
                console.log('New name input element:', newNameInput);
                
                if (!modal || !unitIdInput || !newNameInput) {
                    console.error('One or more required elements not found');
                    return;
                }

                unitIdInput.value = unitId;
                newNameInput.value = currentName;
                modal.style.display = "block";
                
                console.log('Modal displayed successfully');
            } catch (error) {
                console.error('Error in showRenameUnitModal:', error);
            }
        }

        function closeRenameUnitModal() {
            const modal = document.getElementById('renameUnitModal');
            modal.style.display = "none";
        }

        document.getElementById('renameUnitForm').onsubmit = function(e) {
            e.preventDefault();
            const unitId = document.getElementById('unitIdForRename').value;
            const newName = document.getElementById('newUnitName').value;
            
            if (newName.includes('"') || newName.includes("'")) {
                showToast("Unit name cannot contain quotation marks", "error");
                return;
            }
            
            fetch('../backend/rename_unit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `unit_id=${encodeURIComponent(unitId)}&new_name=${encodeURIComponent(newName)}`
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
                showToast('An error occurred while renaming the unit', "error");
            });
        };

        function sendPeacekeeping(divisionId, divisionName) {
            if (!confirm(`Are you sure you want to send ${divisionName} on a peacekeeping mission?`)) {
                return;
            }

            fetch('../backend/send_peacekeeping.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `division_id=${divisionId}`
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
                showToast('An error occurred while sending the division on peacekeeping', "error");
            });
        }

        // Check for stored toast messages when the page loads
        document.addEventListener('DOMContentLoaded', () => {
            const message = localStorage.getItem('toastMessage');
            const type = localStorage.getItem('toastType');
            
            if (message) {
                showToast(message, type);
                localStorage.removeItem('toastMessage');
                localStorage.removeItem('toastType');
            }
        });

        function openTab(evt, tabName) {
            let i, tabcontent, tablinks;
            
            // Hide all tab content
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            
            // Remove active class from all tab buttons
            tablinks = document.getElementsByClassName("tab-button");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            
            // Show the selected tab content and mark the button as active
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
            
            // Store the current tab in localStorage
            localStorage.setItem('currentMilitaryTab', tabName);
        }

        // Add this new function to restore the tab state
        document.addEventListener('DOMContentLoaded', function() {
            // First handle any toast messages
            const message = localStorage.getItem('toastMessage');
            const type = localStorage.getItem('toastType');
            if (message) {
                showToast(message, type);
                localStorage.removeItem('toastMessage');
                localStorage.removeItem('toastType');
            }
            
            // Then restore the tab state
            const savedTab = localStorage.getItem('currentMilitaryTab');
            if (savedTab) {
                const tabButton = document.querySelector(`[data-tab-id="${savedTab}"]`);
                if (tabButton) {
                    // Simulate a click on the saved tab
                    tabButton.click();
                } else {
                    // If the saved tab no longer exists, default to the first tab
                    const firstTab = document.querySelector('.tab-button');
                    if (firstTab) firstTab.click();
                }
            } else {
                // If no saved tab, default to the first tab
                const firstTab = document.querySelector('.tab-button');
                if (firstTab) firstTab.click();
            }
        });

        function toggleMobilization(divisionId, newState) {
            fetch('../backend/toggle_mobilization.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `division_id=${divisionId}&new_state=${newState}`
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
                showToast('An error occurred while updating mobilization state', "error");
            });
        }
    </script>
    <div class="toast-container"></div>
</body>
</html> 
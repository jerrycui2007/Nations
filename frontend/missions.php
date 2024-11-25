<?php
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/mission_config.php';
require_once '../backend/unit_config.php';
require_once '../backend/resource_config.php';
require_once 'helpers/resource_display.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's missions
$stmt = $pdo->prepare("SELECT * FROM missions WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Missions - Nations</title>
    <link rel="stylesheet" href="design/style.css">
    <style>
        .main-content {
            margin-left: 220px;
            padding: 15px;
        }

        .content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .mission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .mission-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .mission-header {
            padding: 15px 20px;
            margin-bottom: 0;
        }

        .rarity-common .mission-header {
            background: transparent;
        }

        .rarity-uncommon .mission-header {
            background: #2a9d38;
            color: white;
        }

        .rarity-rare .mission-header {
            background: #0066cc;
            color: white;
        }

        .rarity-epic .mission-header {
            background: #6a1b9a;
            color: white;
        }

        .rarity-legendary .mission-header {
            background: #e65100;
            color: white;
        }

        .mission-name {
            font-size: 1.2em;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .mission-description {
            color: #666;
            margin: 10px 0;
            line-height: 1.4;
        }

        .mission-details {
            margin-bottom: 15px;
        }

        .mission-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            color: #555;
        }


        .start-mission-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            transition: background-color 0.2s;
        }

        .start-mission-btn:hover {
            background-color: #45a049;
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

        .mission-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
            margin: 0px 0;
        }

        .status-incomplete {
            background-color: #ffeeba;
            color: #856404;
        }

        .status-complete {
            background-color: #d4edda;
            color: #155724;
        }

        .status-in_progress {
            background-color: #fff3cd;
            color: #856404;
        }

        .mission-rarity {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 0px;
        }

        .enemy-units {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .enemy-units h3 {
            margin: 0 0 15px 0;
            color: #2c3e50;
        }

        .unit-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .unit-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }

        .unit-list-item:last-child {
            border-bottom: none;
        }

        .spawn-rate, .spawn-amount {
            font-size: 0.9em;
            color: #666;
        }

        .unit-stats {
            display: flex;
            gap: 5px;
        }

        .stat-box {
            width: 25px;
            height: 25px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
            font-weight: bold;
            color: white;
            text-shadow: 0 0 2px rgba(0,0,0,0.5);
        }

        .stat-firepower { background-color: #dc3545; }
        .stat-armour { background-color: #0d6efd; }
        .stat-maneuver { background-color: #fd7e14; }
        .stat-hp { background-color: #44bb44; }

        .rarity-common .mission-name {
            color: #2c3e50;
        }

        .rarity-uncommon .mission-name,
        .rarity-rare .mission-name,
        .rarity-epic .mission-name,
        .rarity-legendary .mission-name {
            color: white;
        }

        .rarity-common .mission-rarity {
            color: #666;
        }

        .rarity-uncommon .mission-rarity,
        .rarity-rare .mission-rarity,
        .rarity-epic .mission-rarity,
        .rarity-legendary .mission-rarity {
            color: rgba(255, 255, 255, 0.9);
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
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            position: relative;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            right: 20px;
            top: 10px;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .submit-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 10px;
        }

        .submit-button:hover {
            background-color: #45a049;
        }

        .claim-rewards-btn {
            background-color: #ffc107;
            color: #000;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            transition: background-color 0.2s;
        }

        .claim-rewards-btn:hover {
            background-color: #e0a800;
        }

        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .toast {
            padding: 12px 24px;
            margin-bottom: 10px;
            border-radius: 4px;
            color: white;
            min-width: 200px;
            max-width: 400px;
            animation: slideIn 0.5s ease-in-out;
        }

        .toast.success {
            background-color: #28a745;
        }

        .toast.error {
            background-color: #dc3545;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .mission-content {
            display: flex;
            flex-direction: column;
            flex: 1;
            padding: 15px;
        }

        .mission-actions {
            margin-top: auto;
            padding-top: 15px;
        }

        .start-mission-btn,
        .claim-rewards-btn {
            width: 100%;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content">
            <h1>Missions</h1>
            
            <div class="mission-grid">
                <?php foreach ($user_missions as $mission): ?>
                    <?php 
                    $mission_config = $MISSION_CONFIG[$mission['mission_type']] ?? null;
                    if (!$mission_config) continue;
                    ?>
                    <div class="mission-card rarity-<?php echo strtolower($mission_config['rarity']); ?>">
                        <div class="mission-header">
                            <div class="mission-name"><?php echo htmlspecialchars($mission_config['name']); ?></div>
                            <div class="mission-rarity">Rarity: <?php echo htmlspecialchars($mission_config['rarity']); ?></div>
                        </div>
                        
                        <div class="mission-content">
                            <div class="mission-status status-<?php echo htmlspecialchars($mission['status']); ?>">
                                Status: <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($mission['status']))); ?>
                            </div>
                            <div class="mission-description">
                                <?php echo htmlspecialchars($mission_config['description']); ?>
                            </div>
                            <div class="mission-details">
                                <div class="mission-detail">
                                    <span>Location:</span>
                                    <span><?php echo ucfirst(htmlspecialchars($mission_config['continent'])); ?></span>
                                </div>
                                <div class="mission-detail">
                                    <span>Rewards:</span>
                                    <span>
                                        <?php 
                                        $reward_strings = [];
                                        foreach ($mission_config['rewards'] as $reward) {
                                            $reward_strings[] = getResourceIcon($reward['resource']) . formatNumber($reward['amount']);
                                        }
                                        echo implode(' ', $reward_strings);
                                        ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Enemy Units Table -->
                            <div class="enemy-units">
                                <h3>Enemy Forces</h3>
                                <ul class="unit-list">
                                    <?php foreach ($mission_config['enemies'] as $enemy_type => $enemy_data): ?>
                                        <?php 
                                        $unit_config = $UNIT_CONFIG[$enemy_type] ?? null;
                                        if (!$unit_config) continue;
                                        ?>
                                        <li class="unit-list-item">
                                            <div class="unit-info">
                                                <div class="unit-name"><?php echo htmlspecialchars($unit_config['name']); ?></div>
                                                <div class="spawn-rate">Spawn Rate: <?php echo $enemy_data['weight']; ?>%</div>
                                                <div class="spawn-amount">Amount: <?php echo $enemy_data['min_amount']; ?>-<?php echo $enemy_data['max_amount']; ?></div>
                                            </div>
                                            <div class="unit-stats">
                                                <div class="stat-box stat-firepower"><?php echo $unit_config['firepower']; ?></div>
                                                <div class="stat-box stat-armour"><?php echo $unit_config['armour']; ?></div>
                                                <div class="stat-box stat-maneuver"><?php echo $unit_config['maneuver']; ?></div>
                                                <div class="stat-box stat-hp"><?php echo $unit_config['hp']; ?></div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div class="mission-actions">
                                <?php if ($mission['status'] === 'incomplete'): ?>
                                    <button class="start-mission-btn" onclick="showMissionModal(<?php echo $mission['mission_id']; ?>, '<?php echo $mission['mission_type']; ?>')">
                                        Start Mission
                                    </button>
                                <?php endif; ?>
                                <?php if ($mission['status'] === 'Complete'): ?>
                                    <button class="claim-rewards-btn" onclick="claimRewards(<?php echo $mission['mission_id']; ?>, '<?php echo $mission['mission_type']; ?>')">
                                        Claim Rewards
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($user_missions)): ?>
                    <p>No missions available at this time.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <!-- Mission Division Selection Modal -->
    <div id="missionDivisionModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeMissionModal()">&times;</span>
            <h2>Select Division for Mission</h2>
            <form id="startMissionForm">
                <input type="hidden" id="missionId" name="missionId">
                <input type="hidden" id="missionType" name="missionType">
                <div class="form-group">
                    <label for="divisionId">Select Division:</label>
                    <select id="divisionId" name="divisionId" required>
                        <?php
                        // Fetch available divisions
                        $stmt = $pdo->prepare("
                            SELECT d.division_id, d.name, d.in_combat, d.is_defence,
                                   COUNT(u.unit_id) as unit_count
                            FROM divisions d 
                            LEFT JOIN units u ON d.division_id = u.division_id
                            WHERE d.user_id = ?
                            GROUP BY d.division_id
                            ORDER BY d.name
                        ");
                        $stmt->execute([$_SESSION['user_id']]);
                        $divisions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($divisions as $division):
                            if (!$division['is_defence'] && !$division['in_combat'] && $division['unit_count'] > 0):
                        ?>
                            <option value="<?php echo $division['division_id']; ?>">
                                <?php echo htmlspecialchars($division['name']); ?> 
                                (<?php echo $division['unit_count']; ?>/15 units)
                            </option>
                        <?php 
                            endif;
                        endforeach;
                        ?>
                    </select>
                </div>
                <button type="submit" class="submit-button">Start Mission</button>
            </form>
        </div>
    </div>

    <div class="toast-container"></div>

    <script>
        function showMissionModal(missionId, missionType) {
            const modal = document.getElementById('missionDivisionModal');
            document.getElementById('missionId').value = missionId;
            document.getElementById('missionType').value = missionType;
            modal.style.display = "block";
        }

        function closeMissionModal() {
            const modal = document.getElementById('missionDivisionModal');
            modal.style.display = "none";
        }

        document.getElementById('startMissionForm').onsubmit = function(e) {
            e.preventDefault();
            const missionId = document.getElementById('missionId').value;
            const missionType = document.getElementById('missionType').value;
            const divisionId = document.getElementById('divisionId').value;
            
            fetch('../backend/start_mission.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `mission_id=${missionId}&mission_type=${missionType}&division_id=${divisionId}`
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
                showToast('An error occurred while starting the mission', "error");
            });
        };

        function claimRewards(missionId, missionType) {
            fetch('../backend/claim_mission_rewards.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `mission_id=${missionId}&mission_type=${missionType}`
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
                showToast('An error occurred while claiming rewards', "error");
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

        // Check for stored toast message on page load
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
<?php
session_start();
require_once '../backend/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get battle ID from URL parameter
$battle_id = isset($_GET['battle_id']) ? intval($_GET['battle_id']) : 0;

if ($battle_id === 0) {
    header("Location: home.php");
    exit();
}

// Fetch battle data
$stmt = $pdo->prepare("SELECT *, 
    CASE 
        WHEN is_over = 1 THEN winner_name
        ELSE NULL 
    END as winner 
    FROM battles WHERE battle_id = ?");
$stmt->execute([$battle_id]);
$battle = $stmt->fetch(PDO::FETCH_ASSOC);

// If battle doesn't exist, redirect to home
if (!$battle) {
    header("Location: home.php");
    exit();
}

// Fetch defending division and its units
$stmt = $pdo->prepare("
    SELECT d.*, u.name as unit_name, u.custom_name, u.unit_id, 
           u.level, u.firepower, u.armour, u.maneuver, u.hp,
           u.max_hp
    FROM divisions d
    JOIN units u ON d.division_id = u.division_id
    WHERE d.division_id = ?
");
$stmt->execute([$battle['defender_division_id']]);
$defending_units = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch attacking division and its units
$stmt = $pdo->prepare("
    SELECT d.*, u.name as unit_name, u.custom_name, u.unit_id, 
           u.level, u.firepower, u.armour, u.maneuver, u.hp,
           u.max_hp
    FROM divisions d
    JOIN units u ON d.division_id = u.division_id
    WHERE d.division_id = ?
");
$stmt->execute([$battle['attacker_division_id']]);
$attacking_units = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch combat reports for this battle
$stmt = $pdo->prepare("
    SELECT time, message 
    FROM combat_reports 
    WHERE battle_id = ? 
    ORDER BY time DESC
");
$stmt->execute([$battle_id]);
$combat_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate division strength
function calculateDivisionStrength($units) {
    $total_firepower = 0;
    $total_armour = 0;
    $total_maneuver = 0;
    $total_hp = 0;
    
    foreach ($units as $unit) {
        if ($unit['hp'] > 0) {  // Only count stats for living units
            $total_firepower += $unit['firepower'];
            $total_armour += $unit['armour'];
            $total_maneuver += $unit['maneuver'];
            $total_hp += floor($unit['hp'] / 10);
        }
    }
    
    return $total_firepower + $total_armour + $total_maneuver + $total_hp;
}

// Calculate strengths
$defender_strength = calculateDivisionStrength($defending_units);
$attacker_strength = calculateDivisionStrength($attacking_units);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($battle['battle_name']); ?> - Nations</title>
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
            background: url('resources/<?php echo $battle['continent'] ? $battle['continent'] : 'default'; ?>.png') no-repeat center center;
            background-size: cover;
            padding: 150px 20px;
            color: white;
            position: relative;
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

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.7);
        }

        .header-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-right: 0px;
        }

        .header-right {
            flex: 1;
            padding-left: 20px;
            border-left: 2px solid rgba(255, 255, 255, 0.5);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .info-group {
            margin-bottom: 20px;
            text-align: center;
        }

        .info-label {
            font-size: 0.9em;
            opacity: 0.8;
            margin-bottom: 5px;
            text-align: center;
        }

        .info-value {
            font-size: 1.8em;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .info-value-simple {
            padding-left: 20px;
        }

        .battle-name {
            font-size: 2.5em;
            font-weight: bold;
            text-align: center;
        }

        .battle-sides {
            display: flex;
            justify-content: space-between;
            margin: 20px;
            align-items: flex-start;
            height: auto;
        }

        .battle-side {
            flex: 0 0 25%;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: fit-content;
            min-height: min-content;
        }

        .side-title {
            background-color: #2c71f2;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            margin: -20px -20px 20px -20px;
            font-weight: bold;
            font-size: 1.2em;
        }

        .side-title.attacker {
            background-color: #dc3545;
        }

        .unit-list {
            list-style: none;
            padding: 0;
            margin: 0;
            height: auto;
        }

        .unit-list-item {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            text-align: left;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .unit-info {
            flex: 1;
        }

        .unit-stats {
            display: flex;
            gap: 5px;
            margin-left: 10px;
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

        .stat-level { background-color: #ffc107; }    /* Yellow */
        .stat-firepower { background-color: #dc3545; } /* Red */
        .stat-armour { background-color: #0d6efd; }    /* Blue */
        .stat-maneuver { background-color: #fd7e14; }  /* Orange */
        .stat-hp { background-color: #44bb44; }        /* Green (Default) */
        .stat-hp-low { background-color: #ffc107; }    /* Yellow */
        .stat-hp-dead { background-color: #dc3545; }   /* Red */

        .unit-custom-name {
            font-size: 1em;
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }

        .unit-name {
            font-size: 0.8em;
            color: #666;
        }

        .combat-reports {
            flex: 0 0 30%;
            margin: 0 20px;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: fit-content;
            min-height: min-content;
        }

        .combat-reports h2 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 1.4em;
        }

        .report-list {
            /* Remove these properties:
            max-height: 600px;
            overflow-y: auto; */
        }

        .report-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }

        .report-item:hover {
            background-color: #f8f9fa;
        }

        .report-time {
            font-size: 0.8em;
            color: #666;
            margin-bottom: 5px;
        }

        .report-message {
            color: #333;
            line-height: 1.4;
        }

        .no-reports {
            color: #666;
            text-align: center;
            padding: 20px;
            font-style: italic;
        }

        .reports-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .reports-header h2 {
            margin: 0;
            color: #333;
            font-size: 1.4em;
        }

        .loading-spinner {
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .battle-status {
            background-color: #e9ecef;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
            color: #495057;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .division-strength {
            background-color: #f8f9fa;
            padding: 8px 12px;
            border-radius: 4px;
            margin: 10px 0;
            font-size: 0.9em;
            color: #495057;
            text-align: center;
            border: 1px solid #dee2e6;
        }

        .unit-stat-bar.stat-hp {
            transition: width 0.3s ease-in-out;
        }

        .strength-bar {
            width: 100%;
            height: 20px;
            display: flex;
            margin-bottom: 20px;
        }

        .defender-strength {
            height: 100%;
            background-color: #0d6efd;  /* Blue */
            transition: width 0.3s ease-in-out;
        }

        .attacker-strength {
            height: 100%;
            background-color: #dc3545;  /* Red */
            transition: width 0.3s ease-in-out;
        }

        .unit-link {
            color: #333;
            text-decoration: none;
            transition: color 0.2s;
        }

        .unit-link:hover {
            color: #0d6efd;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <div class="header-content">
                <div class="header-left">
                    <div class="battle-name"><?php echo htmlspecialchars($battle['battle_name']); ?></div>
                </div>
            </div>
        </div>

        <?php 
        $total_strength = $defender_strength + $attacker_strength;
        $defender_percentage = ($total_strength > 0) ? ($defender_strength / $total_strength) * 100 : 50;
        ?>
        <div class="strength-bar">
            <div class="defender-strength" style="width: <?php echo $defender_percentage; ?>%"></div>
            <div class="attacker-strength" style="width: <?php echo (100 - $defender_percentage); ?>%"></div>
        </div>

        <div class="content">
            <div class="battle-sides">
                <div class="battle-side">
                    <div class="side-title"><?php echo htmlspecialchars($battle['defender_name']); ?></div>
                    <div class="division-strength defender-strength-value">
                        Division Strength: <?php echo number_format($defender_strength); ?>
                    </div>
                    <ul class="unit-list defender-units">
                        <?php foreach ($defending_units as $unit): ?>
                            <li class="unit-list-item" data-unit-id="<?php echo $unit['unit_id']; ?>">
                                <div class="unit-info">
                                    <div class="unit-custom-name">
                                        <a href="unit_view.php?unit_id=<?php echo $unit['unit_id']; ?>" class="unit-link" target="_blank">
                                            <?php echo htmlspecialchars($unit['custom_name']); ?>
                                        </a>
                                    </div>
                                    <div class="unit-name">
                                        <?php echo htmlspecialchars($unit['unit_name']); ?>
                                    </div>
                                </div>
                                <div class="unit-stats">
                                    <div class="stat-box stat-level"><?php echo $unit['level']; ?></div>
                                    <div class="stat-box stat-firepower"><?php echo $unit['firepower']; ?></div>
                                    <div class="stat-box stat-armour"><?php echo $unit['armour']; ?></div>
                                    <div class="stat-box stat-maneuver"><?php echo $unit['maneuver']; ?></div>
                                    <div class="stat-box stat-hp <?php 
                                        if ($unit['hp'] <= 0) {
                                            echo 'stat-hp-dead';
                                        } elseif ($unit['hp'] < $unit['max_hp'] / 2) {
                                            echo 'stat-hp-low';
                                        }
                                    ?>"><?php echo $unit['hp']; ?></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="combat-reports">
                    <div class="reports-header">
                        <h2>Battle Reports</h2>
                        <?php if (!$battle['is_over']): ?>
                            <div class="loading-spinner"></div>
                        <?php else: ?>
                            <div class="battle-status">
                                Battle Finished - <?php echo htmlspecialchars($battle['winner']); ?> Victory
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (empty($combat_reports)): ?>
                        <p class="no-reports">No battle reports yet</p>
                    <?php else: ?>
                        <div class="report-list">
                            <?php foreach ($combat_reports as $report): ?>
                                <div class="report-item">
                                    <div class="report-time">
                                        <?php 
                                        $report_time = new DateTime($report['time']);
                                        echo $report_time->format('M j, Y H:i:s'); 
                                        ?>
                                    </div>
                                    <div class="report-message">
                                        <?php 
                                        // Highlight numbers in red using regex
                                        echo preg_replace(
                                            '/(\d+)/', 
                                            '<span class="number-highlight">$1</span>', 
                                            htmlspecialchars($report['message'])
                                        ); 
                                        ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="battle-side">
                    <div class="side-title attacker"><?php echo htmlspecialchars($battle['attacker_name']); ?></div>
                    <div class="division-strength attacker-strength-value">
                        Division Strength: <?php echo number_format($attacker_strength); ?>
                    </div>
                    <ul class="unit-list attacker-units">
                        <?php foreach ($attacking_units as $unit): ?>
                            <li class="unit-list-item" data-unit-id="<?php echo $unit['unit_id']; ?>">
                                <div class="unit-info">
                                    <div class="unit-custom-name">
                                        <a href="unit_view.php?unit_id=<?php echo $unit['unit_id']; ?>" class="unit-link" target="_blank">
                                            <?php echo htmlspecialchars($unit['custom_name']); ?>
                                        </a>
                                    </div>
                                    <div class="unit-name">
                                        <?php echo htmlspecialchars($unit['unit_name']); ?>
                                    </div>
                                </div>
                                <div class="unit-stats">
                                    <div class="stat-box stat-level"><?php echo $unit['level']; ?></div>
                                    <div class="stat-box stat-firepower"><?php echo $unit['firepower']; ?></div>
                                    <div class="stat-box stat-armour"><?php echo $unit['armour']; ?></div>
                                    <div class="stat-box stat-maneuver"><?php echo $unit['maneuver']; ?></div>
                                    <div class="stat-box stat-hp <?php 
                                        if ($unit['hp'] <= 0) {
                                            echo 'stat-hp-dead';
                                        } elseif ($unit['hp'] < $unit['max_hp'] / 2) {
                                            echo 'stat-hp-low';
                                        }
                                    ?>"><?php echo $unit['hp']; ?></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script>
        const battleId = <?php echo $battle_id; ?>;
        const userId = <?php echo $_SESSION['user_id']; ?>;
        
        let page = 1;
        const reportsPerPage = 20;
        let loading = false;
        let hasMore = true;
        
        // Function to load more reports
        async function loadMoreReports() {
            if (loading || !hasMore) return;
            
            loading = true;
            const battleId = <?php echo $battle_id; ?>;
            
            try {
                const response = await fetch(`get_combat_reports.php?battle_id=${battleId}&page=${page}&per_page=${reportsPerPage}`);
                const data = await response.json();
                
                if (data.reports.length < reportsPerPage) {
                    hasMore = false;
                }
                
                if (data.reports.length > 0) {
                    const reportList = document.querySelector('.report-list');
                    data.reports.forEach(report => {
                        const reportElement = document.createElement('div');
                        reportElement.className = 'report-item';
                        reportElement.innerHTML = `
                            <div class="report-time">${new Date(report.time).toLocaleString()}</div>
                            <div class="report-message">
                                ${report.message.replace(/(\d+)/g, '<span class="number-highlight">$1</span>')}
                            </div>
                        `;
                        reportList.appendChild(reportElement);
                    });
                }
            } catch (error) {
                console.error('Error loading more reports:', error);
            } finally {
                loading = false;
            }
        }
    </script>
    <script>
    function requestNotificationPermission() {
        if (!("Notification" in window)) {
            console.log("This browser does not support desktop notifications");
            return;
        }
        
        Notification.requestPermission();
    }

    function sendBattleNotification(title, message) {
        if (Notification.permission === "granted") {
            new Notification(title, {
                body: message,
                icon: '/favicon.ico' // Add your favicon path here
            });
        }
    }

    // Request permission when page loads
    requestNotificationPermission();

    // Add this to your existing battle update polling logic
    function checkBattleStatus() {
        if (typeof battleId === 'undefined') return;
        
        fetch('battle_status.php?battle_id=' + battleId)
            .then(response => response.json())
            .then(data => {
                if (data.is_over && !window.battleNotificationSent) {
                    window.battleNotificationSent = true;
                    
                    // Send notification to involved users
                    fetch('../backend/send_battle_notification.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'user_id=' + userId + '&battle_id=' + battleId + '&winner_name=' + encodeURIComponent(data.winner_name)
                    })
                    .then(response => response.json())
                    .then(notificationData => {
                        if (notificationData.should_notify) {
                            sendBattleNotification(
                                notificationData.title,
                                notificationData.message
                            );
                        }
                    });
                }
            });
    }

    // Check battle status every 5 seconds
    setInterval(checkBattleStatus, 5000);

    // Initial check
    checkBattleStatus();
    </script>
    <script>
        // Function to fetch and update battle data
        async function updateBattleData() {
            try {
                const response = await fetch(`get_battle_data.php?battle_id=${battleId}`);
                const data = await response.json();
                
                if (data.is_over) {
                    // Stop polling if battle is over
                    clearInterval(battleUpdateInterval);
                    
                    // Update battle status
                    document.querySelector('.battle-status').innerHTML = 
                        `Battle Finished - ${data.winner} Victory`;
                    
                    // Remove loading spinner if it exists
                    const spinner = document.querySelector('.loading-spinner');
                    if (spinner) spinner.remove();

                    // Send notification if not already sent
                    if (!window.battleNotificationSent) {
                        window.battleNotificationSent = true;
                        
                        // Send notification to involved users
                        const notifyResponse = await fetch('../backend/send_battle_notification.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `user_id=${userId}&battle_id=${battleId}&winner_name=${encodeURIComponent(data.winner)}`
                        });
                        
                        const notificationData = await notifyResponse.json();
                        if (notificationData.should_notify) {
                            sendBattleNotification(
                                notificationData.title,
                                notificationData.message
                            );
                        }
                    }
                }
                
                // Update units
                updateUnits(data.defending_units, '.defender-units');
                updateUnits(data.attacking_units, '.attacker-units');
                
                // Update strength bar
                updateStrengthBar(data.defender_strength, data.attacker_strength);
                
                // Update combat reports
                if (data.combat_reports.length > 0) {
                    updateCombatReports(data.combat_reports);
                }
            } catch (error) {
                console.error('Error updating battle data:', error);
            }
        }

        // Function to update unit displays
        function updateUnits(units, containerSelector) {
            const container = document.querySelector(containerSelector);
            units.forEach(unit => {
                const unitElement = container.querySelector(`[data-unit-id="${unit.unit_id}"]`);
                if (unitElement) {
                    // Update HP display
                    const hpElement = unitElement.querySelector('.stat-hp');
                    hpElement.textContent = unit.hp;
                    
                    // Update HP status class
                    hpElement.className = `stat-box stat-hp ${
                        unit.hp <= 0 ? 'stat-hp-dead' : 
                        unit.hp < unit.max_hp / 2 ? 'stat-hp-low' : ''
                    }`;
                }
            });
        }

        // Function to update strength bar
        function updateStrengthBar(defenderStrength, attackerStrength) {
            const totalStrength = defenderStrength + attackerStrength;
            const defenderPercentage = (totalStrength > 0) ? (defenderStrength / totalStrength) * 100 : 50;
            
            document.querySelector('.defender-strength').style.width = `${defenderPercentage}%`;
            document.querySelector('.attacker-strength').style.width = `${100 - defenderPercentage}%`;
            
            // Update strength displays
            document.querySelector('.defender-strength-value').textContent = 
                `Division Strength: ${defenderStrength.toLocaleString()}`;
            document.querySelector('.attacker-strength-value').textContent = 
                `Division Strength: ${attackerStrength.toLocaleString()}`;
        }

        // Function to update combat reports
        function updateCombatReports(reports) {
            const reportList = document.querySelector('.report-list');
            if (!reportList) {
                // Create report list if it doesn't exist
                const reportsContainer = document.querySelector('.combat-reports');
                const noReports = reportsContainer.querySelector('.no-reports');
                if (noReports) {
                    noReports.remove();
                }
                const newReportList = document.createElement('div');
                newReportList.className = 'report-list';
                reportsContainer.appendChild(newReportList);
                return;
            }

            reports.forEach(report => {
                // Only add if report doesn't already exist
                if (!document.querySelector(`[data-report-time="${report.time}"]`)) {
                    const reportElement = document.createElement('div');
                    reportElement.className = 'report-item';
                    reportElement.setAttribute('data-report-time', report.time);
                    reportElement.innerHTML = `
                        <div class="report-time">
                            ${new Date(report.time).toLocaleString()}
                        </div>
                        <div class="report-message">
                            ${report.message.replace(/(\d+)/g, '<span class="number-highlight">$1</span>')}
                        </div>
                    `;
                    reportList.insertBefore(reportElement, reportList.firstChild);
                }
            });
        }

        let battleUpdateInterval;

        // Start polling if battle is not over
        if (!document.querySelector('.battle-status')) {
            battleUpdateInterval = setInterval(updateBattleData, 5000); // Poll every 5 seconds
            
            // Initial update
            updateBattleData();
        }
    </script>
</body>
</html>


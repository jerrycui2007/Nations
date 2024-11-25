<?php
session_start();
require_once '../backend/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch active battles for this user
$stmt = $pdo->prepare("
    SELECT b.* 
    FROM battles b
    JOIN divisions d ON b.defender_division_id = d.division_id OR b.attacker_division_id = d.division_id
    WHERE d.user_id = ? AND b.is_over = 0
    ORDER BY b.battle_id DESC
");
$stmt->execute([$_SESSION['user_id']]);
$active_battles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all visible battle reports for this user
$stmt = $pdo->prepare("
    SELECT br.*, b.battle_name, b.defender_name, b.attacker_name, b.winner_name 
    FROM battle_reports br
    JOIN battles b ON br.battle_id = b.battle_id
    WHERE br.user_id = ? AND br.visible = 1
    ORDER BY br.date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$battle_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Battle Reports - Nations</title>
    <link rel="stylesheet" href="design/style.css">
    <style>
        .battle-reports-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .battle-report {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .battle-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .battle-name {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
        }

        .battle-date {
            color: #666;
            font-size: 0.9em;
        }

        .battle-participants {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 0.9em;
            color: #555;
        }

        .battle-content {
            line-height: 1.6;
        }

        .unit-destroyed.friendly { 
            color: #3498db; 
            font-weight: bold;
        }

        .unit-destroyed.enemy { 
            color: #e74c3c; 
            font-weight: bold;
        }

        .battle-result { 
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }

        .xp-gains, .level-ups { 
            list-style-type: none; 
            padding-left: 20px;
            margin: 10px 0;
        }

        .battle-winner {
            font-weight: bold;
            color: #27ae60;
            margin-top: 15px;
            text-align: right;
        }

        .no-reports {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
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

        .active-battles {
            margin-bottom: 30px;
        }

        .active-battles h2 {
            color: #333;
            margin-bottom: 15px;
        }

        .active-battle-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }

        .active-battle-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .active-battle-card:hover {
            transform: translateY(-2px);
        }

        .active-battle-name {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .active-battle-participants {
            font-size: 0.9em;
            color: #666;
        }

        .battle-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content">
            <div class="battle-reports-container">
                <h1>Battle Reports</h1>
                
                <?php if (!empty($active_battles)): ?>
                    <div class="active-battles">
                        <h2>Active Battles</h2>
                        <div class="active-battle-list">
                            <?php foreach ($active_battles as $battle): ?>
                                <a href="battle.php?battle_id=<?php echo $battle['battle_id']; ?>" class="battle-link">
                                    <div class="active-battle-card">
                                        <div class="active-battle-name">
                                            <?php echo htmlspecialchars($battle['battle_name']); ?>
                                        </div>
                                        <div class="active-battle-participants">
                                            <?php echo htmlspecialchars($battle['defender_name']); ?> vs 
                                            <?php echo htmlspecialchars($battle['attacker_name']); ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($battle_reports)): ?>
                    <div class="no-reports">
                        <p>No battle reports available</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($battle_reports as $report): ?>
                        <div class="battle-report">
                            <div class="battle-header">
                                <div class="battle-name">
                                    <?php echo htmlspecialchars($report['battle_name']); ?>
                                </div>
                                <div class="battle-date">
                                    <?php echo date('M j, Y H:i', strtotime($report['date'])); ?>
                                </div>
                            </div>
                            
                            <div class="battle-participants">
                                <div>Defender: <?php echo htmlspecialchars($report['defender_name']); ?></div>
                                <div>vs</div>
                                <div>Attacker: <?php echo htmlspecialchars($report['attacker_name']); ?></div>
                            </div>
                            
                            <div class="battle-content">
                                <?php echo $report['message']; ?>
                            </div>
                            
                            <?php if ($report['winner_name']): ?>
                                <div class="battle-winner">
                                    Victor: <?php echo htmlspecialchars($report['winner_name']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>
</body>
</html> 
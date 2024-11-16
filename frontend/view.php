<?php
global $pdo;
session_start();
require_once '../backend/db_connection.php';

// Get nation ID from URL parameter
$nation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($nation_id === 0) {
    header("Location: home.php");
    exit();
}

// Fetch nation data
$stmt = $pdo->prepare("SELECT u.country_name, u.leader_name, u.population, u.tier, u.gp, u.flag, u.description, u.creationDate,
                       u.alliance_id, a.name as alliance_name, a.flag_link as alliance_flag
                       FROM users u
                       LEFT JOIN alliances a ON u.alliance_id = a.alliance_id
                       WHERE u.id = ?");
$stmt->execute([$nation_id]);
$nation = $stmt->fetch(PDO::FETCH_ASSOC);

// If nation doesn't exist, redirect to home
if (!$nation) {
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($nation['country_name']); ?> - Nations</title>
    <link rel="stylesheet" type="text/css" href="design/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .main-content {
            margin-left: 220px;
            padding-bottom: 60px; /* Add space for footer */
        }

        .header {
            background: url('resources/westberg.png') no-repeat center center;
            background-size: cover;
            padding: 150px 20px;
            color: white;
            position: relative;
            width: 100%; /* Changed from fixed width */
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

        .nation-flag {
            width: 200px;
            height: 120px;
            object-fit: cover;
            margin-bottom: 20px;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .nation-name {
            font-size: 2.5em;
            font-weight: bold;
            text-align: center;
        }

        .tier-icon {
            height: 1em;
            width: auto;
            vertical-align: middle;
        }

        .tier-number {
            color: #FFD700;
            font-size: 0.8em;
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

        .description-panel {
            background: white;
            padding: 20px;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .description-panel h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        .empty-description {
            color: #666;
            font-style: italic;
            text-align: center;
            padding: 20px 0;
        }

        .nation-description {
            line-height: 1.6;
            color: #333;
            white-space: pre-line;
        }

        .gp-label {
            cursor: pointer;
        }

        .gp-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1001;
        }

        .gp-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 8px;
            z-index: 1002;
            min-width: 300px;
        }

        .gp-close {
            position: absolute;
            right: 10px;
            top: 5px;
            cursor: pointer;
            font-size: 24px;
        }

        .gp-breakdown {
            margin-top: 20px;
        }

        .gp-item {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }

        .gp-item:last-child {
            border-bottom: none;
            font-weight: bold;
        }

        .alliance-status {
            font-size: 1.1em;
            margin: 15px 0;
            text-align: center;
        }

        .alliance-link {
            color: #0066cc;
            text-decoration: none;
        }

        .alliance-link:hover {
            text-decoration: underline;
        }

        .panel {
            background: white;
            padding: 20px;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .alliance-panel {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <div class="header-content">
                <div class="header-left">
                    <img src="<?php echo htmlspecialchars($nation['flag']); ?>" alt="Nation Flag" class="nation-flag">
                    <div class="nation-name"><?php echo htmlspecialchars($nation['country_name']); ?></div>
                </div>
                <div class="header-right">
                    <div class="info-group">
                        <div class="info-label">Leader</div>
                        <div class="info-value"><?php echo htmlspecialchars($nation['leader_name']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Population</div>
                        <div class="info-value">
                            <img src="resources/tier.png" alt="Tier" class="tier-icon">
                            <span class="tier-number"><?php echo $nation['tier']; ?></span>
                            <?php echo number_format($nation['population']); ?>
                        </div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label gp-label" onclick="showGPBreakdown()">GP</div>
                        <div class="info-value gp-label" onclick="showGPBreakdown()"><?php echo number_format($nation['gp']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Founded</div>
                        <div class="info-value">
                            <?php echo date('M j, Y', strtotime($nation['creationDate'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="panel alliance-panel">
                <p class="alliance-status">
                    <?php if ($nation['alliance_id']): ?>
                        <?php echo htmlspecialchars($nation['country_name']); ?> is a member of 
                        <a href="alliance_view.php?id=<?php echo $nation['alliance_id']; ?>" class="alliance-link">
                            <?php echo htmlspecialchars($nation['alliance_name']); ?>
                        </a>
                    <?php else: ?>
                        <?php echo htmlspecialchars($nation['country_name']); ?> is not in any alliance
                    <?php endif; ?>
                </p>
            </div>
            <div class="panel description-panel">
                <h2>About <?php echo htmlspecialchars($nation['country_name']); ?></h2>
                <?php if (!isset($nation['description']) || $nation['description'] === null || trim($nation['description']) === ''): ?>
                    <p class="empty-description">This nation has not set a description yet.</p>
                <?php else: ?>
                    <p class="nation-description"><?php echo nl2br(htmlspecialchars($nation['description'])); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <div class="gp-overlay"></div>
    <div class="gp-popup">
        <span class="gp-close">&times;</span>
        <h2>GP Breakdown</h2>
        <div class="gp-breakdown">
            <div class="gp-item">
                <span>Population</span>
                <span id="population-gp">0</span>
            </div>
            <div class="gp-item">
                <span>Land</span>
                <span id="land-gp">0</span>
            </div>
            <div class="gp-item">
                <span>Factories</span>
                <span id="factory-gp">0</span>
            </div>
            <div class="gp-item">
                <span>Buildings</span>
                <span id="building-gp">0</span>
            </div>
            <div class="gp-item">
                <span>Total GP</span>
                <span id="total-gp">0</span>
            </div>
        </div>
    </div>

    <script>
        function formatNumber(num) {
            return new Intl.NumberFormat().format(num);
        }

        function showGPBreakdown() {
            document.querySelector('.gp-overlay').style.display = 'block';
            document.querySelector('.gp-popup').style.display = 'block';
            
            // Show loading state
            const elements = ['population-gp', 'land-gp', 'factory-gp', 'building-gp', 'total-gp'];
            elements.forEach(id => document.getElementById(id).textContent = 'Loading...');
            
            // Fetch GP breakdown
            fetch('../backend/get_nation_gp_breakdown.php?id=<?php echo $nation_id; ?>')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    // Update values
                    document.getElementById('population-gp').textContent = formatNumber(data.population_gp);
                    document.getElementById('land-gp').textContent = formatNumber(data.land_gp);
                    document.getElementById('factory-gp').textContent = formatNumber(data.factory_gp);
                    document.getElementById('building-gp').textContent = formatNumber(data.building_gp);
                    document.getElementById('total-gp').textContent = formatNumber(data.total_gp);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error fetching GP breakdown');
                });
        }

        function closeGPPopup() {
            document.querySelector('.gp-overlay').style.display = 'none';
            document.querySelector('.gp-popup').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const closeButton = document.querySelector('.gp-close');
            const overlay = document.querySelector('.gp-overlay');
            
            if (closeButton) {
                closeButton.addEventListener('click', closeGPPopup);
            }
            
            if (overlay) {
                overlay.addEventListener('click', closeGPPopup);
            }
        });
    </script>
</body>
</html>
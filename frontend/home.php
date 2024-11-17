<?php
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/calculate_population_growth.php';
require_once '../backend/gp_functions.php';
require_once 'helpers/resource_display.php';
require_once '../backend/continent_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user = null; // Initialize user variable
$error = null;

try {
    // Fetch user data
    $stmt = $pdo->prepare("
        SELECT u.country_name, u.leader_name, u.population, u.tier, u.gp, u.description,
               c.food, c.power, c.consumer_goods, l.urban_areas, u.flag, u.creationDate,
               u.alliance_id, a.name as alliance_name, a.flag_link as alliance_flag, u.continent
        FROM users u 
        JOIN commodities c ON u.id = c.id 
        JOIN land l ON u.id = l.id
        LEFT JOIN alliances a ON u.alliance_id = a.alliance_id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User data not found");
    }

    // Calculate population growth
    $population_growth_result = calculatePopulationGrowth($user);
    $growth = $population_growth_result['growth'];

    // Handle flag update
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_flag'])) {
        $new_flag = trim($_POST['new_flag']);
        
        // Check if it's a valid URL
        if (filter_var($new_flag, FILTER_VALIDATE_URL)) {
            // Check if the URL ends with an allowed image extension
            $valid_extensions = array('.jpg', '.jpeg', '.png', '.webp');
            $is_valid_image = false;
            
            foreach ($valid_extensions as $ext) {
                if (strtolower(substr($new_flag, -strlen($ext))) === $ext) {
                    $is_valid_image = true;
                    break;
                }
            }

            if ($is_valid_image) {
                $stmt = $pdo->prepare("UPDATE users SET flag = ? WHERE id = ?");
                if ($stmt->execute([$new_flag, $_SESSION['user_id']])) {
                    $flag_update_message = "Flag updated successfully!";
                    $user['flag'] = $new_flag;
                } else {
                    $flag_update_message = "Error updating flag.";
                }
            } else {
                $flag_update_message = "Invalid image format. URL must end with .jpg, .jpeg, .png, or .webp";
            }
        } else {
            $flag_update_message = "Invalid URL format.";
        }
    }

    // Handle description update
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_description'])) {
        $new_description = trim($_POST['new_description']);
        
        $stmt = $pdo->prepare("UPDATE users SET description = ? WHERE id = ?");
        if ($stmt->execute([$new_description, $_SESSION['user_id']])) {
            $description_update_message = "Description updated successfully!";
            $user['description'] = $new_description;
        } else {
            $description_update_message = "Error updating description.";
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "An error occurred while loading the page.";
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = "An error occurred while loading the page.";
}

if ($error) {
    die($error); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['country_name']); ?> - Nations</title>
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
            background: url('resources/<?php echo $user['continent'] ? $user['continent'] : 'default'; ?>.png') no-repeat center center;
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

        .panel {
            background: white;
            padding: 20px;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .panel h2 {
            font-size: 1.5em;
            margin-bottom: 15px;
            color: #333;
            text-align: left;
        }

        .flag-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .flag-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
            box-sizing: border-box;
        }

        .flag-input:focus {
            outline: none;
            border-color: #007BFF;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }

        .flag-message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 0.9em;
        }

        .flag-message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .flag-message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .flag-button {
            padding: 12px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.2s;
        }

        .flag-button:hover {
            background-color: #0056b3;
        }

        .resources-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background-color: #333;
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            margin-bottom: 10px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .toast.success {
            background-color: #4CAF50;
        }

        .toast.error {
            background-color: #f44336;
        }

        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }
        .description-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .description-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
            box-sizing: border-box;
            resize: vertical;
            font-family: Arial, sans-serif;
        }

        .description-input:focus {
            outline: none;
            border-color: #007BFF;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }

        .empty-description {
            color: #666;
            font-style: italic;
            margin-bottom: 15px;
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 1100;
            max-width: 500px;
            width: 90%;
        }

        .gp-popup h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .gp-breakdown {
            margin: 15px 0;
        }

        .gp-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .gp-item:last-child {
            border-bottom: none;
            font-weight: bold;
        }

        .gp-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1050;
        }

        .gp-close {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 20px;
            color: #666;
        }

        .gp-close:hover {
            color: #333;
        }

        .gp-label {
            cursor: pointer;
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
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <div class="header-content">
                <div class="header-left">
                    <img src="<?php echo htmlspecialchars($user['flag']); ?>" alt="Nation Flag" class="nation-flag">
                    <div class="nation-name"><?php echo htmlspecialchars($user['country_name']); ?></div>
                </div>
                <div class="header-right">
                    <div class="info-group">
                        <div class="info-label">Leader</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['leader_name']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Population</div>
                        <div class="info-value">
                            <img src="resources/tier.png" alt="Tier" class="tier-icon">
                            <span class="tier-number"><?php echo $user['tier']; ?></span>
                            <?php echo number_format($user['population']); ?>
                            <span style="font-size: 0.6em; color: <?php echo ($growth > 0) ? '#28a745' : '#dc3545'; ?>">
                                <?php echo ($growth >= 0 ? '+' : '-') . number_format(abs($growth)); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label gp-label" onclick="showGPBreakdown()">GP</div>
                        <div class="info-value gp-label" onclick="showGPBreakdown()"><?php echo $user['gp']; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Founded</div>
                        <div class="info-value">
                            <?php echo date('M j, Y', strtotime($user['creationDate'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="panel">
                <p class="alliance-status">
                    <?php if ($user['alliance_id']): ?>
                        <?php echo htmlspecialchars($user['country_name']); ?> is a member of 
                        <a href="alliance_view.php?id=<?php echo $user['alliance_id']; ?>" class="alliance-link">
                            <?php echo htmlspecialchars($user['alliance_name']); ?>.
                        </a>
                    <?php else: ?>
                        <?php echo htmlspecialchars($user['country_name']); ?> is not in any alliance.
                    <?php endif; ?>
                </p>
            </div>

            <div class="panel">
                <p class="alliance-status">
                    <?php if ($user['continent']): ?>
                        <?php echo htmlspecialchars($user['country_name']); ?> is located in 
                        <?php echo htmlspecialchars($CONTINENT_CONFIG[$user['continent']]); ?>.
                    <?php else: ?>
                        <?php echo htmlspecialchars($user['country_name']); ?> is not on any continent yet
                    <?php endif; ?>
                </p>
            </div>

            <div class="panel">
                <h2>Change Flag</h2>
                <?php if (isset($flag_update_message)): ?>
                    <div class="flag-message <?php echo strpos($flag_update_message, 'successfully') !== false ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($flag_update_message); ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="" id="flagForm" class="flag-form" onsubmit="return handleFlagSubmit(event)">
                    <input type="text" 
                           name="new_flag" 
                           id="new_flag" 
                           class="flag-input"
                           placeholder="Enter new flag URL (must end with .jpg, .jpeg, .png, or .webp)" 
                           required>
                    <button type="submit" class="flag-button">Update Flag</button>
                </form>
            </div>

            <div class="panel">
                <h2>Country Description</h2>
                <?php if (isset($description_update_message)): ?>
                    <div class="flag-message <?php echo strpos($description_update_message, 'successfully') !== false ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($description_update_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!isset($user['description']) || $user['description'] === null || trim($user['description']) === ''): ?>
                    <p class="empty-description">No description set. Add a description to tell others about your country!</p>
                <?php endif; ?>
                
                <form method="POST" action="" id="descriptionForm" class="description-form" onsubmit="return handleDescriptionSubmit(event)">
                    <textarea 
                        name="new_description" 
                        id="new_description" 
                        class="description-input" 
                        placeholder="Enter your country's description..."
                        rows="6"><?php echo isset($user['description']) ? htmlspecialchars($user['description']) : ''; ?></textarea>
                    <button type="submit" class="flag-button">Update Description</button>
                </form>
            </div>
        </div>

        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script>
        function showToast(message, type = 'success') {
            // Create toast container if it doesn't exist
            let container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container';
                document.body.appendChild(container);
            }

            // Create toast element
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;

            // Add toast to container
            container.appendChild(toast);

            // Trigger animation
            setTimeout(() => toast.classList.add('show'), 10);

            // Remove toast after 5 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        function validateFlagUrl() {
            const url = document.getElementById('new_flag').value;
            const validExtensions = ['.jpg', '.jpeg', '.png', '.webp'];
            
            const hasValidExtension = validExtensions.some(ext => 
                url.toLowerCase().endsWith(ext)
            );
            
            if (!hasValidExtension) {
                showToast('Invalid image format. URL must end with .jpg, .jpeg, .png, or .webp', 'error');
                return false;
            }
            
            return true;
        }

        async function handleFlagSubmit(event) {
            event.preventDefault();
            
            if (!validateFlagUrl()) {
                return false;
            }

            const form = document.getElementById('flagForm');
            const formData = new FormData(form);

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(data, 'text/html');
                const message = doc.querySelector('.flag-message')?.textContent?.trim();
                
                if (message) {
                    showToast(message, message.includes('successfully') ? 'success' : 'error');
                    if (message.includes('successfully')) {
                        setTimeout(() => window.location.reload(), 1000);
                    }
                }
            } catch (error) {
                showToast('An error occurred while updating the flag.', 'error');
            }
            
            return false;
        }

        async function handleDescriptionSubmit(event) {
            event.preventDefault();
            
            const form = document.getElementById('descriptionForm');
            const formData = new FormData(form);

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(data, 'text/html');
                const message = doc.querySelector('.flag-message')?.textContent?.trim();
                
                if (message) {
                    showToast(message, message.includes('successfully') ? 'success' : 'error');
                    if (message.includes('successfully')) {
                        setTimeout(() => window.location.reload(), 1000);
                    }
                }
            } catch (error) {
                showToast('An error occurred while updating the description.', 'error');
            }
            
            return false;
        }

        <?php if (isset($flag_update_message)): ?>
            showToast("<?php echo addslashes($flag_update_message); ?>", 
                "<?php echo strpos($flag_update_message, 'successfully') !== false ? 'success' : 'error'; ?>");
        <?php endif; ?>

        function showGPBreakdown() {
            document.querySelector('.gp-overlay').style.display = 'block';
            document.querySelector('.gp-popup').style.display = 'block';
            
            // Show loading state
            const elements = ['population-gp', 'land-gp', 'factory-gp', 'building-gp', 'total-gp'];
            elements.forEach(id => document.getElementById(id).textContent = 'Loading...');
            
            // Fetch GP breakdown
            fetch('../backend/get_gp_breakdown.php')
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
                    console.error('GP Breakdown Error:', error);
                    showToast(error.message || 'An error occurred while fetching GP breakdown', 'error');
                    closeGPPopup();
                });
        }

        function closeGPPopup() {
            document.querySelector('.gp-overlay').style.display = 'none';
            document.querySelector('.gp-popup').style.display = 'none';
        }

        function formatNumber(num) {
            return new Intl.NumberFormat().format(num);
        }

        // Add this wrapper around the event listeners
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

        // Add this after the existing DOMContentLoaded event listener
        document.addEventListener('DOMContentLoaded', function() {
            // Format GP value
            const gpValue = document.querySelector('.info-value.gp-label');
            if (gpValue) {
                gpValue.textContent = formatNumber(parseInt(gpValue.textContent));
            }
        });
    </script>
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
</body>
</html>


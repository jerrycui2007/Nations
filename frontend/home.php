<?php
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/calculate_points.php';
require_once '../backend/calculate_population_growth.php';
require_once 'helpers/resource_display.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Fetch user data
    $stmt = $pdo->prepare("
        SELECT u.country_name, u.leader_name, u.population, u.tier, u.gp, 
               c.food, c.power, c.consumer_goods, l.urban_areas, u.flag 
        FROM users u 
        JOIN commodities c ON u.id = c.id 
        JOIN land l ON u.id = l.id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

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
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "An error occurred while loading the page.";
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
        }

        .header {
            background: url('resources/westberg.png') no-repeat center center;
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
            width: 100%;
            margin-left: 0;
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
            margin-left: 0;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 90%;
            text-align: center;
            position: relative;
        }

        .modal-message {
            margin: 20px 0;
            font-size: 1.1em;
            color: #333;
        }

        .modal-button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.2s;
        }

        .modal-button:hover {
            background-color: #0056b3;
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
                        <div class="info-label">GP</div>
                        <div class="info-value"><?php echo formatNumber($user['gp']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
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
        </div>

        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script>
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
                    showModal(message, () => {
                        window.location.reload();
                    });
                }
            } catch (error) {
                showModal('An error occurred while updating the flag.');
            }
            
            return false;
        }

        function showModal(message, callback = null) {
            const modal = document.getElementById('alertModal');
            const modalMessage = document.getElementById('modalMessage');
            modalMessage.textContent = message;
            modal.style.display = 'flex';
            
            // Store callback for when modal is closed
            modal.dataset.callback = callback ? 'true' : 'false';
        }

        function closeModal() {
            const modal = document.getElementById('alertModal');
            modal.style.display = 'none';
            
            // Execute callback if it exists
            if (modal.dataset.callback === 'true') {
                window.location.reload();
            }
        }

        function validateFlagUrl() {
            const url = document.getElementById('new_flag').value;
            const validExtensions = ['.jpg', '.jpeg', '.png', '.webp'];
            
            const hasValidExtension = validExtensions.some(ext => 
                url.toLowerCase().endsWith(ext)
            );
            
            if (!hasValidExtension) {
                showModal('Invalid image format. URL must end with .jpg, .jpeg, .png, or .webp');
                return false;
            }
            
            return true;
        }

        <?php if (isset($flag_update_message)): ?>
            showModal("<?php echo addslashes($flag_update_message); ?>", () => {
                window.location.reload();
            });
        <?php endif; ?>

        document.getElementById('alertModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>

    <!-- Add this right before the closing </body> tag -->
    <div id="alertModal" class="modal">
        <div class="modal-content">
            <div class="modal-message" id="modalMessage"></div>
            <button class="modal-button" onclick="closeModal()">OK</button>
        </div>
    </div>
</body>
</html>

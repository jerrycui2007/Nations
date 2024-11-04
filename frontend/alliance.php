<?php
session_start();
require_once '../backend/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user has an alliance
$stmt = $pdo->prepare("SELECT alliance_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If user has an alliance, fetch alliance details
if ($user['alliance_id'] > 0) {
    // Get alliance details
    $stmt = $pdo->prepare("
        SELECT a.*, u.country_name as leader_country 
        FROM alliances a
        JOIN users u ON a.leader_id = u.id
        WHERE a.alliance_id = ?
    ");
    $stmt->execute([$user['alliance_id']]);
    $alliance = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate total GP and ranking
    $stmt = $pdo->prepare("
        WITH alliance_totals AS (
            SELECT 
                a.alliance_id,
                SUM(u.gp) as total_gp,
                RANK() OVER (ORDER BY SUM(u.gp) DESC) as ranking
            FROM alliances a
            JOIN users u ON u.alliance_id = a.alliance_id
            GROUP BY a.alliance_id
        )
        SELECT total_gp, ranking
        FROM alliance_totals
        WHERE alliance_id = ?
    ");
    $stmt->execute([$user['alliance_id']]);
    $alliance_stats = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle alliance creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_alliance'])) {
    $alliance_name = trim($_POST['alliance_name']);
    $flag_link = trim($_POST['flag_link']);
    $description = trim($_POST['description']);
    $error = null;

    // Validate flag URL
    if (!preg_match('/\.(jpg|jpeg|png|webp)$/i', $flag_link)) {
        $error = "Invalid flag URL format. Must end with .jpg, .jpeg, .png, or .webp";
    }

    // Check if alliance name already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM alliances WHERE name = ?");
    $stmt->execute([$alliance_name]);
    if ($stmt->fetchColumn() > 0) {
        $error = "An alliance with this name already exists.";
    }

    if (!$error) {
        try {
            $pdo->beginTransaction();

            // Create the alliance
            $stmt = $pdo->prepare("INSERT INTO alliances (leader_id, flag_link, name, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $flag_link, $alliance_name, $description]);
            $alliance_id = $pdo->lastInsertId();

            // Update user's alliance_id
            $stmt = $pdo->prepare("UPDATE users SET alliance_id = ? WHERE id = ?");
            $stmt->execute([$alliance_id, $_SESSION['user_id']]);

            $pdo->commit();
            $success_message = "Alliance created successfully!";
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT alliance_id FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "An error occurred while creating the alliance.";
            error_log($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alliance - Nations</title>
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
            background: url('resources/alliance.png') no-repeat center center;
            background-size: cover;
            padding: 150px 20px;
            color: white;
            position: relative;
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

        .content {
            padding: 40px;
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
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <?php if ($user['alliance_id'] == 0): ?>
            <div class="content">
                <div class="alliance-form">
                    <h2>Create New Alliance</h2>
                    
                    <?php if (isset($error)): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($success_message)): ?>
                        <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" onsubmit="return handleAllianceSubmit(event)">
                        <div class="form-group">
                            <label for="alliance_name">Alliance Name</label>
                            <input type="text" id="alliance_name" name="alliance_name" required maxlength="50">
                        </div>
                        
                        <div class="form-group">
                            <label for="flag_link">Flag URL</label>
                            <input type="text" id="flag_link" name="flag_link" required 
                                   placeholder="Enter flag URL (must end with .jpg, .jpeg, .png, or .webp)">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" required></textarea>
                        </div>
                        
                        <button type="submit" name="create_alliance" class="button">Create Alliance</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="header">
                <div class="header-content">
                    <div class="header-left">
                        <img src="<?php echo htmlspecialchars($alliance['flag_link']); ?>" alt="Alliance Flag" class="nation-flag">
                        <div class="nation-name"><?php echo htmlspecialchars($alliance['name']); ?></div>
                    </div>
                    <div class="header-right">
                        <div class="info-group">
                            <div class="info-label">Leader Nation</div>
                            <div class="info-value"><?php echo htmlspecialchars($alliance['leader_country']); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Alliance Ranking</div>
                            <div class="info-value">#<?php echo number_format($alliance_stats['ranking']); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Total GP</div>
                            <div class="info-value"><?php echo number_format($alliance_stats['total_gp']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content">
                <!-- Additional alliance content can go here -->
            </div>
        <?php endif; ?>

        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script>
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);

            // Trigger reflow
            toast.offsetHeight;

            // Add visible class
            toast.classList.add('visible');

            // Remove toast after animation
            setTimeout(() => {
                toast.classList.remove('visible');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }

        async function handleAllianceSubmit(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(data, 'text/html');
                
                const successMessage = doc.querySelector('.success-message')?.textContent;
                const errorMessage = doc.querySelector('.error-message')?.textContent;
                
                if (successMessage) {
                    showToast(successMessage, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else if (errorMessage) {
                    showToast(errorMessage, 'error');
                }
            } catch (error) {
                showToast('An error occurred while creating the alliance.', 'error');
            }
            
            return false;
        }
    </script>
</body>
</html> 
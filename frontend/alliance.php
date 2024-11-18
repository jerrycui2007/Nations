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
        WITH alliance_stats AS (
            SELECT 
                a.alliance_id,
                COUNT(u.id) as member_count,
                SUM(u.gp) as total_gp
            FROM alliances a
            JOIN users u ON u.alliance_id = a.alliance_id
            WHERE a.alliance_id = ?
            GROUP BY a.alliance_id
        )
        SELECT total_gp, member_count
        FROM alliance_stats
    ");
    $stmt->execute([$user['alliance_id']]);
    $alliance_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get member list
    $stmt = $pdo->prepare("
        SELECT DISTINCT id, country_name, leader_name, gp, flag
        FROM users
        WHERE alliance_id = ?
        ORDER BY gp DESC, id ASC
    ");
    $stmt->execute([$user['alliance_id']]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If user is alliance leader, get pending join requests
    if ($_SESSION['user_id'] == $alliance['leader_id']) {
        $stmt = $pdo->prepare("
            SELECT r.*, u.country_name, u.leader_name, u.flag, u.gp
            FROM alliance_join_requests r
            JOIN users u ON r.user_id = u.id
            WHERE r.alliance_id = ?
            ORDER BY r.date DESC
        ");
        $stmt->execute([$user['alliance_id']]);
        $pending_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
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

            // Delete any existing join requests from this user
            $stmt = $pdo->prepare("DELETE FROM alliance_join_requests WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);

            // Build message
            $message = sprintf(
                'The alliance <a href="alliance_view.php?id=%d">%s</a> was founded by <a href="view.php?id=%d">%s</a>',
                $alliance_id,
                htmlspecialchars($alliance_name),
                $_SESSION['user_id'],
                htmlspecialchars($country_name)
            );

            // Insert
            $stmt = $pdo->prepare("
                INSERT INTO notifications (type, message, date) 
                VALUES ('International Relations', ?, NOW())
            ");
            $stmt->execute([$message]);

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

// Handle alliance flag update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_alliance_flag'])) {
    // Check if user is alliance leader
    if ($_SESSION['user_id'] == $alliance['leader_id']) {
        $new_flag = trim($_POST['new_alliance_flag']);
        $error = null;

        // Validate flag URL
        if (!preg_match('/\.(jpg|jpeg|png|webp)$/i', $new_flag)) {
            $error = "Invalid flag URL format. Must end with .jpg, .jpeg, .png, or .webp";
        }

        if (!$error) {
            try {
                $stmt = $pdo->prepare("UPDATE alliances SET flag_link = ? WHERE alliance_id = ?");
                if ($stmt->execute([$new_flag, $user['alliance_id']])) {
                    $success_message = "Alliance flag updated successfully!";
                    $alliance['flag_link'] = $new_flag;
                } else {
                    $error = "Error updating alliance flag.";
                }
            } catch (Exception $e) {
                $error = "An error occurred while updating the alliance flag.";
                error_log($e->getMessage());
            }
        }
    }
}

// Handle alliance description update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_alliance_description'])) {
    if ($_SESSION['user_id'] == $alliance['leader_id']) {
        $new_description = trim($_POST['new_alliance_description']);
        try {
            $stmt = $pdo->prepare("UPDATE alliances SET description = ? WHERE alliance_id = ?");
            if ($stmt->execute([$new_description, $user['alliance_id']])) {
                $description_success = "Alliance description updated successfully!";
                $alliance['description'] = $new_description;
            } else {
                $description_error = "Error updating alliance description.";
            }
        } catch (Exception $e) {
            $description_error = "An error occurred while updating the alliance description.";
            error_log($e->getMessage());
        }
    }
}

// Handle join request response
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_response'])) {
    if ($_SESSION['user_id'] == $alliance['leader_id']) {
        $request_user_id = intval($_POST['user_id']);
        $response = $_POST['request_response'];

        try {
            $pdo->beginTransaction();

            if ($response === 'accept') {
                // Update user's alliance
                $stmt = $pdo->prepare("UPDATE users SET alliance_id = ? WHERE id = ?");
                if (!$stmt->execute([$alliance['alliance_id'], $request_user_id])) {
                    throw new Exception("Failed to update user's alliance");
                }
                
                // Delete all requests from this user
                $stmt = $pdo->prepare("DELETE FROM alliance_join_requests WHERE user_id = ?");
                if (!$stmt->execute([$request_user_id])) {
                    throw new Exception("Failed to delete join requests");
                }

                // Get user and alliance info first
                $stmt = $pdo->prepare("
                    SELECT u.country_name, a.name as alliance_name 
                    FROM users u 
                    CROSS JOIN alliances a 
                    WHERE u.id = ? AND a.alliance_id = ?
                ");
                $stmt->execute([$request_user_id, $alliance['alliance_id']]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);

                // Build message in PHP
                $message = sprintf(
                    '<a href="view.php?id=%d">%s</a> became a member of <a href="alliance_view.php?id=%d">%s</a>',
                    $request_user_id,
                    htmlspecialchars($data['country_name']),
                    $alliance['alliance_id'],
                    htmlspecialchars($data['alliance_name'])
                );

                // Simple insert
                $stmt = $pdo->prepare("
                    INSERT INTO notifications (type, message, date) 
                    VALUES ('International Relations', ?, NOW())
                ");
                $stmt->execute([$message]);
            } else {
                // Delete this specific request
                $stmt = $pdo->prepare("DELETE FROM alliance_join_requests WHERE user_id = ? AND alliance_id = ?");
                if (!$stmt->execute([$request_user_id, $user['alliance_id']])) {
                    throw new Exception("Failed to delete join request");
                }
            }

            $pdo->commit();
            http_response_code(200);
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'An error occurred while processing the request.']);
            exit();
        }
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized action.']);
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['leave_alliance'])) {
    try {
        // Ensure user is not the alliance leader
        if ($_SESSION['user_id'] != $alliance['leader_id']) {
            $pdo->beginTransaction();
            
            // Get alliance name before leaving
            $stmt = $pdo->prepare("
                SELECT a.name, u.country_name 
                FROM alliances a 
                JOIN users u ON u.id = ? 
                WHERE a.alliance_id = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $alliance['alliance_id']]);
            $leaveData = $stmt->fetch(PDO::FETCH_ASSOC);

            // Update user's alliance
            $stmt = $pdo->prepare("UPDATE users SET alliance_id = 0 WHERE id = ?");
            if ($stmt->execute([$_SESSION['user_id']])) {
                // Build message 
                $message = sprintf(
                    '<a href="view.php?id=%d">%s</a> has left <a href="alliance_view.php?id=%d">%s</a>',
                    $_SESSION['user_id'],
                    htmlspecialchars($leaveData['country_name']),
                    $alliance['alliance_id'],
                    htmlspecialchars($leaveData['name'])
                );

                // Insert
                $stmt = $pdo->prepare("
                    INSERT INTO notifications (type, message, date) 
                    VALUES ('International Relations', ?, NOW())
                ");
                $stmt->execute([$message]);

                $pdo->commit();
                $_SESSION['toast'] = json_encode([
                    'message' => 'You have left the alliance.',
                    'type' => 'success'
                ]);
                header("Location: alliance.php");
                exit();
            } else {
                throw new Exception("Failed to leave alliance");
            }
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $_SESSION['toast'] = json_encode([
            'message' => 'An error occurred while leaving the alliance.',
            'type' => 'error'
        ]);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['disband_alliance'])) {
    try {
        // Verify user is alliance leader
        if ($_SESSION['user_id'] == $alliance['leader_id']) {
            $pdo->beginTransaction();

            // Get alliance and leader info before deletion
            $stmt = $pdo->prepare("
                SELECT a.name, u.country_name 
                FROM alliances a 
                JOIN users u ON u.id = a.leader_id 
                WHERE a.alliance_id = ?
            ");
            $stmt->execute([$user['alliance_id']]);
            $disbandData = $stmt->fetch(PDO::FETCH_ASSOC);

            // Reset alliance_id for all members
            $stmt = $pdo->prepare("UPDATE users SET alliance_id = 0 WHERE alliance_id = ?");
            if (!$stmt->execute([$user['alliance_id']])) {
                throw new Exception("Failed to reset member alliance IDs");
            }

            // Delete all join requests for this alliance
            $stmt = $pdo->prepare("DELETE FROM alliance_join_requests WHERE alliance_id = ?");
            if (!$stmt->execute([$user['alliance_id']])) {
                throw new Exception("Failed to delete join requests");
            }

            // Build message
            $message = sprintf(
                'The alliance %s was disbanded by <a href="view.php?id=%d">%s</a>',
                htmlspecialchars($disbandData['name']),
                $_SESSION['user_id'],
                htmlspecialchars($disbandData['country_name'])
            );

            // Insert
            $stmt = $pdo->prepare("
                INSERT INTO notifications (type, message, date) 
                VALUES ('International Relations', ?, NOW())
            ");
            $stmt->execute([$message]);

            // Delete the alliance
            $stmt = $pdo->prepare("DELETE FROM alliances WHERE alliance_id = ?");
            if (!$stmt->execute([$user['alliance_id']])) {
                throw new Exception("Failed to delete alliance");
            }

            $pdo->commit();
            $_SESSION['toast'] = json_encode([
                'message' => 'Alliance disbanded successfully.',
                'type' => 'success'
            ]);
            header("Location: alliance.php");
            exit();
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        $_SESSION['toast'] = json_encode([
            'message' => 'An error occurred while disbanding the alliance.',
            'type' => 'error'
        ]);
        header("Location: alliance.php");
        exit();
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

        .panel {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .flag-form {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .flag-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .flag-button {
            padding: 10px 20px;
            background-color: #0066cc;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .flag-button:hover {
            background-color: #0052a3;
        }

        .flag-message {
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
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

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 4px;
            color: white;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .toast.success {
            background-color: #4CAF50;
        }

        .toast.error {
            background-color: #f44336;
        }

        .toast.visible {
            opacity: 1;
            transform: translateX(0);
        }

        .description-form {
            margin-top: 15px;
        }

        .description-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            margin-bottom: 10px;
            resize: vertical;
        }

        .empty-description {
            color: #666;
            font-style: italic;
            text-align: center;
            padding: 20px 0;
        }

        .alliance-description {
            line-height: 1.6;
            color: #333;
            white-space: pre-line;
        }

        .member-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .member-list th, .member-list td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .member-list th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .member-flag {
            width: 30px;
            height: auto;
            margin-right: 10px;
            vertical-align: middle;
        }

        .alliance-create-form {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .alliance-create-form h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }

        .form-group input, 
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .button {
            width: 100%;
            padding: 12px;
            background-color: #0066cc;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        .button:hover {
            background-color: #0052a3;
        }

        .request-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .request-list th, .request-list td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .request-list th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .request-actions {
            display: flex;
            gap: 10px;
        }

        .accept-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        .deny-button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        .accept-button:hover {
            background-color: #45a049;
        }

        .deny-button:hover {
            background-color: #da190b;
        }

        .panel form {
            margin-top: 15px;
        }

        .deny-button[name="leave_alliance"] {
            padding: 12px 24px;
            font-size: 16px;
        }

        .deny-button[name="disband_alliance"] {
            padding: 12px 24px;
            font-size: 16px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .deny-button[name="disband_alliance"]:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <?php if ($user['alliance_id'] == 0): ?>
            <div class="content">
                <div class="alliance-create-form">
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
                            <div class="info-label">Member Nations</div>
                            <div class="info-value"><?php echo number_format($alliance_stats['member_count']); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Total GP</div>
                            <div class="info-value"><?php echo number_format($alliance_stats['total_gp']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Founded</div>
                            <div class="info-value">
                                <?php echo date('M j, Y', strtotime($alliance['date_created'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content">
                <?php if ($_SESSION['user_id'] == $alliance['leader_id']): ?>
                    <div class="panel">
                        <h2>Change Alliance Flag</h2>
                        <?php if (isset($error)): ?>
                            <div class="flag-message error"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <?php if (isset($success_message)): ?>
                            <div class="flag-message success"><?php echo htmlspecialchars($success_message); ?></div>
                        <?php endif; ?>
                        <form method="POST" action="" id="allianceFlagForm" class="flag-form" onsubmit="return handleAllianceFlagSubmit(event)">
                            <input type="text" 
                                   name="new_alliance_flag" 
                                   id="new_alliance_flag" 
                                   class="flag-input"
                                   placeholder="Enter new flag URL (must end with .jpg, .jpeg, .png, or .webp)" 
                                   required>
                            <button type="submit" class="flag-button">Update Alliance Flag</button>
                        </form>
                    </div>
                <?php endif; ?>
                <div class="panel">
                    <h2>Alliance Description</h2>
                    <?php if ($_SESSION['user_id'] == $alliance['leader_id']): ?>
                        <?php if (isset($description_error)): ?>
                            <div class="flag-message error"><?php echo htmlspecialchars($description_error); ?></div>
                        <?php endif; ?>
                        <?php if (isset($description_success)): ?>
                            <div class="flag-message success"><?php echo htmlspecialchars($description_success); ?></div>
                        <?php endif; ?>
                        <form method="POST" action="" id="allianceDescriptionForm" class="description-form" onsubmit="return handleDescriptionSubmit(event)">
                            <textarea 
                                name="new_alliance_description" 
                                id="new_alliance_description" 
                                class="description-input" 
                                placeholder="Enter your alliance's description..."
                                rows="6"><?php echo isset($alliance['description']) ? htmlspecialchars($alliance['description']) : ''; ?></textarea>
                            <button type="submit" class="flag-button">Update Description</button>
                        </form>
                    <?php else: ?>
                        <?php if (!isset($alliance['description']) || $alliance['description'] === null || trim($alliance['description']) === ''): ?>
                            <p class="empty-description">This alliance has not set a description yet.</p>
                        <?php else: ?>
                            <p class="alliance-description"><?php echo nl2br(htmlspecialchars($alliance['description'])); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="panel">
                    <h2>Member Nations</h2>
                    <table class="member-list">
                        <tr>
                            <th>Nation</th>
                            <th>Leader</th>
                            <th>GP</th>
                        </tr>
                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($member['flag']); ?>" 
                                         alt="Flag of <?php echo htmlspecialchars($member['country_name']); ?>" 
                                         class="member-flag">
                                    <a href="view.php?id=<?php echo $member['id']; ?>" class="nation-link">
                                        <?php echo htmlspecialchars($member['country_name']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($member['leader_name']); ?></td>
                                <td><?php echo number_format($member['gp']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php if ($_SESSION['user_id'] != $alliance['leader_id']): ?>
                    <div class="panel">
                        <h2>Leave Alliance</h2>
                        <p>Warning: This action cannot be undone. You will need to request to join again if you change your mind.</p>
                        <form method="POST" action="" onsubmit="return confirm('Are you sure you want to leave this alliance?');">
                            <button type="submit" name="leave_alliance" class="deny-button">Leave Alliance</button>
                        </form>
                    </div>
                <?php endif; ?>
                <?php if ($_SESSION['user_id'] == $alliance['leader_id']): ?>
                    <div class="panel">
                        <h2>Pending Join Requests</h2>
                        <?php if (empty($pending_requests)): ?>
                            <p class="empty-description">No pending requests.</p>
                        <?php else: ?>
                            <table class="request-list">
                                <tr>
                                    <th>Nation</th>
                                    <th>Leader</th>
                                    <th>GP</th>
                                    <th>Actions</th>
                                </tr>
                                <?php foreach ($pending_requests as $request): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo htmlspecialchars($request['flag']); ?>" 
                                                 alt="Flag of <?php echo htmlspecialchars($request['country_name']); ?>" 
                                                 class="member-flag">
                                            <a href="view.php?id=<?php echo $request['user_id']; ?>" class="nation-link">
                                                <?php echo htmlspecialchars($request['country_name']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($request['leader_name']); ?></td>
                                        <td><?php echo number_format($request['gp']); ?></td>
                                        <td class="request-actions">
                                            <button type="button" 
                                                    onclick="handleRequestResponse(<?php echo $request['user_id']; ?>, 'accept')" 
                                                    class="accept-button">Accept</button>
                                            <button type="button" 
                                                    onclick="handleRequestResponse(<?php echo $request['user_id']; ?>, 'deny')" 
                                                    class="deny-button">Deny</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if ($_SESSION['user_id'] == $alliance['leader_id']): ?>
                    <div class="panel">
                        <h2>Disband Alliance</h2>
                        <p>Warning: This action cannot be undone. All members will be removed and the alliance will be permanently deleted.</p>
                        <form method="POST" action="" onsubmit="return confirm('Are you sure you want to disband this alliance? This action cannot be undone.');">
                            <button type="submit" name="disband_alliance" class="deny-button">Disband Alliance</button>
                        </form>
                    </div>
                <?php endif; ?>
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

            // Trigger reflows
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
            formData.append('create_alliance', '1'); 
            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.error || 'Network response was not ok');
                }

                // Store success message in sessionStorage
                sessionStorage.setItem('toast', JSON.stringify({
                    message: 'Alliance created successfully!',
                    type: 'success'
                }));

                // Reload the page to update the UI
                window.location.reload();
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message || 'An error occurred while creating the alliance.', 'error');
            }
            
            return false;
        }

        async function handleAllianceFlagSubmit(event) {
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
                
                const successMessage = doc.querySelector('.flag-message.success')?.textContent;
                const errorMessage = doc.querySelector('.flag-message.error')?.textContent;
                
                if (successMessage) {
                    sessionStorage.setItem('toast', JSON.stringify({
                        message: successMessage,
                        type: 'success'
                    }));
                    window.location.reload();
                } else if (errorMessage) {
                    showToast(errorMessage, 'error');
                }
            } catch (error) {
                showToast('An error occurred while updating the alliance flag.', 'error');
            }
            
            return false;
        }

        async function handleDescriptionSubmit(event) {
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
                
                const successMessage = doc.querySelector('.flag-message.success')?.textContent;
                const errorMessage = doc.querySelector('.flag-message.error')?.textContent;
                
                if (successMessage) {
                    sessionStorage.setItem('toast', JSON.stringify({
                        message: successMessage,
                        type: 'success'
                    }));
                    window.location.reload();
                } else if (errorMessage) {
                    showToast(errorMessage, 'error');
                }
            } catch (error) {
                showToast('An error occurred while updating the alliance description.', 'error');
            }
            
            return false;
        }

        async function handleRequestResponse(userId, action) {
            try {
                const formData = new FormData();
                formData.append('user_id', userId);
                formData.append('request_response', action);

                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.error || 'Network response was not ok');
                }

                // Store the success message
                sessionStorage.setItem('toast', JSON.stringify({
                    message: action === 'accept' ? 'Request accepted successfully!' : 'Request denied successfully!',
                    type: 'success'
                }));

                // Reload the page to update all data
                window.location.reload();
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message || 'An error occurred while processing the request.', 'error');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const toast = sessionStorage.getItem('toast');
            if (toast) {
                const { message, type } = JSON.parse(toast);
                showToast(message, type);
                sessionStorage.removeItem('toast');
            }
        });
    </script>
</body>
</html> 
<?php
global $pdo;
session_start();
require_once '../backend/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get alliance ID from URL parameter
$alliance_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($alliance_id === 0) {
    header("Location: home.php");
    exit();
}

// Get alliance details
$stmt = $pdo->prepare("
    SELECT a.*, u.country_name as leader_country 
    FROM alliances a
    JOIN users u ON a.leader_id = u.id
    WHERE a.alliance_id = ?
");
$stmt->execute([$alliance_id]);
$alliance = $stmt->fetch(PDO::FETCH_ASSOC);

// If alliance doesn't exist, redirect to home
if (!$alliance) {
    header("Location: home.php");
    exit();
}

// Calculate total GP and member count
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
$stmt->execute([$alliance_id]);
$alliance_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get member list
$stmt = $pdo->prepare("
    SELECT id, country_name, leader_name, gp, flag
    FROM users
    WHERE alliance_id = ?
    ORDER BY gp DESC
");
$stmt->execute([$alliance_id]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if user already has a pending request
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM alliance_join_requests 
    WHERE user_id = ? AND alliance_id = ?
");
$stmt->execute([$_SESSION['user_id'], $alliance_id]);
$has_pending_request = $stmt->fetchColumn() > 0;

// Get user's current alliance status
$stmt = $pdo->prepare("SELECT alliance_id, country_name FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle join request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_join'])) {
    try {
        if ($current_user['alliance_id'] > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'You are already in an alliance.']);
            exit();
        }
        
        if ($has_pending_request) {
            http_response_code(400);
            echo json_encode(['error' => 'You already have a pending request to join this alliance.']);
            exit();
        }

        $stmt = $pdo->prepare("
            INSERT INTO alliance_join_requests (user_id, alliance_id, requester_nation_name) 
            VALUES (?, ?, ?)
        ");
        
        if ($stmt->execute([$_SESSION['user_id'], $alliance_id, $current_user['country_name']])) {
            http_response_code(200);
            exit();
        } else {
            throw new Exception("Failed to create join request");
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'An error occurred while sending the join request.']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($alliance['name']); ?> - Nations</title>
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
            padding-right: 20px;
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
            position: relative;
            padding: 15px;
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
            text-align: center;
        }

        .alliance-flag {
            width: 200px;
            height: 120px;
            object-fit: cover;
            margin-bottom: 20px;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .alliance-name {
            font-size: 2.5em;
            font-weight: bold;
            text-align: center;
        }

        .content {
            padding: 40px;
        }

        .panel {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

        .nation-link {
            color: #0066cc;
            text-decoration: none;
        }

        .nation-link:hover {
            text-decoration: underline;
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

        .join-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        .join-button:hover {
            background-color: #45a049;
        }

        .pending-request-message {
            text-align: center;
            color: #666;
            font-style: italic;
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 4px;
            color: white;
            opacity: 0;
            transform: translateY(-20px);
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
            transform: translateY(0);
        }
    </style>
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

        document.addEventListener('DOMContentLoaded', () => {
            const toast = sessionStorage.getItem('toast');
            if (toast) {
                const { message, type } = JSON.parse(toast);
                showToast(message, type);
                sessionStorage.removeItem('toast');
            }
        });

        async function handleJoinRequest(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            formData.append('request_join', '1');

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
                    message: 'Join request sent successfully!',
                    type: 'success'
                }));

                // Reload the page to update the UI
                window.location.reload();
            } catch (error) {
                console.error('Error:', error);
                showToast('error', error.message || 'An error occurred while sending the join request.');
            }
            
            return false;
        }
    </script>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <div class="header-content">
                <div class="header-left">
                    <img src="<?php echo htmlspecialchars($alliance['flag_link']); ?>" alt="Alliance Flag" class="alliance-flag">
                    <div class="alliance-name"><?php echo htmlspecialchars($alliance['name']); ?></div>
                </div>
                <div class="header-right">
                    <div class="info-group">
                        <div class="info-label">Leader Nation</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($alliance['leader_country']); ?>
                        </div>
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
            <div class="panel">
                <h2>Alliance Description</h2>
                <?php if (!isset($alliance['description']) || $alliance['description'] === null || trim($alliance['description']) === ''): ?>
                    <p class="empty-description">This alliance has not set a description yet.</p>
                <?php else: ?>
                    <p class="alliance-description"><?php echo nl2br(htmlspecialchars($alliance['description'])); ?></p>
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

            <?php if ($current_user['alliance_id'] == 0): ?>
                <div class="panel">
                    <?php if ($has_pending_request): ?>
                        <p class="pending-request-message">You have a pending request to join this alliance.</p>
                    <?php else: ?>
                        <form method="POST" action="" class="join-request-form" onsubmit="return handleJoinRequest(event)">
                            <button type="submit" name="request_join" class="join-button">Request to Join Alliance</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>
</body>
</html> 
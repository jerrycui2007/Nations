<?php
session_start();
require_once '../backend/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Notification types
$valid_types = ['Trade', 'New Nation', 'International Relations', 'Conflict'];

// Get filter from URL, validate it
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
if (!in_array($filter, $valid_types) && $filter !== 'all') {
    $filter = 'all';
}

// Get page number from AJAX request, default to 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Prepare SQL based on filter
$sql = "SELECT * FROM notifications ";
if ($filter !== 'all') {
    $sql .= "WHERE type = '$filter' ";
}
$sql .= "ORDER BY date DESC LIMIT $per_page OFFSET $offset";

// Fetch notifications with pagination
$stmt = $pdo->prepare($sql);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If this is an AJAX request, return only the notifications HTML
if (isset($_GET['ajax'])) {
    foreach ($notifications as $notification): ?>
        <div class="notification-card notification-<?php echo htmlspecialchars($notification['type']); ?>">
            <div class="notification-header">
                <div class="notification-type">
                    <?php echo htmlspecialchars($notification['type']); ?>
                </div>
                <div class="notification-time">
                    <?php echo date('M j, Y g:i A', strtotime($notification['date'])); ?>
                </div>
            </div>
            <div class="notification-message">
                <?php echo $notification['message']; ?>
            </div>
        </div>
    <?php endforeach;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Nations</title>
    <link rel="stylesheet" type="text/css" href="design/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .main-content {
            margin-left: 220px;
            padding-bottom: 60px;
        }
        .content {
            padding: 40px;
        }
        .notifications-container {
            max-width: 800px;
            margin: 0 auto;
            padding-bottom: 20px;
        }
        .notification-card {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .notification-card:hover {
            transform: translateY(-2px);
        }
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .notification-type {
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .notification-success .notification-type {
            background-color: #4CAF50;
            color: white;
        }
        .notification-error .notification-type {
            background-color: #f44336;
            color: white;
        }
        .notification-info .notification-type {
            background-color: #2196F3;
            color: white;
        }
        .notification-time {
            color: #666;
            font-size: 0.9em;
        }
        .notification-message {
            color: #333;
            font-size: 1.1em;
        }
        .notification-success {
            border-left: 4px solid #4CAF50;
        }
        .notification-error {
            border-left: 4px solid #f44336;
        }
        .notification-info {
            border-left: 4px solid #2196F3;
        }
        .empty-notifications {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
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
        .loading {
            text-align: center;
            padding: 20px;
            display: none;
        }
        .filter-container {
            max-width: 800px;
            margin: 0 auto 20px auto;
            display: flex;
            gap: 10px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .filter-button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.2s;
        }
        
        .filter-button.active {
            background-color: #2196F3;
            color: white;
        }
        
        .filter-button:not(.active) {
            background-color: #f0f0f0;
            color: #333;
        }
        
        .filter-button:hover:not(.active) {
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <?php include 'toast.php'; ?>
    
    <div class="main-content">
        <div class="content">
            <h1>Notifications</h1>
            
            <div class="filter-container">
                <button class="filter-button <?php echo $filter === 'all' ? 'active' : ''; ?>" 
                        onclick="setFilter('all')">All</button>
                <?php foreach ($valid_types as $type): ?>
                    <button class="filter-button <?php echo $filter === $type ? 'active' : ''; ?>" 
                            onclick="setFilter('<?php echo $type; ?>')"><?php echo $type; ?></button>
                <?php endforeach; ?>
            </div>
            
            <div class="notifications-container">
                <?php if (empty($notifications)): ?>
                    <div class="empty-notifications">
                        No notifications yet
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-card notification-<?php echo htmlspecialchars($notification['type']); ?>">
                            <div class="notification-header">
                                <div class="notification-type">
                                    <?php echo htmlspecialchars($notification['type']); ?>
                                </div>
                                <div class="notification-time">
                                    <?php echo date('M j, Y g:i A', strtotime($notification['date'])); ?>
                                </div>
                            </div>
                            <div class="notification-message">
                                <?php echo $notification['message']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="loading">Loading more notifications...</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script>
        let page = 1;
        let loading = false;
        let noMoreNotifications = false;
        const container = document.querySelector('.notifications-container');
        const loadingDiv = document.querySelector('.loading');
        
        function setFilter(type) {
            window.location.href = `notifications.php?filter=${type}`;
        }

        function loadMoreNotifications() {
            if (loading || noMoreNotifications) return;
            
            loading = true;
            page++;
            loadingDiv.style.display = 'block';

            const filter = new URLSearchParams(window.location.search).get('filter') || 'all';
            fetch(`notifications.php?page=${page}&filter=${filter}&ajax=1`)
                .then(response => response.text())
                .then(html => {
                    if (html.trim()) {
                        loadingDiv.insertAdjacentHTML('beforebegin', html);
                    } else {
                        noMoreNotifications = true;
                        loadingDiv.style.display = 'none';
                    }
                    loading = false;
                });
        }

        // Detect when user scrolls near bottom
        window.addEventListener('scroll', () => {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500) {
                loadMoreNotifications();
            }
        });
    </script>
</body>
</html> 
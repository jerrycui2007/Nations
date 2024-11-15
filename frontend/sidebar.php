<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <nav>
        <ul>
            <li><a href="home.php" <?php echo ($current_page == 'home.php') ? 'class="active"' : ''; ?>>Home</a></li>
            <li><a href="land.php" <?php echo ($current_page == 'land.php') ? 'class="active"' : ''; ?>>Land</a></li>
            <li><a href="industry.php" <?php echo ($current_page == 'industry.php') ? 'class="active"' : ''; ?>>Industry</a></li>
            <li><a href="buildings.php" <?php echo ($current_page == 'buildings.php') ? 'class="active"' : ''; ?>>Buildings</a></li>
            <li><a href="resources.php" <?php echo ($current_page == 'resources.php') ? 'class="active"' : ''; ?>>Natural Resources</a></li>
            <li><a href="trade.php" <?php echo ($current_page == 'trade.php') ? 'class="active"' : ''; ?>>Trade</a></li>
            <li><a href="leaderboard.php" <?php echo ($current_page == 'leaderboard.php') ? 'class="active"' : ''; ?>>Leaderboard</a></li>
            <li><a href="notifications.php" <?php echo ($current_page == 'notifications.php') ? 'class="active"' : ''; ?>>Notifications</a></li>

            <li><a href="https://discord.gg/b6VBBDKWSG">Discord Server</a></li>

            
            <li><a href="logout.php">Logout</a></li>
        </ul>
        <div class="version-info">v0.2.5-beta</div>
        <div class="version-info">Join the official Discord Server to interact with the community and be notified of updates!</div>
    </nav>
    
</div>

<style>
    .sidebar {
        width: 220px;
        background-color: #2c3e50;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        border-right: 1px solid rgba(255, 255, 255, 0.1);
        z-index: 500;
        display: flex;
        flex-direction: column;
    }

    .sidebar nav {
        flex-grow: 1;
    }

    .version-info {
        padding: 16px;
        text-align: center;
        font-size: 0.8em;
        color: #95a5a6;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar nav ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }

    .sidebar nav ul li {
        padding: 4px 0;
    }

    .sidebar nav ul li a {
        text-decoration: none;
        color: #ecf0f1;
        display: block;
        padding: 12px 24px;
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
        text-align: left;
    }

    .sidebar nav ul li a:hover {
        background-color: #34495e;
        border-left-color: #3498db;
    }

    .sidebar nav ul li a.active {
        background-color: #34495e;
        border-left-color: #3498db;
        font-weight: 600;
    }
</style>

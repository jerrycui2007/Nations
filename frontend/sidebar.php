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
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</div>

<style>
    .sidebar {
        width: 200px;
        background-color: #f8f9fa;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        border-right: 1px solid #dee2e6;
        z-index: 500;
    }

    .sidebar nav ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }

    .sidebar nav ul li {
        padding: 0;
    }

    .sidebar nav ul li a {
        text-decoration: none;
        color: #333;
        display: block;
        padding: 10px 20px;
        transition: background-color 0.3s;
    }

    .sidebar nav ul li a:hover {
        background-color: #e9ecef;
    }

    .sidebar nav ul li a.active {
        font-weight: bold;
    }
</style>

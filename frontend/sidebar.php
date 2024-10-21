<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <nav>
        <ul>
            <li><a href="home.php" <?php echo ($current_page == 'home.php') ? 'class="active"' : ''; ?>>Home</a></li>
            <li><a href="land_management.php" <?php echo ($current_page == 'land_management.php') ? 'class="active"' : ''; ?>>Land</a></li>
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
        padding-top: 20px;
        z-index: 1000;
    }

    .sidebar nav ul {
        list-style-type: none;
        padding: 0;
    }

    .sidebar nav ul li {
        padding: 10px 0;
    }

    .sidebar nav ul li a {
        text-decoration: none;
        color: #333;
        display: block;
        padding: 5px 20px;
        transition: background-color 0.3s;
    }

    .sidebar nav ul li a:hover, .sidebar nav ul li a.active {
        background-color: #e9ecef;
    }
</style>

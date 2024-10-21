$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <nav>
        <ul>
            <li><a href="home.php" <?php echo ($current_page == 'home.php') ? 'class="active"' : ''; ?>>Home</a></li>
            <li><a href="land_management.php" <?php echo ($current_page == 'land_management.php') ? 'class="active"' : ''; ?>>Land Management</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</div>
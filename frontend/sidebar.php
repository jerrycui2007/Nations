<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Calculate time until next hour
$now = new DateTime('now', new DateTimeZone('America/New_York'));
$next_hour = clone $now;
$next_hour->modify('next hour')->setTime($next_hour->format('H'), 0, 0);
$time_until_hour = $now->diff($next_hour);

// Calculate time until 8pm EST
$today_3pm = new DateTime('today 15:00:00', new DateTimeZone('America/New_York'));
if ($now > $today_3pm) {
    $today_3pm->modify('+1 day');
}
$time_until_daily = $now->diff($today_3pm);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div class="sidebar">
    <nav>
        <ul>
            <li><a href="home.php" <?php echo ($current_page == 'home.php') ? 'class="active"' : ''; ?>><i class="fas fa-home"></i> Home</a></li>
            <li><a href="land.php" <?php echo ($current_page == 'land.php') ? 'class="active"' : ''; ?>><i class="fas fa-map"></i> Land</a></li>
            <li><a href="industry.php" <?php echo ($current_page == 'industry.php') ? 'class="active"' : ''; ?>><i class="fas fa-industry"></i> Industry</a></li>
            <li><a href="buildings.php" <?php echo ($current_page == 'buildings.php') ? 'class="active"' : ''; ?>><i class="fas fa-building"></i> Buildings</a></li>
            <li><a href="resources.php" <?php echo ($current_page == 'resources.php') ? 'class="active"' : ''; ?>><i class="fas fa-tree"></i> Natural Resources</a></li>
            <li><a href="trade.php" <?php echo ($current_page == 'trade.php') ? 'class="active"' : ''; ?>><i class="fas fa-exchange-alt"></i> Trade</a></li>
            <li><a href="alliance.php" <?php echo ($current_page == 'alliance.php') ? 'class="active"' : ''; ?>><i class="fas fa-handshake"></i> Alliance</a></li>
            
            <!-- Military Category -->
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">
                    <span><i class="fas fa-shield-alt"></i> Military</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="military.php" <?php echo ($current_page == 'military.php') ? 'class="active"' : ''; ?>><i class="fas fa-flag"></i> Military Overview</a></li>
                    <li><a href="recruitment.php" <?php echo ($current_page == 'recruitment.php') ? 'class="active"' : ''; ?>><i class="fas fa-user-plus"></i> Recruitment</a></li>
                    <li><a href="battles.php" <?php echo ($current_page == 'battles.php') ? 'class="active"' : ''; ?>><i class="fas fa-crosshairs"></i> Battle Reports</a></li>
                    <li><a href="missions.php" <?php echo ($current_page == 'missions.php') ? 'class="active"' : ''; ?>><i class="fas fa-tasks"></i> Missions</a></li>
                </ul>
            </li>

            <!-- Equipment Category -->
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">
                    <span><i class="fas fa-tools"></i> Equipment</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="equipment.php" <?php echo ($current_page == 'equipment.php') ? 'class="active"' : ''; ?>><i class="fas fa-wrench"></i> Equipment</a></li>
                    <li><a href="loot_crates.php" <?php echo ($current_page == 'loot_crates.php') ? 'class="active"' : ''; ?>><i class="fas fa-box-open"></i> Loot Crate Shop</a></li>
                </ul>
            </li>

            <!-- Leaderboards Category -->
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">
                    <span><i class="fas fa-trophy"></i> Leaderboards</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="leaderboard.php" <?php echo ($current_page == 'leaderboard.php') ? 'class="active"' : ''; ?>><i class="fas fa-medal"></i> Nation Leaderboard</a></li>
                    <li><a href="military_leaderboard.php" <?php echo ($current_page == 'military_leaderboard.php') ? 'class="active"' : ''; ?>><i class="fas fa-medal"></i> Military Leaderboard</a></li>
                    <li><a href="alliance_leaderboard.php" <?php echo ($current_page == 'alliance_leaderboard.php') ? 'class="active"' : ''; ?>><i class="fas fa-users"></i> Alliance Leaderboard</a></li>
                </ul>
            </li>

            <li><a href="notifications.php" <?php echo ($current_page == 'notifications.php') ? 'class="active"' : ''; ?>><i class="fas fa-bell"></i> Notifications</a></li>
            <li><a href="https://nations.miraheze.org/wiki/Rules"><i class="fas fa-gavel"></i> Rules</a></li>
            <li><a href="https://discord.gg/b6VBBDKWSG"><i class="fab fa-discord"></i> Discord Server</a></li>
            <li><a href="donate.php" <?php echo ($current_page == 'donate.php') ? 'class="active"' : ''; ?>><i class="fas fa-heart"></i> Donate</a></li>
            <li><a href="https://nations.miraheze.org/wiki/Main_Page"><i class="fas fa-book"></i> Wiki</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
        <div class="version-info">v0.7.1-beta</div>
        <div class="version-info">Join the official Discord Server to interact with the community and be notified of updates!</div>
        <div class="turn-info">
            <div class="turn-timer">
                Next turn in: <?php echo sprintf('%02d:%02d', $time_until_hour->h, $time_until_hour->i); ?>
            </div>
            <div class="turn-timer">
                Next day in: <?php echo sprintf('%02d:%02d', $time_until_daily->h, $time_until_daily->i); ?>
            </div>
        </div>
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
        overflow-y: auto;
    }

    .sidebar nav {
        flex-grow: 1;
    }

    .version-info {
        padding: 12px;
        text-align: center;
        font-size: 0.75em;
        color: #95a5a6;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar nav ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }

    .sidebar nav ul li {
        padding: 2px 0;
    }

    .sidebar nav ul li a {
        text-decoration: none;
        color: #ecf0f1;
        display: block;
        padding: 8px 16px;
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
        text-align: left;
        font-size: 0.9em;
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

    .sidebar::-webkit-scrollbar {
        width: 8px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.1);
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 4px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .sidebar {
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.2) rgba(0, 0, 0, 0.1);
    }

    .rules-section ul li {
        margin-bottom: 10px;
        line-height: 1.5;
    }

    .turn-info {
        padding: 12px;
        background-color: #34495e;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .turn-timer {
        color: #ecf0f1;
        font-size: 0.9em;
        text-align: center;
        margin: 4px 0;
    }

    /* Add these new styles for the dropdown functionality */
    .dropdown-toggle {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .dropdown-toggle i {
        font-size: 0.8em;
        transition: transform 0.3s ease;
    }

    .dropdown.active .dropdown-toggle i {
        transform: rotate(180deg);
    }

    .dropdown-menu {
        display: none;
        padding-left: 20px;
        background-color: #34495e;
    }

    .dropdown.active .dropdown-menu {
        display: block;
    }

    .dropdown-menu li a {
        padding: 6px 16px;
        font-size: 0.85em;
    }

    /* Add this to your existing styles */
    .sidebar nav ul li.dropdown {
        padding: 0;
    }

    .sidebar nav ul li.dropdown > a {
        padding: 8px 16px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all dropdown toggles
    const dropdowns = document.querySelectorAll('.dropdown-toggle');
    
    // Add click event listener to each dropdown
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown').forEach(item => {
                if (item !== parent) {
                    item.classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            parent.classList.toggle('active');
        });
    });

    // Set active dropdown based on current page
    const currentPage = '<?php echo $current_page; ?>';
    const activeLink = document.querySelector(`a[href="${currentPage}"]`);
    if (activeLink) {
        const parentDropdown = activeLink.closest('.dropdown');
        if (parentDropdown) {
            parentDropdown.classList.add('active');
        }
    }
});
</script>



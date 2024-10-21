<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <nav>
        <ul>
            <li><a href="home.php" <?php echo ($current_page == 'home.php') ? 'class="active"' : ''; ?>>Home</a></li>
            <li><a href="land.php" <?php echo ($current_page == 'land.php') ? 'class="active"' : ''; ?>>Land</a></li>
            <li><button id="end-turn-btn" class="sidebar-button">End Turn</button></li>
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

    .sidebar nav ul li a,
    .sidebar nav ul li button.sidebar-button {
        text-decoration: none;
        color: #333;
        display: block;
        padding: 10px 20px;
        transition: background-color 0.3s;
        width: 100%;
        text-align: center;
        border: none;
        background: none;
        cursor: pointer;
        font-size: 1em;
    }

    .sidebar nav ul li a:hover,
    .sidebar nav ul li button.sidebar-button:hover {
        background-color: #e9ecef;
    }

    .sidebar nav ul li a.active {
        font-weight: bold;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const endTurnBtn = document.getElementById('end-turn-btn');
    
    endTurnBtn.addEventListener('click', function() {
        fetch('../backend/hourly_updates.php', {
            method: 'POST',
        })
        .then(response => {
            if (response.ok) {
                console.log('Hourly updates executed successfully');
                // Optionally, you can reload the page or update specific elements here
                location.reload();
            } else {
                console.error('Failed to execute hourly updates');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});
</script>

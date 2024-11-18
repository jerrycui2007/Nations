<?php
global $pdo;
session_start();
require_once '../backend/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// First get all alliances ordered by total GP
$stmt = $pdo->prepare("
    SELECT 
        a.alliance_id,
        a.name as alliance_name,
        a.flag_link,
        a.leader_id,
        u.country_name as leader_country,
        (SELECT COUNT(*) FROM users WHERE alliance_id = a.alliance_id) as member_count,
        (SELECT SUM(gp) FROM users WHERE alliance_id = a.alliance_id) as total_gp,
        (SELECT COUNT(*) + 1 
         FROM (
             SELECT alliance_id, SUM(gp) as alliance_gp 
             FROM users 
             GROUP BY alliance_id
         ) sub 
         WHERE sub.alliance_gp > (
             SELECT SUM(gp) 
             FROM users 
             WHERE alliance_id = a.alliance_id
         )
        ) as ranking
    FROM alliances a
    JOIN users u ON a.leader_id = u.id
    ORDER BY total_gp DESC
    LIMIT 100
");
$stmt->execute();
$top_alliances = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if current user's alliance is in top 100
$user_alliance_in_top_100 = false;
$stmt = $pdo->prepare("SELECT alliance_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['alliance_id']) {
    foreach ($top_alliances as $alliance) {
        if ($alliance['alliance_id'] == $user['alliance_id']) {
            $user_alliance_in_top_100 = true;
            break;
        }
    }

    // If user's alliance not in top 100, get their alliance info
    if (!$user_alliance_in_top_100) {
        $stmt = $pdo->prepare("
            SELECT 
                a.alliance_id,
                a.name as alliance_name,
                a.flag_link,
                a.leader_id,
                u.country_name as leader_country,
                (SELECT COUNT(*) FROM users WHERE alliance_id = a.alliance_id) as member_count,
                (SELECT SUM(gp) FROM users WHERE alliance_id = a.alliance_id) as total_gp,
                (SELECT COUNT(*) 
                 FROM (SELECT alliance_id, SUM(gp) as total_gp 
                       FROM users 
                       GROUP BY alliance_id) as a2 
                 WHERE a2.total_gp > (SELECT SUM(gp) 
                                    FROM users 
                                    WHERE alliance_id = a.alliance_id)
                ) + 1 as ranking
            FROM alliances a
            JOIN users u ON a.leader_id = u.id
            WHERE a.alliance_id = ?
        ");
        $stmt->execute([$user['alliance_id']]);
        $user_alliance = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Handle search
$search_results = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    // First get alliance basic info
    $stmt = $pdo->prepare("
        SELECT 
            a.alliance_id,
            a.name as alliance_name,
            a.flag_link,
            a.leader_id,
            u.country_name as leader_country
        FROM alliances a
        JOIN users u ON a.leader_id = u.id
        WHERE a.name LIKE ? OR u.country_name LIKE ?
    ");
    $stmt->execute([$search_term, $search_term]);
    $alliances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // For each alliance, get member count and total GP
    foreach ($alliances as &$alliance) {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT id) as member_count,
                SUM(gp) as total_gp
            FROM users 
            WHERE alliance_id = ?
        ");
        $stmt->execute([$alliance['alliance_id']]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $alliance['member_count'] = $stats['member_count'];
        $alliance['total_gp'] = $stats['total_gp'];
    }

    // Sort by total GP
    usort($alliances, function($a, $b) {
        return $b['total_gp'] - $a['total_gp'];
    });

    // Add rankings
    foreach ($alliances as $index => &$alliance) {
        $alliance['ranking'] = $index + 1;
    }

    $search_results = $alliances;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alliance Leaderboard - Nations</title>
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
        h1 {
            color: #333;
            font-size: 2.5em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .alliance-link {
            color: #0066cc;
            text-decoration: none;
        }
        .alliance-link:hover {
            text-decoration: underline;
        }
        .flag-img {
            width: 30px;
            height: auto;
            margin-right: 10px;
        }
        .search-section {
            margin-top: 40px;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .search-form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .search-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .search-button {
            padding: 10px 20px;
            background-color: #0066cc;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .search-button:hover {
            background-color: #0052a3;
        }
        .medal {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 5px;
            text-align: center;
            color: white;
            font-weight: bold;
            line-height: 20px;
        }
        .gold {
            background: linear-gradient(45deg, #FFD700, #FFA500);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .silver {
            background: linear-gradient(45deg, #C0C0C0, #A9A9A9);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .bronze {
            background: linear-gradient(45deg, #CD7F32, #8B4513);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content">
            <h1>Alliance Leaderboard</h1>
            <table>
                <tr>
                    <th>Rank</th>
                    <th>Alliance</th>
                    <th>Founder Nation</th>
                    <th>Members</th>
                    <th>Total GP</th>
                </tr>
                <?php foreach ($top_alliances as $alliance): ?>
                    <tr>
                        <td>
                            <?php 
                            if (isset($alliance['ranking']) && $alliance['ranking'] <= 3) {
                                $medalClass = '';
                                switch ($alliance['ranking']) {
                                    case 1:
                                        $medalClass = 'gold';
                                        break;
                                    case 2:
                                        $medalClass = 'silver';
                                        break;
                                    case 3:
                                        $medalClass = 'bronze';
                                        break;
                                }
                                echo '<span class="medal ' . $medalClass . '">' . $alliance['ranking'] . '</span>';
                            } else {
                                echo isset($alliance['ranking']) ? number_format($alliance['ranking']) : '-';
                            }
                            ?>
                        </td>
                        <td>
                            <img src="<?php echo htmlspecialchars($alliance['flag_link']); ?>" 
                                 alt="Flag of <?php echo htmlspecialchars($alliance['alliance_name']); ?>" 
                                 class="flag-img">
                            <a href="alliance_view.php?id=<?php echo $alliance['alliance_id']; ?>" class="alliance-link">
                                <?php echo htmlspecialchars($alliance['alliance_name']); ?>
                            </a>
                        </td>
                        <td>
                            <a href="view.php?id=<?php echo $alliance['leader_id']; ?>" class="alliance-link">
                                <?php echo htmlspecialchars($alliance['leader_country']); ?>
                            </a>
                        </td>
                        <td><?php echo isset($alliance['member_count']) ? number_format($alliance['member_count']) : '0'; ?></td>
                        <td><?php echo isset($alliance['total_gp']) ? number_format($alliance['total_gp']) : '0'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <div class="search-section">
                <h2>Search Alliances</h2>
                <form method="GET" action="" class="search-form">
                    <input type="text" name="search" placeholder="Search by alliance or founder nation name..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                           class="search-input">
                    <button type="submit" class="search-button">Search</button>
                </form>

                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                    <table>
                        <tr>
                            <th>Rank</th>
                            <th>Alliance</th>
                            <th>Founder Nation</th>
                            <th>Members</th>
                            <th>Total GP</th>
                        </tr>
                        <?php foreach ($search_results as $alliance): ?>
                            <tr>
                                <td><?php echo number_format($alliance['ranking']); ?></td>
                                <td>
                                    <img src="<?php echo htmlspecialchars($alliance['flag_link']); ?>" 
                                         alt="Flag of <?php echo htmlspecialchars($alliance['alliance_name']); ?>" 
                                         class="flag-img">
                                    <a href="alliance_view.php?id=<?php echo $alliance['alliance_id']; ?>" class="alliance-link">
                                        <?php echo htmlspecialchars($alliance['alliance_name']); ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="view.php?id=<?php echo $alliance['leader_id']; ?>" class="alliance-link">
                                        <?php echo htmlspecialchars($alliance['leader_country']); ?>
                                    </a>
                                </td>
                                <td><?php echo number_format($alliance['member_count']); ?></td>
                                <td><?php echo number_format($alliance['total_gp']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>
</body>
</html> 
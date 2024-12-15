<?php
global $pdo;
session_start();
require_once '../backend/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// First get all users ordered by military GP
$stmt = $pdo->prepare("
    SELECT 
        u.id, 
        u.country_name, 
        u.leader_name,
        u.flag,
        u.alliance_id,
        u.is_premium,
        a.name as alliance_name,
        a.flag_link as alliance_flag,
        (
            SELECT SUM(firepower + armour + maneuver + FLOOR(hp/10)) / 10
            FROM units
            WHERE player_id = u.id AND hp > 0
        ) as military_gp,
        (
            SELECT COUNT(*) + 1 
            FROM users u2 
            WHERE (
                SELECT SUM(firepower + armour + maneuver + FLOOR(hp/10))
                FROM units
                WHERE player_id = u2.id AND hp > 0
            ) > (
                SELECT SUM(firepower + armour + maneuver + FLOOR(hp/10))
                FROM units
                WHERE player_id = u.id AND hp > 0
            )
        ) as ranking
    FROM users u
    LEFT JOIN alliances a ON u.alliance_id = a.alliance_id
    HAVING military_gp IS NOT NULL
    ORDER BY military_gp DESC
    LIMIT 100
");
$stmt->execute();
$top_nations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if current user is in top 100
$user_in_top_100 = false;
foreach ($top_nations as $nation) {
    if ($nation['id'] == $_SESSION['user_id']) {
        $user_in_top_100 = true;
        break;
    }
}

// If user not in top 100 get their rank and info
if (!$user_in_top_100) {
    $stmt = $pdo->prepare("
        SELECT 
            u1.id, 
            u1.country_name, 
            u1.leader_name,
            u1.flag,
            u1.alliance_id,
            u1.is_premium,
            a.name as alliance_name,
            a.flag_link as alliance_flag,
            (
                SELECT SUM(firepower + armour + maneuver + FLOOR(hp/10))
                FROM units
                WHERE player_id = u1.id AND hp > 0
            ) as military_gp,
            (
                SELECT COUNT(*) + 1 
                FROM users u2 
                WHERE (
                    SELECT SUM(firepower + armour + maneuver + FLOOR(hp/10))
                    FROM units
                    WHERE player_id = u2.id AND hp > 0
                ) > (
                    SELECT SUM(firepower + armour + maneuver + FLOOR(hp/10))
                    FROM units
                    WHERE player_id = u1.id AND hp > 0
                )
            ) as ranking
        FROM users u1
        LEFT JOIN alliances a ON u1.alliance_id = a.alliance_id
        WHERE u1.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user_nation = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle search
$search_results = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    $stmt = $pdo->prepare("
        SELECT 
            u1.id, 
            u1.country_name, 
            u1.leader_name,
            u1.flag,
            u1.alliance_id,
            u1.is_premium,
            a.name as alliance_name,
            a.flag_link as alliance_flag,
            (
                SELECT SUM(firepower + armour + maneuver + FLOOR(hp/10))
                FROM units
                WHERE player_id = u1.id AND hp > 0
            ) as military_gp,
            (
                SELECT COUNT(*) + 1 
                FROM users u2 
                WHERE (
                    SELECT SUM(firepower + armour + maneuver + FLOOR(hp/10))
                    FROM units
                    WHERE player_id = u2.id AND hp > 0
                ) > (
                    SELECT SUM(firepower + armour + maneuver + FLOOR(hp/10))
                    FROM units
                    WHERE player_id = u1.id AND hp > 0
                )
            ) as ranking
        FROM users u1
        LEFT JOIN alliances a ON u1.alliance_id = a.alliance_id
        WHERE u1.country_name LIKE ? OR u1.leader_name LIKE ?
        HAVING military_gp IS NOT NULL
        ORDER BY military_gp DESC
        LIMIT 50
    ");
    $stmt->execute([$search_term, $search_term]);
    $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Military Leaderboard - Nations</title>
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
        tr.current-user {
            background-color: #e6f3ff;
        }
        .nation-link {
            color: #0066cc;
            text-decoration: none;
        }
        .nation-link:hover {
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
        .alliance-link {
            color: #0066cc;
            text-decoration: none;
        }
        .alliance-link:hover {
            text-decoration: underline;
        }
        .unallied {
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content">
            <h1>Military Leaderboard</h1>
            <table>
                <tr>
                    <th>Rank</th>
                    <th>Nation</th>
                    <th>Alliance</th>
                    <th>Leader</th>
                    <th>Military GP</th>
                </tr>
                <?php foreach ($top_nations as $nation): ?>
                    <tr <?php if ($nation['id'] == $_SESSION['user_id']) echo 'class="current-user"'; ?>>
                        <td>
                            <?php 
                            if ($nation['ranking'] <= 3) {
                                $medalClass = '';
                                switch ($nation['ranking']) {
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
                                echo '<span class="medal ' . $medalClass . '">' . $nation['ranking'] . '</span>';
                            } else {
                                echo number_format($nation['ranking']);
                            }
                            ?>
                        </td>
                        <td>
                            <img src="<?php echo htmlspecialchars($nation['flag']); ?>" alt="Flag of <?php echo htmlspecialchars($nation['country_name']); ?>" class="flag-img">
                            <a href="view.php?id=<?php echo $nation['id']; ?>" class="nation-link">
                                <?php echo htmlspecialchars($nation['country_name']); ?>
                            </a>
                        </td>
                        <td>
                            <?php if ($nation['alliance_id']): ?>
                                <img src="<?php echo htmlspecialchars($nation['alliance_flag']); ?>" alt="Flag of <?php echo htmlspecialchars($nation['alliance_name']); ?>" class="flag-img">
                                <a href="alliance_view.php?id=<?php echo $nation['alliance_id']; ?>" class="alliance-link">
                                    <?php echo htmlspecialchars($nation['alliance_name']); ?>
                                </a>
                            <?php else: ?>
                                <span class="unallied">Unallied</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($nation['is_premium']): ?>
                                <img src="resources/premium.png" alt="Premium" style="height: 1em; width: auto; vertical-align: middle; margin-right: 5px;">
                            <?php endif; ?>
                            <?php echo htmlspecialchars($nation['leader_name']); ?>
                        </td>
                        <td><?php echo number_format($nation['military_gp']); ?></td>
                    </tr>
                <?php endforeach; ?>
                
                <?php if (!$user_in_top_100): ?>
                    <tr class="current-user">
                        <td><?php echo number_format($user_nation['ranking']); ?></td>
                        <td>
                            <img src="<?php echo htmlspecialchars($user_nation['flag']); ?>" alt="Flag of <?php echo htmlspecialchars($user_nation['country_name']); ?>" class="flag-img">
                            <a href="view.php?id=<?php echo $user_nation['id']; ?>" class="nation-link">
                                <?php echo htmlspecialchars($user_nation['country_name']); ?>
                            </a>
                        </td>
                        <td>
                            <?php if ($user_nation['alliance_id']): ?>
                                <img src="<?php echo htmlspecialchars($user_nation['alliance_flag']); ?>" alt="Flag of <?php echo htmlspecialchars($user_nation['alliance_name']); ?>" class="flag-img">
                                <a href="alliance_view.php?id=<?php echo $user_nation['alliance_id']; ?>" class="alliance-link">
                                    <?php echo htmlspecialchars($user_nation['alliance_name']); ?>
                                </a>
                            <?php else: ?>
                                <span class="unallied">Unallied</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user_nation['is_premium']): ?>
                                <img src="resources/premium.png" alt="Premium" style="height: 1em; width: auto; vertical-align: middle; margin-right: 5px;">
                            <?php endif; ?>
                            <?php echo htmlspecialchars($user_nation['leader_name']); ?>
                        </td>
                        <td><?php echo number_format($user_nation['military_gp']); ?></td>
                    </tr>
                <?php endif; ?>
            </table>

            <div class="search-section">
                <h2>Search Nations</h2>
                <form method="GET" action="" class="search-form">
                    <input type="text" name="search" placeholder="Search by country or leader name..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                           class="search-input">
                    <button type="submit" class="search-button">Search</button>
                </form>

                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                    <table>
                        <tr>
                            <th>Rank</th>
                            <th>Nation</th>
                            <th>Alliance</th>
                            <th>Leader</th>
                            <th>Military GP</th>
                        </tr>
                        <?php foreach ($search_results as $nation): ?>
                            <tr <?php if ($nation['id'] == $_SESSION['user_id']) echo 'class="current-user"'; ?>>
                                <td><?php echo number_format($nation['ranking']); ?></td>
                                <td>
                                    <img src="<?php echo htmlspecialchars($nation['flag']); ?>" 
                                         alt="Flag of <?php echo htmlspecialchars($nation['country_name']); ?>" 
                                         class="flag-img">
                                    <a href="view.php?id=<?php echo $nation['id']; ?>" class="nation-link">
                                        <?php echo htmlspecialchars($nation['country_name']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($nation['alliance_id']): ?>
                                        <img src="<?php echo htmlspecialchars($nation['alliance_flag']); ?>" 
                                             alt="Flag of <?php echo htmlspecialchars($nation['alliance_name']); ?>" 
                                             class="flag-img">
                                        <a href="alliance_view.php?id=<?php echo $nation['alliance_id']; ?>" class="alliance-link">
                                            <?php echo htmlspecialchars($nation['alliance_name']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="unallied">Unallied</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($nation['is_premium']): ?>
                                        <img src="resources/premium.png" alt="Premium" style="height: 1em; width: auto; vertical-align: middle; margin-right: 5px;">
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($nation['leader_name']); ?>
                                </td>
                                <td><?php echo number_format($nation['military_gp']); ?></td>
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
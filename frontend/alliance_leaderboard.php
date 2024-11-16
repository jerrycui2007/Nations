<?php
global $pdo;
session_start();
require_once '../backend/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch top 100 alliances with their ranks and flags
$stmt = $pdo->prepare("
    WITH alliance_stats AS (
        SELECT 
            a.alliance_id,
            a.name as alliance_name,
            a.flag_link,
            a.leader_id,
            u.country_name as leader_country,
            COUNT(DISTINCT um.id) as member_count,
            SUM(um.gp) as total_gp,
            (SELECT COUNT(*) + 1 
             FROM (
                SELECT a2.alliance_id, SUM(u2.gp) as alliance_gp
                FROM alliances a2
                JOIN users u2 ON u2.alliance_id = a2.alliance_id
                GROUP BY a2.alliance_id
             ) ranked 
             WHERE ranked.alliance_gp > SUM(um.gp)
            ) as ranking
        FROM alliances a
        JOIN users u ON a.leader_id = u.id
        JOIN users um ON um.alliance_id = a.alliance_id
        GROUP BY a.alliance_id, a.name, a.flag_link, a.leader_id, u.country_name
        ORDER BY total_gp DESC
        LIMIT 100
    )
    SELECT * FROM alliance_stats
");
$stmt->execute();
$top_alliances = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle search
$search_results = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    $stmt = $pdo->prepare("
        WITH alliance_stats AS (
            SELECT 
                a.alliance_id,
                a.name as alliance_name,
                a.flag_link,
                a.leader_id,
                u.country_name as leader_country,
                COUNT(DISTINCT um.id) as member_count,
                SUM(um.gp) as total_gp,
                (SELECT COUNT(*) + 1 
                 FROM (
                    SELECT a2.alliance_id, SUM(u2.gp) as alliance_gp
                    FROM alliances a2
                    JOIN users u2 ON u2.alliance_id = a2.alliance_id
                    GROUP BY a2.alliance_id
                 ) ranked 
                 WHERE ranked.alliance_gp > SUM(um.gp)
                ) as ranking
            FROM alliances a
            JOIN users u ON a.leader_id = u.id
            JOIN users um ON um.alliance_id = a.alliance_id
            WHERE a.name LIKE ? OR u.country_name LIKE ?
            GROUP BY a.alliance_id, a.name, a.flag_link, a.leader_id, u.country_name
            ORDER BY total_gp DESC
            LIMIT 50
        )
        SELECT * FROM alliance_stats
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
                            if ($alliance['ranking'] <= 3) {
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
                                echo number_format($alliance['ranking']);
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
                        <td><?php echo number_format($alliance['member_count']); ?></td>
                        <td><?php echo number_format($alliance['total_gp']); ?></td>
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
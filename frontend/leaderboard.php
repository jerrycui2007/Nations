<?php
global $conn;
session_start();
require_once '../backend/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch top 100 nations with their ranks
$stmt = $conn->prepare("
    SELECT 
        id, 
        country_name, 
        leader_name, 
        gp,
        (SELECT COUNT(*) + 1 
         FROM users u2 
         WHERE u2.gp > u1.gp) as ranking
    FROM users u1
    ORDER BY gp DESC
    LIMIT 100
");
$stmt->execute();
$top_nations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Check if current user is in top 100
$user_in_top_100 = false;
foreach ($top_nations as $nation) {
    if ($nation['id'] == $_SESSION['user_id']) {
        $user_in_top_100 = true;
        break;
    }
}

// If user not in top 100, get their rank and info
if (!$user_in_top_100) {
    $stmt = $conn->prepare("
        SELECT 
            id, 
            country_name, 
            leader_name, 
            gp,
            (SELECT COUNT(*) + 1 
             FROM users u2 
             WHERE u2.gp > u1.gp) as ranking
        FROM users u1
        WHERE id = ?
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user_nation = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Nations</title>
    <link rel="stylesheet" type="text/css" href="design/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .content {
            margin-left: 200px;
            padding: 20px;
            padding-bottom: 60px;
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
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content">
        <h1>Leaderboard</h1>
        <table>
            <tr>
                <th>Rank</th>
                <th>Nation</th>
                <th>Leader</th>
                <th>GP</th>
            </tr>
            <?php foreach ($top_nations as $nation): ?>
                <tr <?php if ($nation['id'] == $_SESSION['user_id']) echo 'class="current-user"'; ?>>
                    <td><?php echo number_format($nation['ranking']); ?></td>
                    <td><a href="view.php?id=<?php echo $nation['id']; ?>" class="nation-link">
                        <?php echo htmlspecialchars($nation['country_name']); ?>
                    </a></td>
                    <td><?php echo htmlspecialchars($nation['leader_name']); ?></td>
                    <td><?php echo number_format($nation['gp']); ?></td>
                </tr>
            <?php endforeach; ?>
            
            <?php if (!$user_in_top_100): ?>
                <tr class="current-user">
                    <td><?php echo number_format($user_nation['ranking']); ?></td>
                    <td><a href="view.php?id=<?php echo $user_nation['id']; ?>" class="nation-link">
                        <?php echo htmlspecialchars($user_nation['country_name']); ?>
                    </a></td>
                    <td><?php echo htmlspecialchars($user_nation['leader_name']); ?></td>
                    <td><?php echo number_format($user_nation['gp']); ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>

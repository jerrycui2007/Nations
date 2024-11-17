<?php
session_start();
require_once '../backend/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rules - Nations</title>
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
            margin-bottom: 30px;
        }
        h2 {
            color: #444;
            font-size: 1.8em;
            margin-top: 40px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        h3 {
            color: #555;
            font-size: 1.4em;
            margin-top: 25px;
            margin-bottom: 15px;
        }
        .rules-section {
            background: white;
            padding: 25px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .rules-section p {
            line-height: 1.6;
            margin-bottom: 15px;
            color: #333;
        }
        ul {
            margin-bottom: 20px;
            padding-left: 25px;
        }
        li {
            margin-bottom: 10px;
            line-height: 1.5;
        }
        .important-note {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .warning {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 20px 0;
        }
        .rules-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        
        .rules-table th,
        .rules-table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        .rules-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .rules-table tr:hover {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content">
        <h1>Game Rules</h1>

        <div class="rules-section">
            <h2>Rules and Consequences</h2>
            <p>
                In Nations, players have a lot of freedom to do what they want. However, there are some rules that players must follow to ensure a fair and enjoyable experience for everyone.
                Moderators will determine if a rule has been broken and will take appropriate action. The consequences will vary based on the severity of the rule violation.
            </p>
            
            <table class="rules-table">
                <thead>
                    <tr>
                        <th>Rule</th>
                        <th>Description</th>
                        <th>Punishment</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Multiple Accounts</td>
                        <td>One account per player. No creating alternative accounts.</td>
                        <td>1-year suspension of all associated accounts, reset of main account, to permanent ban.</td>
                    </tr>
                    <tr>
                        <td>Bug Exploitation</td>
                        <td>No exploiting bugs or glitches in the game. Report them to the developer instead.</td>
                        <td>Warning, suspension or permanent ban (depending on severity)</td>
                    </tr>
                    <tr>
                        <td>Account Sharing</td>
                        <td>No sharing accounts with other players.</td>
                        <td>1-week suspension to permanent ban</td>
                    </tr>
                    <tr>
                        <td>Inappropriate Content</td>
                        <td>No offensive or inappropriate content in names, descriptions, or flags.</td>
                        <td>Warning and forced name change to suspension of expression privileges</td>
                    </tr>
                    <tr>
                        <td>Harassment/Bullying</td>
                        <td>No harassment, bullying, hate speech, or threatening behavior towards other players through game or other communication channels.</td>
                        <td>Warning to permanent ban (depending on severity)</td>
                    </tr>
                    <tr>
                        <td>Webscraping/Botting/Scripting</td>
                        <td>No using programs to automatically play the game. This includes using scripts to notify you of events, analyze prices, etc.</td>
                        <td>1-week suspension to permanent ban</td>
                    </tr>
                    <tr>
                        <td>Coercing Players</td>
                        <td>Do not coerce other players to do things for you, delete their account, etc.</td>
                        <td>One year suspension to permanent ban.</td>
                    </tr>
                    <tr>
                        <td>Market Manipulation</td>
                        <td>Do not attempt to unfairly manipulate market prices,.</td>
                        <td>1-week to 1-year suspension of trading privileges.</td>
                    </tr>
                    <tr>
                        <td>Direct Trading</td>
                        <td>Do not transfer resources directly between nations (i.e. selling consumer goods for $1). This includes trading between alliance members.</td>
                        <td>1-week to 1-year suspension of trading privileges.</td>
                    </tr>
                    <tr>
                        <td>Spamming</td>
                        <td>Do not spam actions (i.e. creating multiple trade deals for 1 unit, creating and disbanding alliances repeatedly)</td>
                        <td>1-week to 1-year suspension of action privileges.</td>
                    </tr>
                    <tr>
                        <td>Real Money Trading (RMT)</td>
                        <td>No buying, selling, or trading in-game items/resources for real-world money or items.</td>
                        <td>Permanent ban</td>
                    </tr>
                    <tr>
                        <td>False Reporting</td>
                        <td>No deliberately false reporting of other players to waste moderator time.</td>
                        <td>Warning to 1-month suspension</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="important-note">
                <strong>Note:</strong> Multiple rule violations will result in more severe punishments. All moderator decisions are final.
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 
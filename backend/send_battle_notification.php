<?php
require_once 'db_connection.php';

function sendBattleNotification($user_id, $battle_id, $winner_name) {
    global $pdo;
    
    // Check if user has notifications enabled
    $stmt = $pdo->prepare("SELECT notifications_enabled FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && $user['notifications_enabled']) {
        // Get battle name
        $stmt = $pdo->prepare("SELECT battle_name FROM battles WHERE battle_id = ?");
        $stmt->execute([$battle_id]);
        $battle = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'should_notify' => true,
            'title' => 'Battle Concluded',
            'message' => "The battle '{$battle['battle_name']}' has ended! {$winner_name} emerged victorious!"
        ]);
    } else {
        echo json_encode(['should_notify' => false]);
    }
}

if (isset($_POST['user_id']) && isset($_POST['battle_id']) && isset($_POST['winner_name'])) {
    sendBattleNotification(
        intval($_POST['user_id']),
        intval($_POST['battle_id']),
        $_POST['winner_name']
    );
} 
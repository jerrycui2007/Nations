<?php
// Add error handling at the top
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'disband_unit_errors.log');

// Set JSON header immediately
header('Content-Type: application/json');

session_start();
require_once 'db_connection.php';
require_once 'unit_config.php';

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not logged in');
    }

    if (!isset($_POST['unit_id']) || empty($_POST['unit_id'])) {
        throw new Exception('No unit ID provided');
    }

    $user_id = $_SESSION['user_id'];
    $unit_id = $_POST['unit_id'];

    $pdo->beginTransaction();

    // Get unit type for refund calculation
    $stmt = $pdo->prepare("
        SELECT u.*, d.in_combat 
        FROM units u 
        LEFT JOIN divisions d ON u.division_id = d.division_id 
        WHERE u.unit_id = ? AND u.player_id = ?
    ");
    $stmt->execute([$unit_id, $user_id]);
    $unit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$unit) {
        throw new Exception("Unit not found or doesn't belong to you");
    }

    if ($unit['in_combat']) {
        throw new Exception("Cannot disband units from a division that is in combat");
    }

    // Get recruitment costs from unit config
    $unit_type = strtolower(str_replace(' ', '_', $unit['name']));
    $refund_costs = isset($UNIT_CONFIG[$unit_type]['recruitment_cost']) 
        ? $UNIT_CONFIG[$unit_type]['recruitment_cost'] 
        : [];

    // Delete buffs first (foreign key constraint)
    $stmt = $pdo->prepare("DELETE FROM buffs WHERE unit_id = ?");
    $stmt->execute([$unit_id]);

    // Delete the unit
    $stmt = $pdo->prepare("DELETE FROM units WHERE unit_id = ? AND player_id = ?");
    $stmt->execute([$unit_id, $user_id]);

    // Refund 50% of recruitment costs
    $updates = [];
    $params = [];
    foreach ($refund_costs as $resource => $amount) {
        $refund_amount = floor($amount * 0.5);
        if ($refund_amount > 0) {
            $updates[] = "$resource = $resource + ?";
            $params[] = $refund_amount;
        }
    }

    if (!empty($updates)) {
        $params[] = $user_id;
        $stmt = $pdo->prepare("
            UPDATE commodities 
            SET " . implode(', ', $updates) . "
            WHERE id = ?
        ");
        $stmt->execute($params);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Unit disbanded successfully']);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Database Error in disband_unit.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred', 'debug' => $e->getMessage()]);
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Error $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("Fatal Error in disband_unit.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred', 'debug' => $e->getMessage()]);
} 
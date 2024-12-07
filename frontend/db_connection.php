<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

global $pdo;

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=u719074828_sandbox",
        'u719074828_ics4ue',
        'room2038A'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Helper function for transactions
    function beginTransaction($pdo) {
        return $pdo->beginTransaction();
    }
    
    function commitTransaction($pdo) {
        return $pdo->commit();
    }
    
    function rollbackTransaction($pdo) {
        return $pdo->rollBack();
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


?>


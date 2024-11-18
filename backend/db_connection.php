<?php
// Database connection code here
$host = 'localhost';
$dbname = 'nations';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Connection failed. Please try again later.");
}

function checkContinent() {
    global $pdo;
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $current_page = basename($_SERVER['SCRIPT_NAME']);
    $excluded_pages = ['login.php', 'choose_continent.php', 'register.php'];

    if (isset($_SESSION['user_id']) && !in_array($current_page, $excluded_pages)) {
        $stmt = $pdo->prepare("SELECT continent FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user['continent'] === null) {
            header("Location: choose_continent.php");
            exit();
        }
    }
}

// Only check continent for frontend pages
if (strpos($_SERVER['SCRIPT_NAME'], '/frontend/') !== false) {
    checkContinent();
}
?>

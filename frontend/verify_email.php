<?php
session_start();
require_once '../backend/db_connection.php';

$error = "";
$success = "";

if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, email 
            FROM users 
            WHERE verification_token = ? 
            AND token_expiry > NOW() 
            AND email_verified = FALSE
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Update user as verified
            $stmt = $pdo->prepare("
                UPDATE users 
                SET email_verified = TRUE,
                    verification_token = NULL,
                    token_expiry = NULL
                WHERE id = ?
            ");
            $stmt->execute([$user['id']]);
            
            $success = "Email verified successfully! You can now log in.";
        } else {
            $error = "Invalid or expired verification token.";
        }
    } catch (PDOException $e) {
        $error = "An error occurred. Please try again later.";
        error_log($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Nations</title>
    <link rel="stylesheet" type="text/css" href="design/style.css">
</head>
<body>
    <div class="container">
        <h1>Email Verification</h1>
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p style="color: green;"><?php echo $success; ?></p>
            <p><a href="login.php">Click here to login</a></p>
        <?php endif; ?>
    </div>
</body>
</html>

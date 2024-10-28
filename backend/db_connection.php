<?php
// Read database configuration from file
$config_file = __DIR__ . '/config/db_config.txt';

if (!file_exists($config_file)) {
    die("Database configuration file not found at: " . $config_file);
}

$config_lines = file($config_file, FILE_IGNORE_NEW_LINES);

if (count($config_lines) < 4) {
    die("Invalid database configuration file format. Found " . count($config_lines) . " lines");
}

// Database connection settings
$servername = trim($config_lines[0]);
$username = trim($config_lines[1]);
$password = trim($config_lines[2]); // This will now correctly read the password even if empty
$dbname = trim($config_lines[3]);

// Debug output (remove in production)
echo "Server: " . $servername . "<br>";
echo "Username: " . $username . "<br>";
echo "Password length: " . strlen($password) . "<br>";
echo "Database: " . $dbname . "<br>";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nations";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

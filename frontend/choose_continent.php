<?php
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/continent_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user already has a continent
$stmt = $pdo->prepare("SELECT continent FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['continent'] !== null) {
    header("Location: home.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['continent'])) {
    $selected_continent = trim($_POST['continent']);
    
    // Validate continent selection
    if (array_key_exists($selected_continent, $CONTINENT_CONFIG)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET continent = ? WHERE id = ?");
            $stmt->execute([$selected_continent, $_SESSION['user_id']]);
            header("Location: home.php");
            exit();
        } catch (PDOException $e) {
            $error = "An error occurred. Please try again.";
        }
    } else {
        $error = "Invalid continent selection.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Continent - Nations</title>
    <link rel="stylesheet" type="text/css" href="design/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            max-width: 1000px;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .continent-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .continent-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .continent-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .continent-card.selected {
            border-color: #3498db;
            background-color: #f8f9fa;
        }

        .continent-name {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .continent-info {
            margin-bottom: 15px;
            color: #666;
        }

        .resource-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            font-size: 0.9em;
        }

        .resource {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .pro {
            color: #27ae60;
        }

        .con {
            color: #e74c3c;
        }

        .ok {
            color: #f39c12;
        }

        form {
            text-align: center;
        }

        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.2s;
        }

        button:hover {
            background-color: #2980b9;
        }

        .error-message {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 20px;
        }

        .resource-groups {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 0.9em;
        }

        .resource-group {
            display: flex;
            gap: 8px;
            align-items: baseline;
        }

        .group-label {
            font-weight: bold;
            min-width: 70px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Choose Continent</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="continentForm">
            <div class="continent-grid">
                <div class="continent-card" onclick="selectContinent('westberg')">
                    <div class="continent-name">Westberg</div>
                    <div class="continent-info">
                        <div>Difficulty: Easy</div>
                        <div>Type: Temperate</div>
                    </div>
                    <div class="resource-groups">
                        <div class="resource-group">
                            <div class="group-label pro">Pros:</div>
                            <div>Food, Power, Building Materials</div>
                        </div>
                        <div class="resource-group">
                            <div class="group-label ok">Average:</div>
                            <div>Consumer Goods, Metal, Fuel</div>
                        </div>
                        <div class="resource-group">
                            <div class="group-label con">Cons:</div>
                            <div>Ammunition, Uranium</div>
                        </div>
                    </div>
                </div>

                <div class="continent-card" onclick="selectContinent('amarino')">
                    <div class="continent-name">Amarino</div>
                    <div class="continent-info">
                        <div>Difficulty: Medium</div>
                        <div>Type: Tropical</div>
                    </div>
                    <div class="resource-groups">
                        <div class="resource-group">
                            <div class="group-label pro">Pros:</div>
                            <div>Food, Consumer Goods</div>
                        </div>
                        <div class="resource-group">
                            <div class="group-label ok">Average:</div>
                            <div>Building Materials, Ammunition, Uranium</div>
                        </div>
                        <div class="resource-group">
                            <div class="group-label con">Cons:</div>
                            <div>Power, Metal, Fuel</div>
                        </div>
                    </div>
                </div>

                <div class="continent-card" onclick="selectContinent('san_sebastian')">
                    <div class="continent-name">San Sebastian</div>
                    <div class="continent-info">
                        <div>Difficulty: Medium</div>
                        <div>Type: Subtropical</div>
                    </div>
                    <div class="resource-groups">
                        <div class="resource-group">
                            <div class="group-label pro">Pros:</div>
                            <div>Building Materials, Ammunition</div>
                        </div>
                        <div class="resource-group">
                            <div class="group-label ok">Average:</div>
                            <div>Food, Power, Consumer Goods</div>
                        </div>
                        <div class="resource-group">
                            <div class="group-label con">Cons:</div>
                            <div>Metal, Fuel, Uranium</div>
                        </div>
                    </div>
                </div>

                <div class="continent-card" onclick="selectContinent('tind')">
                    <div class="continent-name">Tind</div>
                    <div class="continent-info">
                        <div>Difficulty: Hard</div>
                        <div>Type: Alpine</div>
                    </div>
                    <div class="resource-groups">
                        <div class="resource-group">
                            <div class="group-label pro">Pros:</div>
                            <div>Power, Metal</div>
                        </div>
                        <div class="resource-group">
                            <div class="group-label ok">Average:</div>
                            <div>Building Materials, Uranium</div>
                        </div>
                        <div class="resource-group">
                            <div class="group-label con">Cons:</div>
                            <div>Food, Consumer Goods, Ammunition, Fuel</div>
                        </div>
                    </div>
                </div>

                <div class="continent-card" onclick="selectContinent('zaheria')">
                    <div class="continent-name">Zaheria</div>
                    <div class="continent-info">
                        <div>Difficulty: Hard</div>
                        <div>Type: Desert</div>
                    </div>
                    <div class="resource-groups">
                        <div class="resource-group">
                            <div class="group-label pro">Pros:</div>
                            <div>Fuel, Uranium</div>
                        </div>
                        <div class="resource-group">
                            <div class="group-label ok">Average:</div>
                            <div>Power, Ammunition</div>
                        </div>
                        <div class="resource-group">
                            <div class="group-label con">Cons:</div>
                            <div>Food, Building Materials, Consumer Goods, Metal</div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="continent" id="selectedContinent">
            <button type="submit" disabled id="submitButton">Choose Continent</button>
        </form>
    </div>

    <script>
        function selectContinent(continent) {
            // Remove selected class from all cards
            document.querySelectorAll('.continent-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            event.currentTarget.classList.add('selected');
            
            // Update hidden input and enable submit button
            document.getElementById('selectedContinent').value = continent;
            document.getElementById('submitButton').disabled = false;
        }
    </script>
</body>
</html> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate - Nations</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            min-height: 100vh;
            display: flex;
            position: relative;
        }

        .main-content {
            flex: 1;
            margin-left: 220px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            padding-bottom: 60px;
        }

        .donate-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 8px;
        }

        .donate-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .donate-header h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .donate-header p {
            color: #666;
        }

        .donate-content {
            line-height: 1.6;
            color: #333;
        }

        .donate-method {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border: 1px solid #dee2e6;
        }

        .donate-method h3 {
            color: #333;
            margin-top: 0;
        }

        .email-highlight {
            background-color: #3498db;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 1.1em;
        }

        .benefits-list {
            list-style-type: none;
            padding: 0;
            color: #333;
        }

        .benefits-list li {
            margin: 10px 0;
            padding-left: 25px;
            position: relative;
        }

        .benefits-list li:before {
            content: "✓";
            color: #2ecc71;
            position: absolute;
            left: 0;
        }

        .thank-you {
            text-align: center;
            margin-top: 30px;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="donate-container">
            <div class="donate-header">
                <h1><i class="fas fa-heart"></i> Support Nations</h1>
                <p>Help us keep the game running and growing!</p>
            </div>

            <div class="donate-content">
                <p>Your support helps us maintain and improve Nations, ensuring we can continue providing an amazing gaming experience for everyone. By donating, you're directly contributing to the game's development and maintenance. For every $10 CAD you donate, you will receive one month of premium.</p>
                <p>Note: Donations are not refundable, and are not eligible for tax deductions. Donations are handled manually, so it may take some time for your donation to be processed.</p>

                <div class="donate-method">
                    <h3><i class="fas fa-money-bill-wave"></i> How to Donate</h3>
                    <p>You can support Nations by sending an e-transfer to:</p>
                    <p style="text-align: center;"><span class="email-highlight">jerrycui07@gmail.com</span></p>
                </div>

                <h3><i class="fas fa-star"></i> Benefits of Donating</h3>
                <ul class="benefits-list">
                    <li>Exclusive premium badge</li>
                    <li>Custom nation background image</li>
                    <li>More premium features coming soon!</li>
                </ul>

                <div class="thank-you">
                    <p>Thank you for considering a donation to Nations.<br>Your support means the world to us!</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
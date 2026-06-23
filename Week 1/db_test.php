<?php

/**
 * db_test.php - Verify MySQL connectivity
 * Author: Natasha Wanjiru Thungu
 * Reg No: BSCCS/2024/53895
 */

// Database connection parameters
$host = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty
$database = "test"; // Built-in test database

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("<p style='color:red; font-family: Arial; font-size: 1.2em; text-align: center; margin-top: 50px;'>
        ❌ Connection FAILED: " . $conn->connect_error . "
    </p>");
}

// Display success message with server info
echo "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Connection Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f4f8;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: #fff;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .success {
            color: #27ae60;
            font-size: 1.5em;
        }
        .info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: left;
        }
        .info-item {
            padding: 8px 0;
            border-bottom: 1px solid #e8edf2;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .badge {
            display: inline-block;
            background: #27ae60;
            color: #fff;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 0.8em;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class='card'>
        <h2 class='success'>✅ Database Connected Successfully!</h2>
        <div class='info'>
            <div class='info-item'><strong>MySQL Server Version:</strong> " . $conn->server_info . "</div>
            <div class='info-item'><strong>Host:</strong> " . $host . "</div>
            <div class='info-item'><strong>Database:</strong> " . $database . "</div>
            <div class='info-item'><strong>Character Set:</strong> " . $conn->character_set_name() . "</div>
        </div>
        <span class='badge'>BIT3208 - Connection Successful</span>
    </div>
</body>
</html>
";

// Close the connection
$conn->close();

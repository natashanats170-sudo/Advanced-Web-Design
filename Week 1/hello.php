<?php

/**
 * hello.php - First PHP page for BIT3208
 * Author: Natasha Wanjiru Thungu
 * Reg No: BSCCS/2024/53895
 */

// PHP can embed directly inside HTML
$studentName = "Natasha Wanjiru Thungu";
$regNumber = "BSCCS/2024/53895";
$currentDate = date("l, F j, Y"); // e.g. Monday, June 22, 2026
$serverTime = date("H:i:s");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello World - BIT3208</title>
    <style>
        body {
            font-family: Arial, sans-serif;
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
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        h1 {
            color: #1F4E79;
            font-size: 2.2em;
            margin-bottom: 10px;
        }

        .info {
            color: #555;
            margin: 8px 0;
            font-size: 1.1em;
        }

        .time {
            color: #2E75B6;
            font-weight: bold;
            font-size: 1.2em;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #e8edf2;
        }

        .badge {
            display: inline-block;
            background: #1F4E79;
            color: #fff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>Hello, World! 🌍</h1>
        <p class="info"><strong>Student:</strong> <?= htmlspecialchars($studentName) ?></p>
        <p class="info"><strong>Reg No:</strong> <?= htmlspecialchars($regNumber) ?></p>
        <p class="info"><strong>Date:</strong> <?= $currentDate ?></p>
        <p class="time">🕐 Server Time: <?= $serverTime ?></p>
        <span class="badge">BIT3208 - Advanced Web Design</span>
    </div>
</body>

</html>

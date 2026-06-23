<?php

/**
 * login.php - Placeholder login page for Week 1
 * Full implementation will be added in Week 4
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - University Voting System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #1F4E79, #2E75B6);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.2);
        }

        h1 {
            color: #1F4E79;
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.8em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #2E75B6;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: #1F4E79;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #2E75B6;
        }

        .info {
            text-align: center;
            margin-top: 20px;
            color: #888;
            font-size: 0.9em;
        }

        .info a {
            color: #1F4E79;
            text-decoration: none;
            font-weight: bold;
        }

        .info a:hover {
            text-decoration: underline;
        }

        .placeholder-note {
            text-align: center;
            margin-top: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            color: #888;
            font-size: 0.85em;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>🔐 Login</h1>
        <form>
            <div class="form-group">
                <label for="email">University Email</label>
                <input type="email" id="email" placeholder="your.email@university.ac.ke">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" placeholder="Enter your password">
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <div class="info">
            New student? <a href="register.php">Register here</a>
        </div>
        <div class="placeholder-note">
            ⚡ This is a placeholder. Full functionality will be implemented in Week 4.
        </div>
    </div>
</body>

</html>

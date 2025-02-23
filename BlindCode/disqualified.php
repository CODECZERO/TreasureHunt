<?php
session_start();

// Disqualified users should NOT be able to access the contest
setcookie("disqualified", "true", time() + (365 * 24 * 60 * 60), "/"); // Reinforce the cookie

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disqualified</title>
    <style>
        body {
            text-align: center;
            background-color: #f8d7da;
            color: #721c24;
            font-family: Arial, sans-serif;
            margin-top: 100px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 36px;
        }
        p {
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>❌ You Have Been Disqualified!</h1>
        <p>You switched tabs too many times and violated the rules.</p>
        <p>Access to this contest is now permanently blocked.</p>
    </div>
</body>
</html>
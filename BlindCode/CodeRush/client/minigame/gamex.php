<?php
session_start();

// Generate a random number if not set
if (!isset($_SESSION['random_number'])) {
    $_SESSION['random_number'] = rand(1, 100);
    $_SESSION['attempts'] = 0;
}

$message = "Guess a number between 1 and 100";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guess = (int)$_POST['guess'];
    $_SESSION['attempts']++;
    
    if ($guess < $_SESSION['random_number']) {
        $message = "Too low! Try again.";
    } elseif ($guess > $_SESSION['random_number']) {
        $message = "Too high! Try again.";
    } else {
        $message = "🎉 Congratulations! You guessed it in " . $_SESSION['attempts'] . " attempts.";
        session_destroy(); // Reset game
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Number Guessing Game</title>
    <style>
        body {
            text-align: center;
            font-family: Arial, sans-serif;
            margin-top: 50px;
            background-color: #f4f4f4;
            animation: fadeIn 1s ease-in;
        }
        
        .game-container {
            display: inline-block;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            animation: slideIn 0.5s ease-in-out;
        }
        
        input {
            padding: 10px;
            font-size: 16px;
            border: 2px solid #007BFF;
            border-radius: 5px;
            transition: 0.3s;
        }
        
        input:focus {
            border-color: #0056b3;
            outline: none;
        }
        
        button {
            padding: 10px 15px;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            transition: 0.3s;
        }
        
        button:hover {
            background-color: #0056b3;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="game-container">
        <h2>🎯 Number Guessing Game</h2>
        <p><?php echo $message; ?></p>
        <form method="POST">
            <input type="number" name="guess" min="1" max="100" required>
            <button type="submit">Submit Guess</button>
        </form>
    </div>
</body>
</html>

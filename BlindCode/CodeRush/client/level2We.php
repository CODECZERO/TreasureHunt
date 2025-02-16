<?php
// Get all text files from the "riddle" folder
$riddle_files = glob("riddle/*.txt");

if (!$riddle_files) {
    die("No riddles found.");
}

// Pick a random riddle file
$random_file = $riddle_files[array_rand($riddle_files)];

// Read the riddle content
$riddle_content = file_get_contents($random_file);

// Check for user input and redirect
$error_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $answer = trim($_POST['answer']);
    $answer = basename($answer); // Prevents directory traversal
    $target_page = "minigame/" . $answer . ".php";

    if (file_exists($target_page)) {
        header("Location: " . $target_page);
        exit();
    } else {
        $error_message = "❌ Incorrect answer! Try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riddle Challenge</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background-color: #121212;
            color: #fff;
            text-align: center;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
            width: 90%;
            max-width: 400px;
        }
        h2 {
            margin-bottom: 10px;
            font-size: 24px;
        }
        p {
            font-size: 18px;
            font-style: italic;
            margin-bottom: 20px;
        }
        input {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            outline: none;
            background: #222;
            color: #fff;
            text-align: center;
        }
        input:focus {
            border: 2px solid #00ffcc;
        }
        button {
            margin-top: 15px;
            padding: 12px;
            font-size: 16px;
            width: 100%;
            border: none;
            border-radius: 5px;
            background: #00ffcc;
            color: #121212;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s ease;
        }
        button:hover {
            background: #00b38f;
        }
        .error {
            color: #ff4d4d;
            margin-top: 15px;
            font-weight: bold;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🧠 Riddle Challenge</h2>
        <p><?php echo nl2br(htmlspecialchars($riddle_content)); ?></p>
        <form method="POST">
            <input type="text" name="answer" placeholder="Enter your answer..." required>
            <button type="submit">Submit</button>
        </form>
        <?php if (!empty($error_message)) { echo "<p class='error'>$error_message</p>"; } ?>
    </div>
</body>
</html>

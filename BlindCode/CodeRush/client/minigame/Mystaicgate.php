<?php
session_start();

// Define file paths
$codeFile = __DIR__ . "/codes.txt";
$assignedFile = __DIR__ . "/assigned_codes.txt";

// Load available codes from file
function loadAvailableCodes($codeFile, $assignedFile) {
    if (!file_exists($codeFile)) {
        return [];
    }

    // Read all codes
    $allCodes = file($codeFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Read assigned codes
    $assignedCodes = file_exists($assignedFile) ? file($assignedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

    // Filter out used codes
    return array_diff($allCodes, $assignedCodes);
}

// Get an unused code
function getUnusedCode($codeFile, $assignedFile) {
    $availableCodes = loadAvailableCodes($codeFile, $assignedFile);

    if (empty($availableCodes)) {
        return "NO CODES LEFT"; // Fallback if all codes are used
    }

    $selectedCode = $availableCodes[array_rand($availableCodes)];

    // Mark as used
    file_put_contents($assignedFile, $selectedCode . PHP_EOL, FILE_APPEND);

    return $selectedCode;
}

// Assign a winning code for the session
if (!isset($_SESSION['winningCode'])) {
    $_SESSION['winningCode'] = getUnusedCode($codeFile, $assignedFile);
}
$winningCode = $_SESSION['winningCode'];

// Horror sound selection
$audioFolder = __DIR__ . "/hraudio";
$audioFiles = glob($audioFolder . "/*.mp3");
$selectedAudio = !empty($audioFiles) ? $audioFiles[array_rand($audioFiles)] : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>2D Avoidance Game</title>
    <style>
        body {
            text-align: center;
            font-family: Arial, sans-serif;
            background: black;
            color: white;
        }
        canvas {
            border: 2px solid white;
            background: gray;
        }
        .hidden {
            display: none;
        }
        .win-message {
            font-size: 20px;
            font-weight: bold;
            color: green;
        }
        .timer {
            font-size: 24px;
            font-weight: bold;
            margin-top: 10px;
            color: yellow;
        }
    </style>
</head>
<body>

    <h1>Survive 45 Seconds to Win!</h1>
    <p>Move with Arrow Keys | Avoid the Red Boxes</p>

    <!-- Countdown Timer -->
    <p class="timer">Time Left: <span id="timer">45</span> sec</p>

    <canvas id="gameCanvas" width="400" height="400"></canvas>

    <audio id="horrorAudio" class="hidden">
        <source src="<?php echo htmlspecialchars($selectedAudio); ?>" type="audio/mp3">
    </audio>

    <p id="winMessage" class="win-message hidden">Congratulations! Your Code: <span id="winningCode"><?php echo $winningCode; ?></span></p>

    <script>
        // Game Variables
        const canvas = document.getElementById("gameCanvas");
        const ctx = canvas.getContext("2d");

        let player = { x: 180, y: 350, size: 20, speed: 4 }; 
        let obstacles = [];
        let gameOver = false;
        let startTime = Date.now();
        const winTime = 45000;
        const timerElement = document.getElementById("timer");

        function createObstacle() {
            let size = Math.random() * 40 + 10;
            let x = Math.random() * (canvas.width - size);
            let speed = Math.random() * 3 + 2;
            obstacles.push({ x, y: 0, size, speed });
        }

        function update() {
            if (gameOver) return;

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            let timeLeft = Math.max(0, Math.floor((winTime - (Date.now() - startTime)) / 1000));
            timerElement.textContent = timeLeft;

            if (timeLeft <= 0) {
                winGame();
                return;
            }

            ctx.fillStyle = "blue";
            ctx.fillRect(player.x, player.y, player.size, player.size);

            ctx.fillStyle = "red";
            for (let i = 0; i < obstacles.length; i++) {
                obstacles[i].y += obstacles[i].speed;
                ctx.fillRect(obstacles[i].x, obstacles[i].y, obstacles[i].size, obstacles[i].size);

                if (
                    player.x < obstacles[i].x + obstacles[i].size &&
                    player.x + player.size > obstacles[i].x &&
                    player.y < obstacles[i].y + obstacles[i].size &&
                    player.y + player.size > obstacles[i].y
                ) {
                    endGame();
                    return;
                }
            }

            obstacles = obstacles.filter(obs => obs.y < canvas.height);

            requestAnimationFrame(update);
        }

        function endGame() {
            gameOver = true;
            alert("Game Over! Listen closely...");
            document.getElementById("horrorAudio").play();
        }

        function winGame() {
    gameOver = true;
    document.getElementById("winMessage").style.display = "block";

    // Send the winning code to the server to save it
    fetch("save_winning_code.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "code=" + encodeURIComponent("<?php echo $winningCode; ?>")
    });
}

        window.addEventListener("keydown", (e) => {
            if (gameOver) return;

            if (e.key === "ArrowLeft" && player.x > 0) player.x -= player.speed;
            if (e.key === "ArrowRight" && player.x < canvas.width - player.size) player.x += player.speed;
            if (e.key === "ArrowUp" && player.y > 0) player.y -= player.speed;
            if (e.key === "ArrowDown" && player.y < canvas.height - player.size) player.y += player.speed;
        });

        setInterval(createObstacle, 700);

        update();
    </script>

</body>
</html>


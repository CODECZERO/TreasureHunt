<?php
session_start();

// Define the folder for horror audio files
$audioFolder = __DIR__ . "/hraudio";

// Get all available horror audio files
$audioFiles = glob($audioFolder . "/*.mp3");

// Select a random horror sound when the player loses
$selectedAudio = !empty($audioFiles) ? $audioFiles[array_rand($audioFiles)] : "";

// Generate a random winning code (4 letters + 4 numbers)
function generateWinningCode() {
    $letters = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4);
    $numbers = substr(str_shuffle("0123456789"), 0, 4);
    return $letters . $numbers;
}

// Assign the winning code for the session
if (!isset($_SESSION['winningCode'])) {
    $_SESSION['winningCode'] = generateWinningCode();
}
$winningCode = $_SESSION['winningCode'];

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

        let player = { x: 180, y: 350, size: 20, speed: 4 }; // Slightly reduced speed
        let obstacles = [];
        let gameOver = false;
        let startTime = Date.now();
        const winTime = 45000; // 45 seconds to win (harder)
        const timerElement = document.getElementById("timer");

        function createObstacle() {
            let size = Math.random() * 40 + 10; // Larger max size
            let x = Math.random() * (canvas.width - size);
            let speed = Math.random() * 3 + 2; // Increased base speed
            obstacles.push({ x, y: 0, size, speed });
        }

        function update() {
            if (gameOver) return;

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Update countdown timer
            let timeLeft = Math.max(0, Math.floor((winTime - (Date.now() - startTime)) / 1000));
            timerElement.textContent = timeLeft;

            // Check win condition
            if (timeLeft <= 0) {
                winGame();
                return;
            }

            // Draw player
            ctx.fillStyle = "blue";
            ctx.fillRect(player.x, player.y, player.size, player.size);

            // Update and draw obstacles
            ctx.fillStyle = "red";
            for (let i = 0; i < obstacles.length; i++) {
                obstacles[i].y += obstacles[i].speed;
                ctx.fillRect(obstacles[i].x, obstacles[i].y, obstacles[i].size, obstacles[i].size);

                // Collision detection
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

            // Remove off-screen obstacles
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
            document.getElementById("winMessage").classList.remove("hidden");
        }

        // Player Movement
        window.addEventListener("keydown", (e) => {
            if (gameOver) return;

            if (e.key === "ArrowLeft" && player.x > 0) player.x -= player.speed;
            if (e.key === "ArrowRight" && player.x < canvas.width - player.size) player.x += player.speed;
            if (e.key === "ArrowUp" && player.y > 0) player.y -= player.speed;
            if (e.key === "ArrowDown" && player.y < canvas.height - player.size) player.y += player.speed;
        });

        // Generate obstacles faster (every 700ms instead of 1s)
        setInterval(createObstacle, 700);

        // Start game loop
        update();
    </script>

</body>
</html>

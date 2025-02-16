<?php
session_start();

// Horror audio setup
$audioFolder = __DIR__ . "/hraudio";
$audioFiles = glob($audioFolder . "/*.mp3");
$selectedAudio = !empty($audioFiles) ? $audioFiles[array_rand($audioFiles)] : "";

// Code storage file
$codeFile = __DIR__ . "/codes.txt";
$assignedCodes = __DIR__ . "/assigned_codes.txt"; // File to track assigned codes

// Load available codes
$codes = file($codeFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Load assigned codes
$usedCodes = file_exists($assignedCodes) ? file($assignedCodes, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

// Get an unused code
$availableCodes = array_diff($codes, $usedCodes);

if (!empty($availableCodes)) {
    $winCode = array_values($availableCodes)[array_rand($availableCodes)];
    file_put_contents($assignedCodes, $winCode . PHP_EOL, FILE_APPEND);
} else {
    $winCode = "No codes left! You are late!";
}

?>
<!DOCTYPE html>
<html lang="en">    
<head>
    <meta charset="UTF-8">
    <title>Frog Escape - Win a Secret Code!</title>
    <style>
        body { text-align: center; background: black; color: white; }
        canvas { background: linear-gradient(to bottom, #4CAF50 25%, #5D4037 50%, #4CAF50 75%);
                 border: 4px solid white; display: block; margin: auto; }
        .hidden { display: none; }
        .win-message {
            font-size: 24px;
            font-weight: bold;
            color: gold;
            display: none;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>🐸 Frog Escape - Win a Code! 🚗</h1>
    <p>Cross the road! Move with Arrow Keys</p>
    <canvas id="gameCanvas" width="500" height="500"></canvas>

    <audio id="horrorAudio" class="hidden">
        <source src="<?php echo htmlspecialchars($selectedAudio); ?>" type="audio/mp3">
    </audio>

    <p id="winMessage" class="win-message">🎉 You survived! Your Code: <span id="winningCode"><?php echo $winCode; ?></span></p>

    <script>
        const canvas = document.getElementById("gameCanvas");
        const ctx = canvas.getContext("2d");

        let frog = { x: 250, y: 460, size: 15, color: "lime" };
        let cars = [];
        let gameOver = false;
        let winCode = "<?php echo $winCode; ?>"; // Get winning code from PHP

        function createCar() {
            let width = 50, height = 20;
            let yPositions = [80, 160, 240, 320, 400];
            let y = yPositions[Math.floor(Math.random() * yPositions.length)];
            let speed = Math.random() * 5 + 3; // Faster cars
            let color = ["red", "blue", "orange", "yellow", "purple"][Math.floor(Math.random() * 5)];
            cars.push({ x: -width, y, width, height, speed, color });
        }

        function update() {
            if (gameOver) return;
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Draw frog
            ctx.fillStyle = frog.color;
            ctx.beginPath();
            ctx.arc(frog.x, frog.y, frog.size, 0, Math.PI * 2);
            ctx.fill();

            // Draw and move cars
            cars.forEach((car, i) => {
                car.x += car.speed;
                ctx.fillStyle = car.color;
                ctx.fillRect(car.x, car.y, car.width, car.height);
                if (car.x > canvas.width) cars.splice(i, 1);
                if (collision(frog, car)) endGame();
            });

            // Win condition
            if (frog.y <= 20) {
                winGame();
                return;
            }

            requestAnimationFrame(update);
        }

        function collision(a, b) {
            return a.x < b.x + b.width && a.x + a.size > b.x && a.y < b.y + b.height && a.y + a.size > b.y;
        }

        function endGame() {
            gameOver = true;
            alert("🚗💥 You got hit! Game Over!");
            document.getElementById("horrorAudio").play();
        }

        function winGame() {
            gameOver = true;
            document.getElementById("winMessage").style.display = "block";
            alert("🎉 You won! Your secret code: " + winCode);
        }

        window.addEventListener("keydown", (e) => {
            if (gameOver) return;
            let nextX = frog.x, nextY = frog.y;

            if (e.key === "ArrowLeft" && frog.x > 20) nextX -= 30;
            if (e.key === "ArrowRight" && frog.x < canvas.width - 20) nextX += 30;
            if (e.key === "ArrowUp") nextY -= 30;
            if (e.key === "ArrowDown" && frog.y < canvas.height - 20) nextY += 30;

            frog.x = nextX;
            frog.y = nextY;
        });

        setInterval(createCar, 900); // Harder difficulty
        update();
    </script>
</body>
</html>

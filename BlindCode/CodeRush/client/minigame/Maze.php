<?php
session_start();

// Horror audio setup
$audioFolder = __DIR__ . "/hraudio";
$audioFiles = glob($audioFolder . "/*.mp3");
$selectedAudio = !empty($audioFiles) ? $audioFiles[array_rand($audioFiles)] : "";

// Function to get a unique winning code from codes.txt
function getUniqueWinningCode() {
    $codesFile = __DIR__ . "/codes.txt";
    $assignedCodesFile = __DIR__ . "/assigned_codes.txt";
    
    if (!file_exists($codesFile)) {
        return "XXXX0000"; // Default fallback code
    }
    
    $codes = file($codesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $assignedCodes = file_exists($assignedCodesFile) ? file($assignedCodesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    
    $availableCodes = array_diff($codes, $assignedCodes);
    if (empty($availableCodes)) {
        return "XXXX0000"; // No more available codes
    }
    
    $winningCode = array_shift($availableCodes);
    file_put_contents($assignedCodesFile, $winningCode . PHP_EOL, FILE_APPEND);
    return $winningCode;
}



// Assign the winning code for the session
if (!isset($_SESSION['winningCode'])) {
    $_SESSION['winningCode'] = getUniqueWinningCode();
}
$winningCode = $_SESSION['winningCode'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maze Escape - Horror</title>
    <style>
        body { text-align: center; background: black; color: white; }
        canvas { background: gray; border: 2px solid white; display: block; margin: auto; }
        .hidden { display: none; }
        .win-message {
            font-size: 20px;
            font-weight: bold;
            color: green;
            display: none;
        }
    </style>
</head>
<body>
    <h1>Escape the Maze! Avoid the Ghosts!</h1>
    <p>Use arrow keys to move | Reach the green square to win!</p>
    <canvas id="gameCanvas" width="500" height="500"></canvas>
    
    <audio id="horrorAudio" class="hidden">
        <source src="<?php echo htmlspecialchars($selectedAudio); ?>" type="audio/mp3">
    </audio>

    <p id="winMessage" class="win-message">🎉 You escaped! Your Secret Code: <span id="winningCode"><?php echo $winningCode; ?></span></p>

    <script>
        const canvas = document.getElementById("gameCanvas");
        const ctx = canvas.getContext("2d");
        let player = { x: 30, y: 30, size: 15 };
        let exit = { x: 450, y: 450, size: 20 };
        let ghosts = [
            { x: 100, y: 100, speed: 2, directionY: 1 },
            { x: 250, y: 250, speed: 3, directionX: -1 },
            { x: 350, y: 150, speed: 2.5, directionX: 1, directionY: 1 }
        ];
        let walls = [
            { x: 50, y: 50, width: 400, height: 10 },
            { x: 50, y: 200, width: 10, height: 250 },
            { x: 200, y: 100, width: 10, height: 300 },
            { x: 300, y: 250, width: 100, height: 10 },
            { x: 400, y: 350, width: 10, height: 100 },
            { x: 100, y: 150, width: 150, height: 10 }
        ];
        let gameOver = false;

        function draw() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = "blue";
            ctx.beginPath();
            ctx.arc(player.x, player.y, player.size, 0, Math.PI * 2);
            ctx.fill();
            ctx.fillStyle = "red";
            ghosts.forEach(g => {
                ctx.beginPath();
                ctx.moveTo(g.x, g.y);
                ctx.lineTo(g.x + 20, g.y + 30);
                ctx.lineTo(g.x - 20, g.y + 30);
                ctx.fill();
                g.y += g.speed * g.directionY || 0;
                g.x += g.speed * g.directionX || 0;
                if (g.y <= 0 || g.y >= canvas.height - 30) g.directionY *= -1;
                if (g.x <= 0 || g.x >= canvas.width - 20) g.directionX *= -1;
                if (collision(player, g)) endGame();
            });
            ctx.fillStyle = "white";
            walls.forEach(w => ctx.fillRect(w.x, w.y, w.width, w.height));
            ctx.fillStyle = "green";
            ctx.fillRect(exit.x, exit.y, exit.size, exit.size);
            if (collision(player, exit)) { winGame(); return; }
            if (!gameOver) requestAnimationFrame(draw);
        }

        function collision(a, b) {
            return a.x < b.x + 20 && a.x + player.size > b.x && a.y < b.y + 30 && a.y + player.size > b.y;
        }

        function endGame() {
            gameOver = true;
            alert("You got caught! Listen closely...");
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
            let nextX = player.x, nextY = player.y;
            if (e.key === "ArrowLeft") nextX -= 10;
            if (e.key === "ArrowRight") nextX += 10;
            if (e.key === "ArrowUp") nextY -= 10;
            if (e.key === "ArrowDown") nextY += 10;
            let hitWall = walls.some(w => nextX > w.x && nextX < w.x + w.width && nextY > w.y && nextY < w.y + w.height);
            if (!hitWall) {
                player.x = nextX;
                player.y = nextY;
            }
        });

        draw();
    </script>
</body>
</html>

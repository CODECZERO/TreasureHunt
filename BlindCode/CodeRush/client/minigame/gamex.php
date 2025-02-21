<?php
if (isset($_GET['get_code'])) {
    $codesFile = __DIR__ . "/codes.txt"; 
    $assignedFile = __DIR__ . "/assigned_codes.txt"; 

    // Read available codes
    $codes = file($codesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $assignedCodes = file($assignedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Find an unused code
    $unusedCodes = array_diff($codes, $assignedCodes);
    if (count($unusedCodes) > 0) {
        $assignedCode = array_shift($unusedCodes);

        // Save the assigned code
        file_put_contents($assignedFile, $assignedCode . PHP_EOL, FILE_APPEND);

        echo $assignedCode;
    } else {
        echo "No codes available!";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Catch the Star</title>
    <style>
        body {
            text-align: center;
            font-family: Arial, sans-serif;
            background: skyblue;
            overflow: hidden;
        }
        #gameContainer {
            position: relative;
            width: 400px;
            height: 500px;
            margin: auto;
            border: 2px solid black;
            background: lightyellow;
            overflow: hidden;
        }
        .star {
            position: absolute;
            width: 30px;
            height: 30px;
            background: gold;
            border-radius: 50%;
            top: 0;
        }
        .basket {
            position: absolute;
            width: 80px;
            height: 40px;
            background: brown;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 10px;
        }
        #winMessage {
            display: none;
            font-size: 24px;
            background: white;
            padding: 20px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <h1>⭐ Catch the Star ⭐</h1>
    <p>Move the basket with Left/Right Arrow Keys</p>
    <div id="gameContainer">
        <div class="basket" id="basket"></div>
        <div id="winMessage">
            🎉 You Win! 🎉 <br>
            Your Code: <span id="winCode"></span>
        </div>
    </div>
    <p>Stars Caught: <span id="score">0</span> / 10</p>
    
    <script>
        const gameContainer = document.getElementById("gameContainer");
        const basket = document.getElementById("basket");
        const winMessage = document.getElementById("winMessage");
        const scoreDisplay = document.getElementById("score");
        const winCode = document.getElementById("winCode");

        let score = 0;
        let basketPos = 160;
        let gameOver = false;

        function moveBasket(event) {
            if (gameOver) return;
            if (event.key === "ArrowLeft" && basketPos > 10) basketPos -= 20;
            if (event.key === "ArrowRight" && basketPos < 310) basketPos += 20;
            basket.style.left = basketPos + "px";
        }

        function createStar() {
            if (gameOver) return;
            let star = document.createElement("div");
            star.classList.add("star");
            let xPos = Math.random() * (gameContainer.clientWidth - 30);
            star.style.left = xPos + "px";
            gameContainer.appendChild(star);

            let fallInterval = setInterval(() => {
                let starTop = parseInt(star.style.top || 0);
                if (starTop > 470 && xPos > basketPos - 30 && xPos < basketPos + 80) {
                    clearInterval(fallInterval);
                    gameContainer.removeChild(star);
                    score++;
                    scoreDisplay.textContent = score;
                    if (score >= 10) winGame();
                } else if (starTop > 500) {
                    clearInterval(fallInterval);
                    gameContainer.removeChild(star);
                } else {
                    star.style.top = (starTop + 5) + "px";
                }
            }, 50);
        }

        function winGame() {
            gameOver = true;
            fetch("?get_code=1") // Fetch the code from the server
                .then(response => response.text())
                .then(code => {
                    winCode.textContent = code;
                    winMessage.style.display = "block";
                });
        }

        document.addEventListener("keydown", moveBasket);
        setInterval(createStar, 1500);
    </script>
</body>
</html>

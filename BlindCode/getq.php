<?php
session_start();

// Reset cookies if the server has restarted
if (!isset($_SESSION['initialized'])) {
    session_destroy();
    session_start();
    $_SESSION['initialized'] = true;

    setcookie('unlocked_levels', '', time() - 3600, "/");
}

// Assign unlocked levels
$unlocked_levels = isset($_COOKIE['unlocked_levels']) ? json_decode($_COOKIE['unlocked_levels'], true) : [1];

// Unlock new levels dynamically
if (isset($_GET['unlock']) && is_numeric($_GET['unlock'])) {
    $next_level = (int)$_GET['unlock'];
    if (!in_array($next_level, $unlocked_levels)) {
        $unlocked_levels[] = $next_level;
        setcookie('unlocked_levels', json_encode($unlocked_levels), time() + (86400 * 7), "/");
    }
    header("Location: index.php");
    exit;
}

// Reset competition
if (isset($_GET['reset'])) {
    setcookie('unlocked_levels', '', time() - 3600, "/");
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Blind Code - Random Questions</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f4f9;
        }
        .container {
            max-width: 600px;
            margin: auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        .level-btn {
            width: 100%;
            margin: 5px 0;
            font-size: 18px;
            font-weight: bold;
        }
        .locked {
            background-color: #6c757d !important;
            cursor: not-allowed !important;
        }
        .question-box {
            display: none;
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        .loader {
            display: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 4px solid rgba(0, 123, 255, 0.3);
            border-top-color: #007bff;
            animation: spin 0.8s linear infinite;
            margin: 10px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <script>
        async function fetchRandomQuestion(levelNumber) {
            const loader = document.getElementById("loader");
            const questionBox = document.getElementById("question-box");
            const questionText = document.getElementById("question-text");

            loader.style.display = "block";
            questionBox.style.display = "none";
            questionText.innerHTML = "";

            const level = `Level ${levelNumber}`; // Converts 1 → "Level 1"

            try {
                const requestBody = JSON.stringify({ level: level });

                const response = await fetch("http://localhost:4008/question/random", {
                    method: "POST",
                    headers: { 
                        "Content-Type": "application/json",
                        "Content-Length": requestBody.length
                    },
                    body: requestBody
                });

                console.log("Sent level:", level);

                const data = await response.json();
                loader.style.display = "none";

                if (response.ok && data.data) {
                    questionText.innerHTML = `📝 <strong>Question:</strong> ${data.data.Question}`;
                    questionBox.style.display = "block";
                } else {
                    questionText.innerHTML = `<span style="color:red;">❌ ${data.data.message || "No question found."}</span>`;
                    questionBox.style.display = "block";
                }
            } catch (error) {
                loader.style.display = "none";
                questionText.innerHTML = "<span style='color:red;'>⚠️ Error connecting to the server.</span>";
                questionBox.style.display = "block";
            }
        }
    </script>
</head>
<body>

    <div class="container">
        <h2>🔍 Blind Code - Get Random Questions</h2>

        <div class="d-flex flex-column">
            <?php for ($i = 1; $i <= 4; $i++): ?>
                <button class="btn btn-primary level-btn <?php echo in_array($i, $unlocked_levels) ? '' : 'locked'; ?>"
                    <?php echo in_array($i, $unlocked_levels) ? "onclick=\"fetchRandomQuestion($i)\"" : 'disabled'; ?>>
                    Get Question for Level <?php echo $i; ?>
                </button>
            <?php endfor; ?>
        </div>

        <div id="loader" class="loader"></div>
        <div id="question-box" class="question-box">
            <p id="question-text"></p>
        </div>

        <button onclick="window.location.href='?reset=true'" class="btn btn-danger mt-3">Reset Levels</button>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

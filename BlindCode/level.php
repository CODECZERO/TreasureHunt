<?php 
session_start();

// If the user is disqualified, redirect them to disqualified.php
if (isset($_SESSION['disqualified']) || isset($_COOKIE['disqualified'])) {
    header("Location: disqualified.php");
    exit();
}

// Initialize session-based timer if not set
if (!isset($_SESSION['end_time'])) {
    $_SESSION['end_time'] = time() + (90 * 60); // 1 hour 30 minutes
}

// Get remaining time
$time_remaining = $_SESSION['end_time'] - time();
if ($time_remaining <= 0) {
    $time_remaining = 0;
}

// Check if user is on Level 1 and has used their first chance
$level = isset($_GET['level']) ? (int)$_GET['level'] : 1;
$first_attempt_used = isset($_SESSION['level1_first_attempt']) ? $_SESSION['level1_first_attempt'] : false;
$second_chance_used = isset($_SESSION['level1_second_attempt']) ? $_SESSION['level1_second_attempt'] : false;

// Handle second chance reduction logic
if (isset($_GET['second_chance']) && $level == 1 && !$second_chance_used) {
    $_SESSION['end_time'] -= 1200; // Reduce 20 minutes (1200 seconds)
    $_SESSION['level1_second_attempt'] = true;
    header("Location: level.php?level=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo "Level " . htmlspecialchars($level); ?> - Blind Code</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        body { background: #f8f9fa; }
        .card { margin-top: 50px; }
        .card-header { background-color: #343a40; color: white; }
        .btn-submit { background-color: #28a745; color: white; }
        .btn-submit:hover { background-color: #218838; }
        .btn-secondary { background-color: #ffcc00; color: black; }
        .btn-secondary:hover { background-color: #e6b800; }
        .back-link { margin-top: 20px; }
        textarea {
    resize: vertical;
    user-select: none; /* Still prevents text selection */
    pointer-events: auto; /* Allows typing */
}

    </style>
</head>
<body>

    <div class="container">
        <div class="card shadow">
            <div class="card-header text-center">
                <h2>Level <?php echo $level; ?></h2>
            </div>
            <div class="card-body">
                <p class="card-text">
                    <strong>Your Question:</strong> <span id="question">Loading...</span>
                </p>

                <form id="code-form">
                    <div class="mb-3">
                        <textarea name="solution" class="form-control" rows="10" placeholder="Write your code here..." required onpaste="return false;"></textarea>
                    </div>
                    <input type="hidden" name="level" id="hidden-level" value="<?php echo $level; ?>">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-submit" <?php echo ($level == 1 && $first_attempt_used && !$second_chance_used) ? 'disabled' : ''; ?>>
                            Submit Code
                        </button>
                    </div>
                </form>

                <?php if ($level == 1 && $first_attempt_used && !$second_chance_used): ?>
                <div class="d-grid gap-2 mt-3">
                    <a href="level.php?level=1&second_chance=1" class="btn btn-secondary">Get Second Chance (-20 min)</a>
                </div>
                <?php endif; ?>

                <div class="back-link text-center mt-4">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Go Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let tabSwitchCount = localStorage.getItem("tabSwitchCount") || 0;
        const maxTabSwitches = 3;

        document.addEventListener("visibilitychange", function () {
            if (document.hidden) {
                tabSwitchCount++;
                localStorage.setItem("tabSwitchCount", tabSwitchCount);

                if (tabSwitchCount >= maxTabSwitches) {
                    alert("❌ You switched tabs too many times! You are disqualified.");
                    fetch("disqualify.php").then(() => {
                        window.location.href = "disqualified.php";
                    });
                }
            }
        });

        document.addEventListener("DOMContentLoaded", async function () {
            const questionText = document.getElementById("question");
            const level = parseInt(document.getElementById("hidden-level").value);

            const storedQuestion = sessionStorage.getItem("question_level_" + level);
            if (storedQuestion) {
                questionText.innerHTML = storedQuestion; 
            } else {
                try {
                    const response = await fetch("http://localhost:4008/question/random", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ level: `Level ${level}` })
                    });

                    const data = await response.json();
                    if (response.ok && data.data && data.data.Question) {
                        questionText.innerHTML = data.data.Question;
                        sessionStorage.setItem("question_level_" + level, data.data.Question);
                    } else {
                        questionText.innerHTML = `<span style="color:red;">❌ ${data.message || "No question found."}</span>`;
                    }
                } catch (error) {
                    console.error("Fetch error:", error);
                    questionText.innerHTML = "<span style='color:red;'>⚠️ Error connecting to the server.</span>";
                }
            }
        });

        const ws = new WebSocket("ws://localhost:3000/");
        ws.onopen = () => console.log("Connected to WebSocket server");
        ws.onerror = (error) => console.error("WebSocket error:", error);
        
        ws.onmessage = (event) => {
            try {
                const response = JSON.parse(event.data);
                if (response.aiResult && response.aiResult.length > 0) {
                    const textResult = response.aiResult[0]?.content?.parts[0]?.text.trim();
                    if (textResult.toLowerCase() === "true") {
                        alert("✅ Correct! Moving to the next level.");
                        window.location.href = `level.php?level=${parseInt(document.getElementById("hidden-level").value) + 1}`;
                    } else {
                        alert("❌ Incorrect answer. Try again.");
                        if (<?php echo $level; ?> === 1) {
                            fetch("level.php?level=1&first_attempt=1").then(() => {
                                location.reload();
                            });
                        }
                    }
                } else {
                    alert("⚠️ Invalid response from server.");
                }
            } catch (error) {
                console.error("Error parsing WebSocket response:", error);
                alert("⚠️ Error processing server response.");
            }
        };

        document.getElementById("code-form").addEventListener("submit", function (event) {
            event.preventDefault();
            const question = document.getElementById("question").innerText;
            const answer = document.querySelector("textarea").value.trim();
            if (!answer) {
                alert("Please enter an answer before submitting.");
                return;
            }
            
            const payload = {
                "MessageId": "MSG_" + Date.now(),
                "typeOfMessage": "SEND_MESSAGE",
                "roomName": "Ankit",
                "userId": "user_5678",
                "question": question,
                "answer": answer
            };
            
            ws.send(JSON.stringify(payload));
            alert("Answer submitted for verification...");
        });
    </script>
</body>
</html>

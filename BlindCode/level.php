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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo "Level " . htmlspecialchars($level); ?> - Blind Code</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <style>
    body { background: #f8f9fa; }
    /* Card styling for a split view */
    .card { margin-top: 50px; overflow: hidden; border-radius: 0.5rem; }
    /* Upper section: Question area */
    .question-section { background-color: #343a40; color: #fff; padding: 20px; }
    .question-section h2 { margin-bottom: 15px; }
    /* Lower section: Code editor area */
    .code-editor-section { background-color: #ffffff; padding: 20px; }
    textarea.code-editor {
      font-family: 'Courier New', Courier, monospace;
      background-color: #f1f1f1;
      border: 1px solid #ccc;
      border-radius: 4px;
      width: 100%;
      resize: vertical;
      padding: 10px;
    }
    .btn-submit { background-color: #28a745; color: #fff; border: none; }
    .btn-submit:hover { background-color: #218838; }
    .btn-secondary { background-color: #ffcc00; color: black; border: none; }
    .btn-secondary:hover { background-color: #e6b800; }
    .back-link { margin-top: 20px; }
  </style>
</head>
<body>
  <div class="container my-4">
    <div class="card shadow">
      <!-- Upper Section: Question -->
      <div class="question-section">
        <h2 class="text-center">Level <?php echo $level; ?></h2>
        <p><strong>Your Question:</strong></p>
        <div id="question">Loading...</div>
      </div>
      <!-- Lower Section: Code Editor -->
      <div class="code-editor-section">
        <form id="code-form">
          <div class="mb-3">
            <textarea name="solution" class="form-control code-editor" rows="10" placeholder="Write your code here..." required onpaste="return false;"></textarea>
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

        <div class="back-link text-center mt-3">
          <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Go Back
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Utility function to get a cookie's value by name.
    function getCookie(name) {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop().split(';').shift();
      return null;
    }

    // Track tab switching for disqualification logic.
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

    // Load the question (using sessionStorage to cache it per level)
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

    // WebSocket connection for receiving AI verification results
    const ws = new WebSocket("ws://localhost:3000/");
    ws.onopen = () => console.log("Connected to WebSocket server");
    ws.onerror = (error) => console.error("WebSocket error:", error);
    
    ws.onmessage = (event) => {
      try {
        const response = JSON.parse(event.data);
        if (response.aiResult && response.aiResult.length > 0) {
          const textResult = response.aiResult[0]?.content?.parts[0]?.text.trim();
          if (textResult.toLowerCase() === "true") {
            // Correct answer - update level on the server via a PUT request.
            (async () => {
              const teamName = getCookie('TeamName');
              const currentLevel = parseInt(document.getElementById("hidden-level").value);
              const apiUrl = "http://localhost:4008/techHub/addLevel";
              try {
                const updateResponse = await fetch(apiUrl, {
                  method: "PUT",
                  headers: {
                    "Content-Type": "application/json"
                  },
                  body: JSON.stringify({ TeamName: teamName, level: currentLevel })
                });
                if (!updateResponse.ok) {
                  throw new Error("Failed to update level");
                }
              } catch (error) {
                console.error("Error updating level:", error);
              }
              alert("✅ Correct! Moving to the next level.");
              window.location.href = `level.php?level=${currentLevel + 1}`;
            })();
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

    // Handle form submission
    document.getElementById("code-form").addEventListener("submit", function (event) {
      event.preventDefault();
      const question = document.getElementById("question").innerText;
      const answer = document.querySelector("textarea").value.trim();
      if (!answer) {
        alert("Please enter an answer before submitting.");
        return;
      }
      // Use TeamName from cookie as the room name.
      const teamName = getCookie('TeamName');
      const payload = {
        "MessageId": "MSG_" + Date.now(),
        "typeOfMessage": "SEND_MESSAGE",
        "roomName": teamName,
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

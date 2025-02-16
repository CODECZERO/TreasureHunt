<?php
// Start session
session_start();

// Reset cookies if the server has restarted
if (!isset($_SESSION['initialized'])) {
    session_destroy();  // Clear session data
    session_start();  // Restart session
    $_SESSION['initialized'] = true;

    // Reset all cookies
    setcookie('time_remaining', '', time() - 3600, "/");
    setcookie('group_id', '', time() - 3600, "/");
    setcookie('unlocked_levels', '', time() - 3600, "/");
}

// Set a 2-hour timer using cookies if not set
if (!isset($_COOKIE['time_remaining'])) {
    $expiry_time = time() + (2 * 60 * 60);
    setcookie('time_remaining', $expiry_time, $expiry_time, "/"); // Expires in 2 hours
    $_COOKIE['time_remaining'] = $expiry_time;
}

// Get remaining time (in seconds)
$time_remaining = $_COOKIE['time_remaining'] - time();
if ($time_remaining <= 0) {
    $time_remaining = 0;
}

// Assign random group ID if not set
if (!isset($_COOKIE['group_id'])) {
    $group_id = rand(1, 3);
    setcookie('group_id', $group_id, time() + (86400 * 7), "/"); // 7-day expiration
} else {
    $group_id = $_COOKIE['group_id'];
}

// Question pool for each level
$questions = [
    1 => [
        1 => "Implement Bubble Sort.",
        2 => "Implement Binary Search.",
        3 => "Implement Fibonacci Series."
    ],
    2 => [
        1 => "Fix syntax errors in the given 400-line code.",
        2 => "Find and correct the function that calculates factorial."
    ],
    3 => [
        1 => "Implement a Stack using only one variable.",
        2 => "Implement a Queue using only one variable."
    ],
    4 => [
        1 => "Write a program using LaTeX to solve a quadratic equation.",
        2 => "Create a simple calculator using only CSS."
    ]
];

// Track unlocked levels
$unlocked_levels = isset($_COOKIE['unlocked_levels']) ? json_decode($_COOKIE['unlocked_levels'], true) : [1];

// Unlock next level
if (isset($_GET['unlock']) && is_numeric($_GET['unlock'])) {
    $next_level = (int)$_GET['unlock'];
    if (!in_array($next_level, $unlocked_levels)) {
        $unlocked_levels[] = $next_level;
        setcookie('unlocked_levels', json_encode($unlocked_levels), time() + (86400 * 7), "/");
    }
    header("Location: index.php");
    exit;
}

// Reset the competition (For testing)
if (isset($_GET['reset'])) {
    setcookie('time_remaining', '', time() - 3600, "/");
    setcookie('group_id', '', time() - 3600, "/");
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
  <title>Blind Code Hackathon</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f8f9fa;
    }
    .jumbotron {
      background: rgb(10, 11, 12);
      color: #fff;
      padding: 2rem 1rem;
      border-radius: 0.3rem;
      margin-bottom: 30px;
    }
    .jumbotron h1 {
      font-size: 2.5rem;
    }
    .timer {
      font-size: 1.5rem;
      font-weight: bold;
      color: #dc3545;
    }
    .level-btn {
      min-width: 150px;
      margin: 10px;
    }
    .locked {
      background-color: #6c757d !important;
      cursor: not-allowed !important;
    }
    .reset-btn {
      margin-top: 20px;
    }
  </style>
  <script>
    // Improved Timer: Store absolute end time in localStorage.
    function startTimer(endTime) {
      function updateTimer() {
        // Get current time in seconds.
        const currentTime = Math.floor(Date.now() / 1000);
        let remaining = endTime - currentTime;
        if (remaining < 0) {
          remaining = 0;
        }
        // Compute hours, minutes, and seconds.
        const hours = Math.floor(remaining / 3600);
        const minutes = Math.floor((remaining % 3600) / 60);
        const seconds = remaining % 60;
        
        // Format time as HH:MM:SS.
        document.getElementById("timer").textContent =
          (hours < 10 ? "0" : "") + hours + ":" +
          (minutes < 10 ? "0" : "") + minutes + ":" +
          (seconds < 10 ? "0" : "") + seconds;
          
        // When time is up, stop the timer and show a message.
        if (remaining <= 0) {
          clearInterval(timerInterval);
          document.getElementById("timer").textContent = "Time Over!";
          document.getElementById("all-levels").style.display = "none";
          document.getElementById("message").classList.remove("d-none");
          localStorage.removeItem("endTime");
        }
      }
      updateTimer();
      const timerInterval = setInterval(updateTimer, 1000);
    }

    window.onload = function () {
      // Check if an absolute end time is stored in localStorage.
      let storedEndTime = localStorage.getItem("endTime");
      if (!storedEndTime) {
        // If not, set it based on the PHP-calculated remaining time.
        const expiry = <?php echo $time_remaining; ?>;
        const endTime = Math.floor(Date.now() / 1000) + expiry;
        localStorage.setItem("endTime", endTime);
        storedEndTime = endTime;
      }
      startTimer(parseInt(storedEndTime));
    };
  </script>
</head>
<body>
  <div class="container">
    <!-- Header Section -->
    <div class="jumbotron text-center">
      <h1>Blind Code Hackathon</h1>
      <p class="timer" id="timer"></p>
    </div>

    <!-- Level Buttons -->
    <div id="all-levels" class="d-flex flex-wrap justify-content-center">
      <?php for ($i = 1; $i <= 4; $i++): ?>
      <button class="btn btn-primary level-btn <?php echo in_array($i, $unlocked_levels) ? '' : 'locked'; ?>"
        <?php echo in_array($i, $unlocked_levels) ? "onclick=\"window.location.href='level.php?level=$i'\"" : 'disabled'; ?>>
        Level <?php echo $i; ?>
      </button>
      <?php endfor; ?>
    </div>

    <!-- Time Over Message -->
    <div id="message" class="alert alert-danger text-center d-none mt-3">
      Time is up! The contest has ended.
    </div>

    <!-- Reset Button -->
    <div class="text-center reset-btn">
      <button onclick="window.location.href='?reset=true'" class="btn btn-warning">
        Reset Competition
      </button>
    </div>
  </div>
  
  <!-- Bootstrap Bundle JS (includes Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
        
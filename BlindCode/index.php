<?php
// Start session
session_start();

// Set session timer to 90 minutes if not set
if (!isset($_SESSION['end_time'])) {
    $_SESSION['end_time'] = time() + (90 * 60); // 1 hour 30 minutes
}

// Get remaining time
$time_remaining = $_SESSION['end_time'] - time();
if ($time_remaining <= 0) {
    $time_remaining = 0;
}

// Prevent access if time is over
if ($time_remaining <= 0) {
    header("Location: time_over.php");
    exit();
}

// Assign random group ID if not set
if (!isset($_COOKIE['group_id'])) {
    $group_id = rand(1, 3);
    setcookie('group_id', $group_id, time() + (86400 * 7), "/"); // 7-day expiration
} else {
    $group_id = $_COOKIE['group_id'];
}

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
    exit();
}

// Reset the competition (For testing)
if (isset($_GET['reset'])) {
    session_destroy();
    setcookie('group_id', '', time() - 3600, "/");
    setcookie('unlocked_levels', '', time() - 3600, "/");
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Blind Code Hackathon</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body { font-family: Arial, sans-serif; background: #f8f9fa; }
    .jumbotron { background: rgb(10, 11, 12); color: #fff; padding: 2rem; border-radius: 0.3rem; margin-bottom: 30px; }
    .timer { font-size: 1.5rem; font-weight: bold; color: #dc3545; }
    .level-btn { min-width: 150px; margin: 10px; }
    .locked { background-color: #6c757d !important; cursor: not-allowed !important; }
    .reset-btn { margin-top: 20px; }
  </style>
  <script>
    function startTimer(remainingTime) {
      function updateTimer() {
        let remaining = remainingTime - Math.floor(Date.now() / 1000);
        if (remaining < 0) remaining = 0;

        let hours = Math.floor(remaining / 3600);
        let minutes = Math.floor((remaining % 3600) / 60);
        let seconds = remaining % 60;

        document.getElementById("timer").textContent =
          (hours < 10 ? "0" : "") + hours + ":" +
          (minutes < 10 ? "0" : "") + minutes + ":" +
          (seconds < 10 ? "0" : "") + seconds;

        if (remaining <= 0) {
          clearInterval(timerInterval);
          document.getElementById("timer").textContent = "Time Over!";
          document.getElementById("all-levels").style.display = "none";
          document.getElementById("message").classList.remove("d-none");

          // Redirect to time_over page
          setTimeout(() => {
            window.location.href = "time_over.php";
          }, 3000);
        }
      }
      updateTimer();
      let timerInterval = setInterval(updateTimer, 1000);
    }

    window.onload = function () {
      const expiryTime = <?php echo $_SESSION['end_time']; ?>;
      startTimer(expiryTime);
    };
  </script>
</head>
<body>
  <div class="container">
    <div class="jumbotron text-center">
      <h1>Blind Code Hackathon</h1>
      <p class="timer" id="timer"></p>
    </div>

    <div id="all-levels" class="d-flex flex-wrap justify-content-center">
      <?php for ($i = 1; $i <= 4; $i++): ?>
      <button class="btn btn-primary level-btn <?php echo ($time_remaining > 0 && in_array($i, $unlocked_levels)) ? '' : 'locked'; ?>"
        <?php echo ($time_remaining > 0 && in_array($i, $unlocked_levels)) ? "onclick=\"window.location.href='level.php?level=$i'\"" : 'disabled'; ?>>
        Level <?php echo $i; ?>
      </button>
      <?php endfor; ?>
    </div>

    <div id="message" class="alert alert-danger text-center d-none mt-3">
      Time is up! The contest has ended.
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();

// Ensure data is received
if (!isset($_POST['solution']) || !isset($_POST['level'])) {
    die("Invalid submission.");
}

$level = (int) $_POST['level'];
$solution = trim($_POST['solution']);
$group_id = isset($_COOKIE['group_id']) ? (int) $_COOKIE['group_id'] : 1;

// Load the expected answers (Example test cases)
$answers = [
    1 => [
        1 => "Bubble Sort",
        2 => "Binary Search",
        3 => "Fibonacci Series",
        4 => "GCD Using Recursion",
        5 => "Merge Sort Without Arrays"
    ],
    2 => "Fixed syntax errors", // Just checking if they submitted something
    3 => "Only one variable used",
    4 => "Non-programming language"
];

// Function to check if code contains only one variable
function check_one_variable($code) {
    $pattern = '/\b(int|float|string|var|let|const|char|double|boolean|array|list|dict|map|struct|class|object)\s+(\w+)\b/';
    preg_match_all($pattern, $code, $matches);
    return count($matches[2]) <= 1; // Should contain only one variable
}

// Function to determine pass/fail
function check_submission($level, $solution) {
    global $answers;

    if ($level == 1) {
        // Just checking if they implemented the correct algorithm
        foreach ($answers[1] as $key => $expected) {
            if (stripos($solution, $expected) !== false) {
                return true;
            }
        }
        return false;
    } elseif ($level == 2) {
        return strlen($solution) > 20; // Ensure they at least tried to fix errors
    } elseif ($level == 3) {
        return check_one_variable($solution); // Check single variable constraint
    } elseif ($level == 4) {
        return strlen($solution) > 5; // Ensure they at least wrote something
    }
    return false;
}

// Evaluate submission
if (check_submission($level, $solution)) {
    // Unlock next level
    $unlocked_levels = isset($_COOKIE['unlocked_levels']) ? json_decode($_COOKIE['unlocked_levels'], true) : [1];
    $next_level = $level + 1;
    
    if (!in_array($next_level, $unlocked_levels) && $next_level <= 4) {
        $unlocked_levels[] = $next_level;
        setcookie('unlocked_levels', json_encode($unlocked_levels), time() + (86400 * 7), "/");
    }
    
    echo "<h1>✅ Congratulations! You passed Level $level.</h1>";
    if ($next_level <= 4) {
        echo "<p>Level $next_level is now unlocked!</p>";
    }
} else {
    echo "<h1>❌ Incorrect solution. You have been disqualified!</h1>";
}

// Back button
echo "<br><a href='index.php'>Go Back to Dashboard</a>";
?>

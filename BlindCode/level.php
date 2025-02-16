<?php
session_start();

// Validate the level number
if (!isset($_GET['level']) || !in_array($_GET['level'], [1, 2, 3, 4])) {
    die("Invalid level selected!");
}

$level = (int) $_GET['level'];

// Get the user's group from cookies
$group_id = isset($_COOKIE['group_id']) ? (int) $_COOKIE['group_id'] : 1;

// Question pool (same as in index.php)
$questions = [
    1 => [
        1 => "Implement Bubble Sort.",
        2 => "Implement Binary Search.",
        3 => "Implement Fibonacci Series.",
        4 => "Find the GCD of two numbers using recursion.",
        5 => "Implement Merge Sort without using arrays."
    ],
    2 => [
        1 => "Fix the syntax errors in the given 400-line code.",
        2 => "Find and correct the function that calculates factorial.",
        3 => "Debug and extract the quicksort function.",
        4 => "Locate and correct the function that performs matrix multiplication.",
        5 => "Identify and fix the incorrect function that reverses a string."
    ],
    3 => [
        1 => "Implement a Stack using only one variable.",
        2 => "Implement a Queue using only one variable.",
        3 => "Implement a Linked List using only one variable.",
        4 => "Create a Priority Queue using only one variable.",
        5 => "Implement a HashMap using only one variable."
    ],
    4 => [
        1 => "Write a program using LaTeX to solve a quadratic equation.",
        2 => "Create a simple calculator using only CSS.",
        3 => "Write a SQL query that simulates a loop.",
        4 => "Write a recipe in YAML that functions as a program.",
        5 => "Design a state machine using Excel formulas."
    ]
];

// Select a random question from the participant's group
$question = $questions[$level][$group_id];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level <?php echo $level; ?> - Blind Code</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background: #f8f9fa;
        }
        .card {
            margin-top: 50px;
        }
        .card-header {
            background-color: #343a40;
            color: white;
        }
        .btn-submit {
            background-color: #28a745;
            color: white;
        }
        .btn-submit:hover {
            background-color: #218838;
        }
        .back-link {
            margin-top: 20px;
        }
        textarea {
            resize: vertical;
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
                    <strong>Your Question:</strong> <?php echo $question; ?>
                </p>
                <form action="submit.php" method="post">
                    <div class="mb-3">
                        <textarea name="solution" class="form-control" rows="10" placeholder="Write your code here..." required></textarea>
                    </div>
                    <input type="hidden" name="level" value="<?php echo $level; ?>">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-submit">Submit Code</button>
                    </div>
                </form>
                <!-- Improved Go Back Button -->
                <div class="back-link text-center mt-4">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Go Back
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

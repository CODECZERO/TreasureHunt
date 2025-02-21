<?php
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["code"])) {
    $code = trim($_POST["code"]);
    $assignedCodesFile = __DIR__ . "/assigned_codes.txt";

    // Prevent duplicate entries
    $assignedCodes = file_exists($assignedCodesFile) ? file($assignedCodesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    if (!in_array($code, $assignedCodes)) {
        file_put_contents($assignedCodesFile, $code . PHP_EOL, FILE_APPEND);
    }
}
?>

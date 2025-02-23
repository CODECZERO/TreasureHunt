<?php
// Set the server version. Update this value on each server restart.
$serverVersion = "0000001"; // Example value: update manually or generate on startup

// List of available codes
$codes = ["SDVL6105", "PTWE1885", "YDHS8009"];

// File to store allocated codes (adjust the path as needed)
$storageFile = "allocated_codes.json";

// Check if the user (device) has already claimed a code via cookie,
// but only if the cookie's server version matches the current version.
if (isset($_COOKIE['claimed_code'])) {
    $cookieValue = $_COOKIE['claimed_code'];
    $parts = explode("|", $cookieValue);
    if (count($parts) === 2 && $parts[0] === $serverVersion) {
        echo "You have already claimed your secret code: " . $parts[1];
        exit();
    }
}

// Read allocated codes from file or initialize an empty array
if (file_exists($storageFile)) {
    $allocated = json_decode(file_get_contents($storageFile), true);
    if (!is_array($allocated)) {
        $allocated = [];
    }
} else {
    $allocated = [];
}

// Determine available codes by subtracting allocated codes
$available = array_diff($codes, $allocated);

if (count($available) > 0) {
    // Randomly pick one available code
    $randomKey = array_rand($available);
    $selectedCode = $available[$randomKey];

    // Allocate the code: add it to the allocated array
    $allocated[] = $selectedCode;

    // Save the updated allocated codes back to the file
    file_put_contents($storageFile, json_encode($allocated));

    // Set a cookie that includes the server version and the claimed key.
    // Cookie is valid for 30 days.
    setcookie("claimed_code", $serverVersion . "|" . $selectedCode, time() + (86400 * 30), "/");

    // Return the allocated code to the user
    echo "Your secret code: " . $selectedCode;
} else {
    // All codes have been claimed
    echo "You're late to claim!";
}
?>

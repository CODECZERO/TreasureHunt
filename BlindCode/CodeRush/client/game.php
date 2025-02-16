<?php
// Define the folder where PHP files are stored
$folder = 'minigame/';

// Get all PHP files in the folder
$files = glob($folder . '*.php');

// Check if there are any PHP files available
if ($files) {
    // Pick a random file
    $randomFile = $files[array_rand($files)];
    
    // Include the selected PHP file
    include $randomFile;
} else {
    echo "No PHP files found in the minigame folder.";
}
?>

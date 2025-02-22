<?php
session_start();

$imageDir = 'image/';
$images = glob($imageDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);

if (!$images) {
    die('No images found in the folder.');
}

// Load assigned images from a temporary file
$tempFile = 'assigned_images.json';
$assignedImages = file_exists($tempFile) ? json_decode(file_get_contents($tempFile), true) : [];

// Remove expired assignments (older than 10 minutes)
$assignedImages = array_filter($assignedImages, function ($timestamp) {
    return (time() - $timestamp) < 600;
});

// Find an unassigned image
$availableImages = array_diff($images, array_keys($assignedImages));

if (empty($availableImages)) {
    // If all images are assigned, allow reuse by clearing the list
    $assignedImages = [];
    $availableImages = $images;
}

// Select a random available image
$selectedImage = $availableImages[array_rand($availableImages)];
$assignedImages[$selectedImage] = time();

// Save updated assignments
file_put_contents($tempFile, json_encode($assignedImages));

// Store the assigned image in a session to maintain consistency for the user
$_SESSION['assigned_image'] = $selectedImage;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Random Image</title>
</head>
<body>
    <h1>Your Unique Image</h1>
    <img src="<?php echo htmlspecialchars($selectedImage); ?>" alt="Random Image" width="500">
</body>
</html>

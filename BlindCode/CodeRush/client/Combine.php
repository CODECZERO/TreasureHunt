<?php
session_start();

$audioDir = 'revImg/';
$imageDir = 'revImg/';
$audios = glob($audioDir . '*.{mp3,wav,ogg}', GLOB_BRACE);
$images = glob($imageDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);

if (!$audios || !$images) {
    die('No audio or image files found in the folders.');
}

// Load assigned audios and images from a temporary file
$tempFile = 'assigned_files.json';
$assignedFiles = file_exists($tempFile) ? json_decode(file_get_contents($tempFile), true) : [];

// Remove expired assignments (older than 10 minutes)
$assignedFiles = array_filter($assignedFiles, function ($data) {
    return (time() - $data['timestamp']) < 600;
});

// Extract assigned audios and images separately
$assignedAudios = array_column($assignedFiles, 'audio');
$assignedImages = array_column($assignedFiles, 'image');

// Find unassigned audio and image
$availableAudios = array_diff($audios, $assignedAudios);
$availableImages = array_diff($images, $assignedImages);

// If all audios or images are assigned, allow reuse by clearing the list
if (empty($availableAudios)) {
    $availableAudios = $audios;
}
if (empty($availableImages)) {
    $availableImages = $images;
}

// Select a random available audio and image
$selectedAudio = $availableAudios[array_rand($availableAudios)];
$selectedImage = $availableImages[array_rand($availableImages)];

// Store the assignment with timestamp
$assignedFiles[session_id()] = [
    'audio' => $selectedAudio,
    'image' => $selectedImage,
    'timestamp' => time()
];

// Save updated assignments
file_put_contents($tempFile, json_encode($assignedFiles));

// Store the assigned audio and image in session
$_SESSION['assigned_audio'] = $selectedAudio;
$_SESSION['assigned_image'] = $selectedImage;

// Handle download request
if (isset($_GET['download'])) {
    header('Content-Type: image/jpeg');
    header('Content-Disposition: attachment; filename="downloaded_image.jpg"');
    readfile($selectedImage);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audio</title>
</head>
<body>
    <h1>Listen to Your Audio</h1>
    <audio controls>
        <source src="<?php echo htmlspecialchars($selectedAudio); ?>" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
    <br>
    <a href="?download=true">Download Audio</a>
</body>
</html>

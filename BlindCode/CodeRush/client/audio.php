<?php
session_start();

$audioDir = 'revaudio/';
$audios = glob($audioDir . '*.{mp3,wav,ogg}', GLOB_BRACE);

if (!$audios) {
    die('No audio files found in the folder.');
}

// Load assigned audios from a temporary file
$tempFile = 'assigned_audios.json';
$assignedAudios = file_exists($tempFile) ? json_decode(file_get_contents($tempFile), true) : [];

// Remove expired assignments (older than 10 minutes)
$assignedAudios = array_filter($assignedAudios, function ($timestamp) {
    return (time() - $timestamp) < 600;
});

// Find an unassigned audio
$availableAudios = array_diff($audios, array_keys($assignedAudios));

if (empty($availableAudios)) {
    // If all audios are assigned, allow reuse by clearing the list
    $assignedAudios = [];
    $availableAudios = $audios;
}

// Select a random available audio
$selectedAudio = $availableAudios[array_rand($availableAudios)];
$assignedAudios[$selectedAudio] = time();

// Save updated assignments
file_put_contents($tempFile, json_encode($assignedAudios));

// Store the assigned audio in a session to maintain consistency for the user
$_SESSION['assigned_audio'] = $selectedAudio;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Random Audio</title>
</head>
<body>
<script>
        // Example Base64-encoded URL
        let base64Url = "aHR0cHM6Ly9leGFtcGxlLmNvbS9mbGFnLnR4dA==";
        
        // Decode Base64
        let decodedUrl = atob(base64Url);
        
        // Print decoded URL in the console
        console.log("Decoded URL:", decodedUrl);
    </script>
    <h1>Your Unique Audio</h1>
    <audio controls>
        <source src="<?php echo htmlspecialchars($selectedAudio); ?>" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
</body>
</html>

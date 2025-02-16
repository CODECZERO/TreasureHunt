<?php
session_start();

// Folder containing QR codes
$qrCodeDir = 'qrcode/';
$qrCodes = glob($qrCodeDir . '*.{png,jpg,jpeg,gif}', GLOB_BRACE);

if (!$qrCodes) {
    die('No QR codes found in the folder.');
}

// Select a random QR code
$selectedQRCode = $qrCodes[array_rand($qrCodes)];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Random QR Code</title>
    <style>
        body {
            text-align: center;
            font-family: Arial, sans-serif;
            margin-top: 50px;
            background-color: #f4f4f4;
            animation: fadeIn 1s ease-in;
        }
        .qr-container {
            display: inline-block;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            animation: slideIn 0.5s ease-in-out;
        }
        img {
            width: 200px;
            height: auto;
            border-radius: 10px;
            margin-top: 20px;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="qr-container">
        <h2>📷 Random QR Code</h2>
        <img src="<?php echo htmlspecialchars($selectedQRCode); ?>" alt="QR Code">
    </div>
</body>
</html>

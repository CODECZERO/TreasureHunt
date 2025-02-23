<?php
session_start();
session_destroy(); // Destroy session to prevent re-access

echo "<h1>Time Over! ⏳</h1>";
echo "<p>The competition has ended. Thanks for participating!</p>";
?>
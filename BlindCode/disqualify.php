<?php
session_start();
$_SESSION['disqualified'] = true;

// Set a permanent cookie (expires in 1 year)
setcookie("disqualified", "true", time() + (365 * 24 * 60 * 60), "/"); 

echo "Disqualified";
?>
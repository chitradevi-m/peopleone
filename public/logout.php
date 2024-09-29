<?php
session_start();
$_SESSION['expiration'] = time() + 600; // Set expiration time to 10 minutes from now
session_destroy();
header("location: login.php");
exit;
?>
<?php
session_start();

$_SESSION = [];
session_destroy();

session_start();
$_SESSION["logout_message"] = "You have been logged out successfully.";

header("Location: login.php");
exit();
?>
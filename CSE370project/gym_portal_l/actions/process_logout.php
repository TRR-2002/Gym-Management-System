<?php

session_start();

$_SESSION = array();

session_destroy();


$logoutMessage = "You have been logged out successfully.";














$encodedMessage = urlencode($logoutMessage);


header("Location: ../login.php?success_msg=" . $encodedMessage);

exit();
?>
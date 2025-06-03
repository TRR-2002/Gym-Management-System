<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'member') {

    $errorMessage = "Access Denied. Please log in as a member to view this page.";

    $encodedErrorMessage = urlencode($errorMessage);


    header("Location: login.php?error_msg=" . $encodedErrorMessage);

    exit();
}

?>
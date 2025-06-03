<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['admin_id'])) {

    header("Location: ../admin_login.php?error=auth_check_failed_no_session"); 
    exit();
}


require_once __DIR__ . '/db_connect_pdo.php'; 


if (!isset($pdo) || !$pdo instanceof PDO) {
   
    session_unset();
    session_destroy();

    error_log("CRITICAL: PDO object not available in auth_check_admin.php after including db_connect.php. Check db_connect.php.");

    header("Location: ../admin_login.php?error=db_config_issue_auth");

    exit();
}

try {
    $admin_id_to_check = $_SESSION['admin_id']; 

    $stmt = $pdo->prepare("SELECT w.W_id FROM Workers w JOIN Admin a ON w.W_id = a.A_id WHERE w.W_id = :admin_id");
    $stmt->bindParam(':admin_id', $admin_id_to_check, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->fetch() === false) {

        session_unset();
        session_destroy();
        header("Location: ../admin_login.php?error=admin_session_invalidated");
        exit();
    }

} catch (PDOException $e) {

    error_log("Database error during admin session validation: " . $e->getMessage());

}
?>

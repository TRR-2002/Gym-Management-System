<?php


session_start();

require_once '../includes/auth_check_member.php'; 
require_once '../includes/db_connect.php';     


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $m_id = $_SESSION['user_id'];

    $package_type_selected = trim($_POST['package_type'] ?? '');


    $allowed_packages = ['Basic Monthly', 'Premium Monthly', 'Premium Annual', 'Inactive'];

    if (empty($package_type_selected) || !in_array($package_type_selected, $allowed_packages)) {
        $error_msg = "Invalid subscription package selected.";
    
        header("Location: ../member_packages.php?error_msg=" . urlencode($error_msg));

        exit();
    }

    $redirect_url = ''; 
    $final_message = '';
    $message_category = ''; 

    $sql_update_subscription = "UPDATE Members SET Subscription_Type = ? WHERE M_id = ?";
   
    if ($stmt = $mysqli->prepare($sql_update_subscription)) {

        $stmt->bind_param("si", $package_type_selected, $m_id);


        if ($stmt->execute()) {

            $_SESSION['subscription_type'] = $package_type_selected;

            if ($stmt->affected_rows > 0) {
                $final_message = "Your subscription package has been successfully updated to '" . htmlspecialchars($package_type_selected) . "'!";
                $message_category = "success_msg";
            } else {

                $final_message = "Your subscription is now set to '" . htmlspecialchars($package_type_selected) . "'. No database change was required, or it was already this type.";
                $message_category = "info_msg";
            }

            $redirect_url = "../member_dashboard.php";
        } else {

            error_log("MySQLi Execute Error (Package Update M_id: {$m_id}): " . $stmt->error);
            $final_message = "Failed to update subscription package due to a database error. Please try again.";
            $message_category = "error_msg";
            $redirect_url = "../member_packages.php"; 
        }

        $stmt->close();
    } else {

        error_log("MySQLi Prepare Error (Package Update): " . $mysqli->error);
        $final_message = "Database error during package update preparation. Please try again.";
        $message_category = "error_msg";
        $redirect_url = "../member_packages.php"; 
    }


    if (isset($mysqli)) $mysqli->close();


    $redirect_url .= (strpos($redirect_url, '?') === false ? '?' : '&') . $message_category . "=" . urlencode($final_message);
    header("Location: " . $redirect_url);
    exit();

} else { 
    header("Location: ../member_packages.php");
    exit();
}
?>
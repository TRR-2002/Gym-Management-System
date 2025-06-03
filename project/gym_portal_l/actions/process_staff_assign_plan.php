<?php


session_start();
require_once '../includes/auth_check_staff.php'; 
require_once '../includes/db_connect.php';     

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $s_id_assigning_staff = $_SESSION['staff_s_id'];

    $m_id_member = filter_input(INPUT_POST, 'member_m_id', FILTER_VALIDATE_INT);
    $p_id_plan_to_assign = filter_input(INPUT_POST, 'plan_p_id', FILTER_VALIDATE_INT);


    $redirect_url = trim($_POST['redirect_to'] ?? '') ?: '../staff_manage_members.php';


    if (!$m_id_member || !$p_id_plan_to_assign) {
        $error_msg = "Invalid member or plan selection for assignment.";
        header("Location: " . $redirect_url . (strpos($redirect_url, '?') === false ? '?' : '&') . "error_msg=" . urlencode($error_msg));
        exit();
    }

    $mysqli->autocommit(FALSE);
    $all_queries_successful = true;
    $final_message = '';
    $message_type = '';


    $sql_assign_to_member = "UPDATE Members SET P_id = ?, S_id = ? WHERE M_id = ?";
    if ($stmt_assign = $mysqli->prepare($sql_assign_to_member)) {

        $stmt_assign->bind_param("iii", $p_id_plan_to_assign, $s_id_assigning_staff, $m_id_member);

        if (!$stmt_assign->execute()) {
            error_log("MySQLi Execute Error (Assign Plan to Member M_id {$m_id_member}): " . $stmt_assign->error);
            $all_queries_successful = false;
            $final_message = "Failed to assign plan to member.";
        } elseif ($stmt_assign->affected_rows === 0) {

            $final_message = "No changes made to member's plan assignment (already assigned or member not found).";
            $message_type = "info"; 
        }
        $stmt_assign->close();
    } else {
        error_log("MySQLi Prepare Error (Assign Plan to Member): " . $mysqli->error);
        $all_queries_successful = false;
        $final_message = "Database error preparing plan assignment.";
    }


    if ($all_queries_successful) {
        $sql_reset_plan_weights = "UPDATE Plan SET Starting_Weight = NULL, Current_Weight = NULL WHERE P_id = ?";
        if ($stmt_reset = $mysqli->prepare($sql_reset_plan_weights)) {
            $stmt_reset->bind_param("i", $p_id_plan_to_assign);
            if (!$stmt_reset->execute()) {
                error_log("MySQLi Execute Error (Reset Plan Weights for P_id {$p_id_plan_to_assign}): " . $stmt_reset->error);

                $all_queries_successful = false;
                $final_message = ($final_message ? $final_message . "<br>" : "") . "Failed to reset plan instance weights for the member.";
            }

            $stmt_reset->close();
        } else {
            error_log("MySQLi Prepare Error (Reset Plan Weights): " . $mysqli->error);
            $all_queries_successful = false;
            $final_message = ($final_message ? $final_message . "<br>" : "") . "Database error preparing to reset plan weights.";
        }
    }

    if ($all_queries_successful) {
        $mysqli->commit();
        if (empty($final_message) || $message_type === "info") { 
             $final_message = "Plan successfully assigned to the member. You are now their assigned trainer.";
             $message_type = "success";
        }
    } else {
        $mysqli->rollback(); 
        if (empty($final_message)) $final_message = "Failed to assign plan due to an unknown error."; 
        $message_type = "error";
    }

    $mysqli->autocommit(TRUE); 
    if (isset($mysqli)) $mysqli->close(); 

    $query_param_key = ($message_type === "success" || $message_type === "info") ? "success_msg" : "error_msg";
    if ($message_type === "info" && $query_param_key === "success_msg") $query_param_key = "info_msg"; 

    $redirect_query_string = http_build_query([$query_param_key => $final_message]);
    header("Location: " . $redirect_url . (strpos($redirect_url, '?') === false ? '?' : '&') . $redirect_query_string);
    exit();

} else {
    header("Location: ../staff_manage_members.php"); 
}
?>
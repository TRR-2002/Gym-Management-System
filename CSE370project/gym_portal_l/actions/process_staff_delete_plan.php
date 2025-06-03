<?php


session_start();
require_once '../includes/auth_check_staff.php';
require_once '../includes/db_connect.php';     
















if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['p_id'])) {
    $s_id_staff = $_SESSION['staff_s_id']; 
    $p_id_to_delete = filter_input(INPUT_GET, 'p_id', FILTER_VALIDATE_INT);

    if (!$p_id_to_delete) {
        $error_msg = "Invalid Plan ID specified for deletion.";
        header("Location: ../staff_manage_plans.php?error_msg=" . urlencode($error_msg));
        exit();
    }

    $mysqli->autocommit(FALSE); 
    $all_queries_successful = true;
    $plan_name_deleted = "ID: " . $p_id_to_delete; 
    $final_message = '';
    $message_type = '';















    $sql_check_owner = "SELECT S_id, Plan_Name FROM Plan WHERE P_id = ?";
    if ($stmt_check = $mysqli->prepare($sql_check_owner)) {
        $stmt_check->bind_param("i", $p_id_to_delete);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows === 1) {
            $plan_data = $result_check->fetch_assoc();
            $plan_name_deleted = $plan_data['Plan_Name']; 
            if ($plan_data['S_id'] != $s_id_staff) {
                $all_queries_successful = false;
                $final_message = "You do not have permission to delete this plan.";
            }
        } else {
            $all_queries_successful = false;
            $final_message = "Plan not found for deletion.";
        }
        $stmt_check->close();
    } else {
        error_log("MySQLi Prepare Error (Delete Plan - Owner Check): " . $mysqli->error);
        $all_queries_successful = false;
        $final_message = "Database error verifying plan ownership.";
    }


    if ($all_queries_successful) {
        $sql_delete_plan = "DELETE FROM Plan WHERE P_id = ? AND S_id = ?"; 
        if ($stmt_delete = $mysqli->prepare($sql_delete_plan)) {
            $stmt_delete->bind_param("ii", $p_id_to_delete, $s_id_staff);

            if ($stmt_delete->execute()) {
                if ($stmt_delete->affected_rows > 0) {
                    $final_message = "Plan '".htmlspecialchars($plan_name_deleted)."' deleted successfully.";
                    $message_type = "success";
                } else {

                    $all_queries_successful = false; 
                    $final_message = "Plan could not be deleted. It might have already been removed or does not belong to you.";
                }
            } else {
                error_log("MySQLi Execute Error (Delete Plan P_id: {$p_id_to_delete}): " . $stmt_delete->error);
                $all_queries_successful = false;
                $final_message = "Failed to delete the plan due to a database error.";
            }
            $stmt_delete->close();
        } else {
            error_log("MySQLi Prepare Error (Delete Plan): " . $mysqli->error);
            $all_queries_successful = false;
            $final_message = "Database error preparing plan deletion.";
        }
    }








    if ($all_queries_successful && $message_type === "success") {
        $mysqli->commit();
    } else {
        $mysqli->rollback();
        $message_type = "error"; 
        if (empty($final_message)) $final_message = "Plan deletion failed."; 
    }

    $mysqli->autocommit(TRUE); 
    if (isset($mysqli)) $mysqli->close();

    $query_param_key = ($message_type === "success") ? "success_msg" : "error_msg";
    $redirect_query_string = http_build_query([$query_param_key => $final_message]);
    header("Location: ../staff_manage_plans.php?" . $redirect_query_string);
    exit();

} else {
    $error_msg = "Invalid request for deleting a plan.";
    header("Location: ../staff_manage_plans.php?error_msg=" . urlencode($error_msg));
    exit();
}
?>
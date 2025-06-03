<?php


session_start();
require_once '../includes/auth_check_member.php'; 
require_once '../includes/db_connect.php';     


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $m_id = $_SESSION['user_id'];
    $p_id = $_SESSION['plan_id'] ?? null; 

    $current_weight_input = filter_input(INPUT_POST, 'current_weight', FILTER_VALIDATE_FLOAT);

    
    if (!$p_id) { 
        $error_msg = "No active fitness plan assigned. Cannot log progress.";
        header("Location: ../member_log_progress.php?error_msg=" . urlencode($error_msg));
        exit();
    }
    if ($current_weight_input === false || $current_weight_input <= 0) { 
        $error_msg = "Please enter a valid positive weight value.";
        header("Location: ../member_log_progress.php?error_msg=" . urlencode($error_msg));
        exit();
    }


    $sql_fetch_plan_state = "SELECT Starting_Weight FROM Plan WHERE P_id = ?";
    $starting_weight_from_db = null;
    $plan_exists = false;
    $db_error_occurred = false;

    if ($stmt_fetch = $mysqli->prepare($sql_fetch_plan_state)) {
        $stmt_fetch->bind_param("i", $p_id);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();
        if ($result_fetch->num_rows === 1) {
            $plan_row = $result_fetch->fetch_assoc();
            $starting_weight_from_db = $plan_row['Starting_Weight'];
            $plan_exists = true;
        } else {

            $error_msg = "Your assigned plan could not be found. Please contact support.";
        }
        $stmt_fetch->close();
    } else {
        error_log("MySQLi Prepare Error (Fetch Plan for Progress Log): " . $mysqli->error);
        $error_msg = "Database error fetching plan details.";
        $db_error_occurred = true;
    }

    if (!$plan_exists || $db_error_occurred) {

        header("Location: ../member_log_progress.php?error_msg=" . urlencode($error_msg ?? 'Unknown error fetching plan.'));
        exit();
    }

    $sql_update_plan = "";
    $bind_types_string = ""; 
    $params_for_bind = [];   

    if ($starting_weight_from_db === null) {

        $sql_update_plan = "UPDATE Plan SET Starting_Weight = ?, Current_Weight = ? WHERE P_id = ?";
        $bind_types_string = "ddi"; 
        $params_for_bind = [$current_weight_input, $current_weight_input, $p_id];
    } else {

        $sql_update_plan = "UPDATE Plan SET Current_Weight = ? WHERE P_id = ?";
        $bind_types_string = "di";
        $params_for_bind = [$current_weight_input, $p_id];
    }


    $redirect_url_suffix = ''; 

    if ($stmt_update = $mysqli->prepare($sql_update_plan)) {

        $stmt_update->bind_param($bind_types_string, ...$params_for_bind); 

        if ($stmt_update->execute()) {
            if ($stmt_update->affected_rows > 0) {
                $redirect_url_suffix = "?success_msg=" . urlencode("Your weight has been logged successfully!");
            } else {

                $redirect_url_suffix = "?info_msg=" . urlencode("Weight logged, but no change was recorded. Your previous weight might be the same.");
            }
        } else {
            error_log("MySQLi Execute Error (Log Progress Update P_id: {$p_id}): " . $stmt_update->error);
            $redirect_url_suffix = "?error_msg=" . urlencode("Failed to log weight due to a system error. Please try again.");
        }
        $stmt_update->close();
    } else {
        error_log("MySQLi Prepare Error (Log Progress Update P_id: {$p_id}): " . $mysqli->error);
        $redirect_url_suffix = "?error_msg=" . urlencode("Database error preparing to log weight. Please try again.");
    }

    if (isset($mysqli)) $mysqli->close(); 
    header("Location: ../member_log_progress.php" . $redirect_url_suffix); 
    exit();

} else { 
    header("Location: ../member_log_progress.php");
    exit();
}
?>
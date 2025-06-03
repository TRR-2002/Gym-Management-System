<?php


session_start();
require_once '../includes/auth_check_staff.php';
require_once '../includes/db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $s_id_editor = $_SESSION['staff_s_id'];

    $p_id_to_edit = filter_input(INPUT_POST, 'p_id_to_edit', FILTER_VALIDATE_INT);

    if (!$p_id_to_edit) {
        $error_msg = "Invalid Plan ID for editing.";
        header("Location: ../staff_manage_plans.php?error_msg=" . urlencode($error_msg));
        exit();
    }


    $plan_name = trim($_POST['plan_name'] ?? '');
    $template_starting_weight_input = trim($_POST['template_starting_weight'] ?? '');
    $template_starting_weight = !empty($template_starting_weight_input) ? filter_var($template_starting_weight_input, FILTER_VALIDATE_FLOAT) : null;
    $goal_weight_input = trim($_POST['goal_weight'] ?? '');
    $goal_weight = !empty($goal_weight_input) ? filter_var($goal_weight_input, FILTER_VALIDATE_FLOAT) : null;
    $diet_input = trim($_POST['diet'] ?? '');
    $diet = !empty($diet_input) ? $diet_input : null;


    $days = $_POST['day'] ?? [];
    $times = $_POST['time'] ?? [];
    $exercises = $_POST['exercise'] ?? [];


    $errors = [];
    if (empty($plan_name)) { $errors[] = "Plan Name is required."; }


    if (!empty($errors)) {

        $error_query_param = http_build_query(['error_msg' => implode("<br>", $errors)]);
        header("Location: ../staff_edit_plan.php?p_id=" . $p_id_to_edit . "&" . $error_query_param);
        exit();
    }


    $mysqli->autocommit(FALSE); 
    $all_queries_successful = true;
    $plan_name_for_success_msg = $plan_name;


    $sql_check_owner = "SELECT S_id, Plan_Name FROM Plan WHERE P_id = ?";
    if ($stmt_check = $mysqli->prepare($sql_check_owner)) {
        $stmt_check->bind_param("i", $p_id_to_edit);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows === 1) {
            $plan_owner_data = $result_check->fetch_assoc();
            $plan_name_for_success_msg = $plan_owner_data['Plan_Name'];
            if ($plan_owner_data['S_id'] != $s_id_editor) {
                $all_queries_successful = false;
                $_SESSION['message_edit_plan_error'] = "You do not have permission to edit this plan.";
            }
        } else {
            $all_queries_successful = false;
            $_SESSION['message_edit_plan_error'] = "Plan not found for editing.";
        }
        $stmt_check->close();
    } else {
        error_log("MySQLi Prepare Error (Plan Edit Owner Check): " . $mysqli->error);
        $all_queries_successful = false;
        $_SESSION['message_edit_plan_error'] = "Database error verifying plan ownership.";
    }

    
    if ($all_queries_successful) {
        $sql_update_plan = "UPDATE Plan SET Plan_Name = ?, Starting_Weight = ?, Goal_Weight = ?, Diet = ?
                            WHERE P_id = ? AND S_id = ?"; 
        if ($stmt_update_plan = $mysqli->prepare($sql_update_plan)) {
            $stmt_update_plan->bind_param("sddsii",
                $plan_name, $template_starting_weight, $goal_weight, $diet,
                $p_id_to_edit, $s_id_editor
            );
            if (!$stmt_update_plan->execute()) {
                error_log("MySQLi Execute Error (Plan Update): " . $stmt_update_plan->error);
                $all_queries_successful = false;
                $_SESSION['message_edit_plan_error'] = "Failed to update plan details.";
            }
            $stmt_update_plan->close();
        } else {
            error_log("MySQLi Prepare Error (Plan Update): " . $mysqli->error);
            $all_queries_successful = false;
            $_SESSION['message_edit_plan_error'] = "Database error preparing plan update.";
        }
    }


    if ($all_queries_successful) {
        $sql_delete_routines = "DELETE FROM Plan_Routine WHERE P_id = ?";
        if ($stmt_delete_routines = $mysqli->prepare($sql_delete_routines)) {
            $stmt_delete_routines->bind_param("i", $p_id_to_edit);
            if (!$stmt_delete_routines->execute()) {
                error_log("MySQLi Execute Error (Delete Old Routines): " . $stmt_delete_routines->error);
                $all_queries_successful = false;
                $_SESSION['message_edit_plan_error'] = "Failed to clear existing routine items.";
            }
            $stmt_delete_routines->close();
        } else {
            error_log("MySQLi Prepare Error (Delete Old Routines): " . $mysqli->error);
            $all_queries_successful = false;
            $_SESSION['message_edit_plan_error'] = "Database error preparing to clear routines.";
        }
    }


    if ($all_queries_successful) {
        $has_valid_routine_item = false;
        for ($i = 0; $i < count($days); $i++) {
            if (!empty(trim($days[$i] ?? '')) && !empty(trim($exercises[$i] ?? ''))) {
                $has_valid_routine_item = true;
                break;
            }
        }

        if ($has_valid_routine_item) {
            $sql_insert_routine = "INSERT INTO Plan_Routine (P_id, Day, Time, Exercise) VALUES (?, ?, ?, ?)";
            if ($stmt_insert_routine = $mysqli->prepare($sql_insert_routine)) {
                for ($i = 0; $i < count($days); $i++) {
                    $current_day_input = trim($days[$i] ?? '');
                    $current_exercise_input = trim($exercises[$i] ?? '');

                    if (!empty($current_day_input) && !empty($current_exercise_input)) {
                        $current_time_input = trim($times[$i] ?? '');
                        $current_time = !empty($current_time_input) ? $current_time_input : null;

                        $stmt_insert_routine->bind_param("isss", $p_id_to_edit, $current_day_input, $current_time, $current_exercise_input);
                        if (!$stmt_insert_routine->execute()) {
                            error_log("MySQLi Execute Error (Re-insert Routine P_id {$p_id_to_edit}): " . $stmt_insert_routine->error);
                            $all_queries_successful = false;
                            $_SESSION['message_edit_plan_error'] = "Failed to save some routine items.";
                            break; 
                        }
                    }
                }
                $stmt_insert_routine->close();
            } else {
                error_log("MySQLi Prepare Error (Re-insert Routine): " . $mysqli->error);
                $all_queries_successful = false;
                $_SESSION['message_edit_plan_error'] = "Database error preparing to save routines.";
            }
        }
    }


    $final_redirect_url = '';
    if ($all_queries_successful) {
        $mysqli->commit(); 
        $success_msg = "Plan '".htmlspecialchars($plan_name)."' updated successfully!";
        $final_redirect_url = "../staff_manage_plans.php?success_msg=" . urlencode($success_msg);
    } else {
        $mysqli->rollback(); 
        $error_msg = $_SESSION['message_edit_plan_error'] ?? "Failed to update plan. Please review and try again.";
        unset($_SESSION['message_edit_plan_error']); 
        $final_redirect_url = "../staff_edit_plan.php?p_id=" . $p_id_to_edit . "&error_msg=" . urlencode($error_msg);
    }

    $mysqli->autocommit(TRUE); 
    if (isset($mysqli)) $mysqli->close();
    header("Location: " . $final_redirect_url);
    exit();

} else { 
    header("Location: ../staff_manage_plans.php");
    exit();
}
?>
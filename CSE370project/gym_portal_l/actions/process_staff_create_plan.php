<?php


session_start();
require_once '../includes/auth_check_staff.php'; 
require_once '../includes/db_connect.php';     

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $s_id_creator = $_SESSION['staff_s_id'];


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
    if ($template_starting_weight !== null && ($template_starting_weight === false || $template_starting_weight <= 0)) {
        $errors[] = "Template Starting Weight, if provided, must be a valid positive number.";
    }
    if ($goal_weight !== null && ($goal_weight === false || $goal_weight <= 0)) {
        $errors[] = "Goal Weight, if provided, must be a valid positive number.";
    }


    if (!empty($errors)) {

        $error_query_param = http_build_query(['error_msg' => implode("<br>", $errors)]);
        header("Location: ../staff_create_plan.php?" . $error_query_param);
        exit();
    }

    $mysqli->autocommit(FALSE);
    $all_queries_successful = true;
    $new_p_id = null;

  
    $sql_insert_plan = "INSERT INTO Plan (S_id, Plan_Name, Starting_Weight, Goal_Weight, Diet, Current_Weight)
                        VALUES (?, ?, ?, ?, ?, NULL)";
    if ($stmt_plan = $mysqli->prepare($sql_insert_plan)) {

        







        $stmt_plan->bind_param("isdds",
            $s_id_creator,
            $plan_name,
            $template_starting_weight, 
            $goal_weight,              
            $diet                     
        );

        if (!$stmt_plan->execute()) {
            error_log("MySQLi Execute Error (Plan Insert): " . $stmt_plan->error);
            $all_queries_successful = false;
        } else {
            
            $new_p_id = $mysqli->insert_id;
        }
        $stmt_plan->close();
    } else {
        error_log("MySQLi Prepare Error (Plan Insert): " . $mysqli->error);
        $all_queries_successful = false;
    }


    if ($all_queries_successful && $new_p_id !== null) {
        $has_valid_routine_item = false;

        for ($i = 0; $i < count($days); $i++) {
            if (!empty(trim($days[$i] ?? '')) && !empty(trim($exercises[$i] ?? ''))) {
                $has_valid_routine_item = true;
                break;
            }
        }

        if ($has_valid_routine_item) {
            $sql_insert_routine = "INSERT INTO Plan_Routine (P_id, Day, Time, Exercise) VALUES (?, ?, ?, ?)";
            if ($stmt_routine = $mysqli->prepare($sql_insert_routine)) {
                for ($i = 0; $i < count($days); $i++) {
                    $current_day_input = trim($days[$i] ?? '');
                    $current_exercise_input = trim($exercises[$i] ?? '');

                    if (!empty($current_day_input) && !empty($current_exercise_input)) {
                        $current_day = $current_day_input;
                        $current_time_input = trim($times[$i] ?? '');






                        $current_time = !empty($current_time_input) ? $current_time_input : null;
                        $current_exercise = $current_exercise_input; 

                        $stmt_routine->bind_param("isss", $new_p_id, $current_day, $current_time, $current_exercise);
                        if (!$stmt_routine->execute()) {
                            error_log("MySQLi Execute Error (Routine Insert for P_id {$new_p_id}): " . $stmt_routine->error);
                            $all_queries_successful = false;
                            break; 
                        }
                    }
                }
                $stmt_routine->close();
            } else {
                error_log("MySQLi Prepare Error (Routine Insert): " . $mysqli->error);
                $all_queries_successful = false;
            }
        }
    }

    if ($all_queries_successful) {

        $mysqli->commit();
        $success_msg = "Fitness plan '".htmlspecialchars($plan_name)."' created successfully!";

        $redirect_url = "../staff_manage_plans.php?success_msg=" . urlencode($success_msg);
    } else {

        $mysqli->rollback();

        $error_msg = $_SESSION['message'] ?? "Failed to create plan. Please check details and routine items."; 
        if(isset($_SESSION['message'])) unset($_SESSION['message']); 
        $redirect_url = "../staff_create_plan.php?error_msg=" . urlencode($error_msg);
    }

    $mysqli->autocommit(TRUE);
    if (isset($mysqli)) $mysqli->close();
    header("Location: " . $redirect_url);
    exit();

} else { 
    header("Location: ../staff_create_plan.php");
    exit();
}
?>
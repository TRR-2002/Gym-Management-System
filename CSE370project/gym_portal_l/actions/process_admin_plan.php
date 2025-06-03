<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once '../includes/auth_check_admin.php';

if (!isset($pdo)) {
    $_SESSION['message'] = "CRITICAL: Database connection not available in plan processing script.";
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/manage_plans.php"); 
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = "Invalid request method.";
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/manage_plans.php");
    exit();
}

$action = $_POST['action'] ?? 'add';
$plan_id = isset($_POST['plan_id']) ? filter_var($_POST['plan_id'], FILTER_VALIDATE_INT) : null;

$goal_weight_input = trim($_POST['goal_weight'] ?? '');
$diet = trim($_POST['diet'] ?? '');
$s_id_input = trim($_POST['s_id'] ?? '');
$current_weight_input = trim($_POST['current_weight'] ?? '');

$goal_weight = (!empty($goal_weight_input) && is_numeric($goal_weight_input)) ? (float)$goal_weight_input : null;
$s_id = (!empty($s_id_input) && filter_var($s_id_input, FILTER_VALIDATE_INT)) ? (int)$s_id_input : null;
$current_weight = (!empty($current_weight_input) && is_numeric($current_weight_input)) ? (float)$current_weight_input : null;

$routines_input = $_POST['routines'] ?? [];

$redirect_url = "../admin/plan_form.php?action=" . urlencode($action);
if ($plan_id) {
    $redirect_url .= "&plan_id=" . urlencode($plan_id);
}

$errors = [];
if ($goal_weight === null) $errors[] = "Goal Weight is required and must be a number.";
elseif ($goal_weight < 0) $errors[] = "Goal Weight cannot be negative.";
if (empty($diet)) $errors[] = "Diet Plan/Guidelines are required.";
if ($current_weight !== null && $current_weight < 0) $errors[] = "Initial Current Weight cannot be negative.";

$valid_routines = [];
if (!empty($routines_input)) {
    foreach ($routines_input as $index => $routine_item) {
        $day = trim($routine_item['day'] ?? '');
        $time = trim($routine_item['time'] ?? '');
        $exercise = trim($routine_item['exercise'] ?? '');

        if (empty($day) || empty($time) || empty($exercise)) {
            continue; 
        }
        $valid_routines[] = ['day' => $day, 'time' => $time, 'exercise' => $exercise];
    }
}

if (!empty($errors)) {
    $_SESSION['message'] = implode("<br>", $errors);
    $_SESSION['message_type'] = "error";
    header("Location: " . $redirect_url);
    exit();
}

try {
    $pdo->beginTransaction();

    if ($s_id !== null) {
        $stmt_check_staff = $pdo->prepare("SELECT w.W_id FROM Workers w JOIN Staff s ON w.W_id = s.S_id WHERE w.W_id = :s_id");
        $stmt_check_staff->bindParam(':s_id', $s_id, PDO::PARAM_INT);
        $stmt_check_staff->execute();
        if (!$stmt_check_staff->fetch()) {
            $pdo->rollBack();
            $_SESSION['message'] = "The selected Staff ID ({$s_id}) is invalid or not a staff member.";
            $_SESSION['message_type'] = "error";
            header("Location: " . $redirect_url);
            exit();
        }
    }
    
    $current_P_id = $plan_id; 

    if ($action === 'add') {
        $sql_plan = "INSERT INTO Plan (Goal_Weight, Diet, S_id, Current_Weight) 
                     VALUES (:goal_weight, :diet, :s_id, :current_weight)";
        $stmt_plan = $pdo->prepare($sql_plan);
    } elseif ($action === 'edit' && $plan_id) {
        $sql_plan = "UPDATE Plan SET Goal_Weight = :goal_weight, Diet = :diet, S_id = :s_id, Current_Weight = :current_weight
                     WHERE P_id = :plan_id";
        $stmt_plan = $pdo->prepare($sql_plan);
        $stmt_plan->bindParam(':plan_id', $plan_id, PDO::PARAM_INT);
    } else {
        throw new Exception("Invalid action or missing data for plan processing.");
    }

    $stmt_plan->bindParam(':goal_weight', $goal_weight, PDO::PARAM_STR); 
    $stmt_plan->bindParam(':diet', $diet, PDO::PARAM_STR);
    $stmt_plan->bindParam(':s_id', $s_id, ($s_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT));
    $stmt_plan->bindParam(':current_weight', $current_weight, ($current_weight === null ? PDO::PARAM_NULL : PDO::PARAM_STR));

    if (!$stmt_plan->execute()) {
        $pdo->rollBack();
        $_SESSION['message'] = "Failed to " . ($action === 'add' ? "create" : "update") . " plan details.";
        $_SESSION['message_type'] = "error";
        header("Location: " . $redirect_url);
        exit();
    }

    if ($action === 'add') {
        $current_P_id = $pdo->lastInsertId(); 
    }

    if ($current_P_id) { 
        if ($action === 'edit') {
            $stmt_delete_old_routines = $pdo->prepare("DELETE FROM Plan_Routine WHERE P_id = :p_id");
            $stmt_delete_old_routines->bindParam(':p_id', $current_P_id, PDO::PARAM_INT);
            $stmt_delete_old_routines->execute();
        }

        if (!empty($valid_routines)) {
            $sql_routine_insert = "INSERT INTO Plan_Routine (P_id, Day, Time, Exercise) VALUES (:p_id, :day, :time, :exercise)";
            $stmt_routine_insert = $pdo->prepare($sql_routine_insert);
            $stmt_routine_insert->bindParam(':p_id', $current_P_id, PDO::PARAM_INT);

            foreach ($valid_routines as $routine) {
                $stmt_routine_insert->bindParam(':day', $routine['day'], PDO::PARAM_STR);
                $stmt_routine_insert->bindParam(':time', $routine['time'], PDO::PARAM_STR);
                $stmt_routine_insert->bindParam(':exercise', $routine['exercise'], PDO::PARAM_STR);
                if (!$stmt_routine_insert->execute()) {
                    $pdo->rollBack();
                    $_SESSION['message'] = "Failed to save one or more routine entries for Plan ID {$current_P_id}.";
                    $_SESSION['message_type'] = "error";
                    header("Location: " . $redirect_url); 
                    exit();
                }
            }
        }
    }

    $pdo->commit();
    $_SESSION['message'] = "Fitness Plan " . ($action === 'add' ? "created" : "updated") . " successfully (ID: {$current_P_id}).";
    $_SESSION['message_type'] = "success";
    header("Location: ../admin/manage_plans.php"); 
    exit();

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Database error processing plan: " . $e->getMessage());
    $_SESSION['message'] = "Database error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log("General error processing plan: " . $e->getMessage());
    $_SESSION['message'] = "An unexpected error occurred: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}

header("Location: " . $redirect_url); 
exit();
?>
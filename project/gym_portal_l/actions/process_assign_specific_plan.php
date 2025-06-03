<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once '../includes/auth_check_admin.php';


if (!isset($pdo)) {
    $_SESSION['message'] = "CRITICAL: Database connection not available.";
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/manage_plans.php"); 
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['assign_to_selected_members'])) {
    $_SESSION['message'] = "Invalid request.";
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/manage_plans.php");
    exit();
}

$plan_id_to_assign = isset($_POST['plan_id_to_assign']) ? filter_var($_POST['plan_id_to_assign'], FILTER_VALIDATE_INT) : null;
$selected_member_ids = $_POST['member_ids'] ?? []; 

if (!$plan_id_to_assign) {
    $_SESSION['message'] = "Plan ID is missing or invalid.";
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/manage_plans.php");
    exit();
}

if (empty($selected_member_ids)) {
    $_SESSION['message'] = "No members were selected for plan assignment.";
    $_SESSION['message_type'] = "warning"; 
    header("Location: ../admin/assign_specific_plan.php?plan_id=" . $plan_id_to_assign);
    exit();
}


try {
    $stmt_check_plan = $pdo->prepare("SELECT P_id FROM Plan WHERE P_id = :plan_id");
    $stmt_check_plan->bindParam(':plan_id', $plan_id_to_assign, PDO::PARAM_INT);
    $stmt_check_plan->execute();
    if (!$stmt_check_plan->fetch()) {
        $_SESSION['message'] = "The Plan ID ({$plan_id_to_assign}) to assign does not exist.";
        $_SESSION['message_type'] = "error";
        header("Location: ../admin/manage_plans.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("DB error checking plan existence: " . $e->getMessage());
    $_SESSION['message'] = "Database error verifying plan.";
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/manage_plans.php");
    exit();
}



$assigned_count = 0;
$error_count = 0;
$already_assigned_skipped = 0;

try {
    $pdo->beginTransaction();
    $sql_update_member_plan = "UPDATE Members SET P_id = :plan_id WHERE M_id = :member_id";
    $stmt_update = $pdo->prepare($sql_update_member_plan);
    $stmt_update->bindParam(':plan_id', $plan_id_to_assign, PDO::PARAM_INT);

    foreach ($selected_member_ids as $member_id_input) {
        $member_id = filter_var($member_id_input, FILTER_VALIDATE_INT);
        if ($member_id) {
    

            $stmt_update->bindParam(':member_id', $member_id, PDO::PARAM_INT);
            if ($stmt_update->execute()) {
                $assigned_count++;
            } else {
                $error_count++;
            }
        } else {
            $error_count++; 
        }
    }

    if ($error_count > 0) {
        $pdo->rollBack(); 
        $_SESSION['message'] = "An error occurred. {$assigned_count} members assigned, but {$error_count} assignments failed. All changes rolled back.";
        $_SESSION['message_type'] = "error";
    } else {
        $pdo->commit();
        $message = "Plan ID {$plan_id_to_assign} successfully assigned to {$assigned_count} member(s).";
     
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = "success";
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Database error assigning plan to multiple members: " . $e->getMessage());
    $_SESSION['message'] = "Database error during bulk assignment: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log("General error assigning plan to multiple members: " . $e->getMessage());
    $_SESSION['message'] = "An unexpected error occurred: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}

header("Location: ../admin/assign_specific_plan.php?plan_id=" . $plan_id_to_assign);
exit();
?>
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once '../includes/auth_check_admin.php';

if (!isset($pdo)) {
    $_SESSION['message'] = "CRITICAL: Database connection not available in equipment processing script.";
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/manage_equipment.php"); 
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = "Invalid request method.";
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/manage_equipment.php");
    exit();
}

$action = $_POST['action'] ?? 'add';
$equipment_id = isset($_POST['equipment_id']) ? filter_var($_POST['equipment_id'], FILTER_VALIDATE_INT) : null;

$e_name = trim($_POST['e_name'] ?? '');
$cost_input = trim($_POST['cost'] ?? '');
$quantity_input = trim($_POST['quantity'] ?? '');
$date_of_purchase_input = trim($_POST['date_of_purchase'] ?? '');
$v_name = trim($_POST['v_name'] ?? '');
$v_contact = trim($_POST['v_contact'] ?? '');


$cost = (!empty($cost_input) && is_numeric($cost_input)) ? (float)$cost_input : null;
$quantity = (!empty($quantity_input) && filter_var($quantity_input, FILTER_VALIDATE_INT) !== false && (int)$quantity_input >= 0) ? (int)$quantity_input : 0;
$date_of_purchase = !empty($date_of_purchase_input) ? $date_of_purchase_input : null;


$redirect_url = "../admin/equipment_form.php?action=" . urlencode($action);
if ($equipment_id) {
    $redirect_url .= "&equipment_id=" . urlencode($equipment_id);
}


$errors = [];
if (empty($e_name)) $errors[] = "Equipment Name is required."; 
if ($quantity === null || $quantity < 0) $errors[] = "Quantity must be a non-negative integer."; 
if ($cost !== null && $cost < 0) $errors[] = "Cost cannot be negative.";


if (!empty($errors)) {
    $_SESSION['message'] = implode("<br>", $errors);
    $_SESSION['message_type'] = "error";
    header("Location: " . $redirect_url);
    exit();
}

try {



    if ($action === 'add') {
        $sql = "INSERT INTO Equipment (Name, Cost, Quantity, Date_of_Purchase, V_name, V_contact) 
                VALUES (:e_name, :cost, :quantity, :date_of_purchase, :v_name, :v_contact)";
        $stmt = $pdo->prepare($sql);
    } elseif ($action === 'edit' && $equipment_id) {
        $sql = "UPDATE Equipment SET Name = :e_name, Cost = :cost, Quantity = :quantity, 
                Date_of_Purchase = :date_of_purchase, V_name = :v_name, V_contact = :v_contact
                WHERE E_id = :equipment_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':equipment_id', $equipment_id, PDO::PARAM_INT);
    } else {
        throw new Exception("Invalid action or missing data for equipment processing.");
    }

    $stmt->bindParam(':e_name', $e_name, PDO::PARAM_STR);
    $stmt->bindParam(':cost', $cost, ($cost === null ? PDO::PARAM_NULL : PDO::PARAM_STR)); 
    $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $stmt->bindParam(':date_of_purchase', $date_of_purchase, ($date_of_purchase === null ? PDO::PARAM_NULL : PDO::PARAM_STR));
    $stmt->bindParam(':v_name', $v_name, ($v_name === '' ? PDO::PARAM_NULL : PDO::PARAM_STR));
    $stmt->bindParam(':v_contact', $v_contact, ($v_contact === '' ? PDO::PARAM_NULL : PDO::PARAM_STR));


    if ($stmt->execute()) {
        $_SESSION['message'] = "Equipment " . ($action === 'add' ? "added" : "updated") . " successfully.";
        $_SESSION['message_type'] = "success";
        header("Location: ../admin/manage_equipment.php");
        exit();
    } else {
        $_SESSION['message'] = "Failed to " . ($action === 'add' ? "add" : "update") . " equipment. SQL execution error.";
        $_SESSION['message_type'] = "error";
    }

} catch (PDOException $e) {
    error_log("Database error processing equipment: " . $e->getMessage());
    $_SESSION['message'] = "Database error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
} catch (Exception $e) {
    error_log("General error processing equipment: " . $e->getMessage());
    $_SESSION['message'] = "An unexpected error occurred: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}

header("Location: " . $redirect_url);
exit();
?>
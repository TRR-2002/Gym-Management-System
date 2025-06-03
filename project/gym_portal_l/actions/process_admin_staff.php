<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once '../includes/auth_check_admin.php';

if (!isset($pdo)) {
    $_SESSION['message'] = "CRITICAL: Database connection not available in staff processing script.";
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/manage_staff.php"); 
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = "Invalid request method.";
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/manage_staff.php");
    exit();
}

$action = $_POST['action'] ?? 'add';
$staff_id = isset($_POST['staff_id']) ? filter_var($_POST['staff_id'], FILTER_VALIDATE_INT) : null; 

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$designation = trim($_POST['designation'] ?? '');
$phone_no = trim($_POST['phone_no'] ?? '');
$address = trim($_POST['address'] ?? '');

$redirect_url = "../admin/staff_form.php?action=" . urlencode($action);
if ($staff_id) {
    $redirect_url .= "&staff_id=" . urlencode($staff_id);
}

$errors = [];
if (empty($name)) $errors[] = "Full Name is required.";
if (empty($email)) $errors[] = "Email is required.";
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
if (empty($designation)) $errors[] = "Designation is required.";
if (empty($phone_no)) $errors[] = "Phone Number is required.";
if (empty($address)) $errors[] = "Address is required.";

if ($action === 'add') {
    if (empty($password)) $errors[] = "Password is required for new staff.";
    elseif (strlen($password) < 6) $errors[] = "Password must be at least 6 characters long.";
    elseif ($password !== $confirm_password) $errors[] = "Passwords do not match.";
} elseif ($action === 'edit' && !empty($password)) {
    if (strlen($password) < 6) $errors[] = "New password must be at least 6 characters long.";
}

if (!empty($errors)) {
    $_SESSION['message'] = implode("<br>", $errors);
    $_SESSION['message_type'] = "error";
    header("Location: " . $redirect_url);
    exit();
}

try {
    $pdo->beginTransaction();

    if ($action === 'add') {
        $stmt_check_email = $pdo->prepare("SELECT W_id FROM Workers WHERE Email = :email");
        $stmt_check_email->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt_check_email->execute();
        if ($stmt_check_email->fetch()) {
            $pdo->rollBack();
            $_SESSION['message'] = "This email address is already registered for another worker.";
            $_SESSION['message_type'] = "error";
            header("Location: " . $redirect_url);
            exit();
        }
    } elseif ($action === 'edit' && $staff_id) {
        $stmt_check_email = $pdo->prepare("SELECT W_id FROM Workers WHERE Email = :email AND W_id != :staff_id");
        $stmt_check_email->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt_check_email->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
        $stmt_check_email->execute();
        if ($stmt_check_email->fetch()) {
            $pdo->rollBack();
            $_SESSION['message'] = "This email address is already in use by another worker.";
            $_SESSION['message_type'] = "error";
            header("Location: " . $redirect_url);
            exit();
        }
    }

    $new_worker_id = null;

    if ($action === 'add') {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql_worker = "INSERT INTO Workers (Name, Email, Password, Designation, Address, Phone_No) 
                       VALUES (:name, :email, :password, :designation, :address, :phone_no)";
        $stmt_worker = $pdo->prepare($sql_worker);
        $stmt_worker->bindParam(':password', $hashed_password, PDO::PARAM_STR);
    } elseif ($action === 'edit' && $staff_id) {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_worker = "UPDATE Workers SET Name = :name, Email = :email, Password = :password, 
                           Designation = :designation, Address = :address, Phone_No = :phone_no 
                           WHERE W_id = :staff_id";
            $stmt_worker = $pdo->prepare($sql_worker);
            $stmt_worker->bindParam(':password', $hashed_password, PDO::PARAM_STR);
        } else {
            $sql_worker = "UPDATE Workers SET Name = :name, Email = :email, 
                           Designation = :designation, Address = :address, Phone_No = :phone_no 
                           WHERE W_id = :staff_id";
            $stmt_worker = $pdo->prepare($sql_worker);
        }
        $stmt_worker->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
    } else {
        throw new Exception("Invalid action or missing data for staff processing.");
    }

    $stmt_worker->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt_worker->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt_worker->bindParam(':designation', $designation, PDO::PARAM_STR);
    $stmt_worker->bindParam(':address', $address, PDO::PARAM_STR);
    $stmt_worker->bindParam(':phone_no', $phone_no, PDO::PARAM_STR);

    if ($stmt_worker->execute()) {
        if ($action === 'add') {
            $new_worker_id = $pdo->lastInsertId(); 
            if ($new_worker_id) {
                $stmt_add_to_staff = $pdo->prepare("INSERT INTO Staff (S_id) VALUES (:s_id)");
                $stmt_add_to_staff->bindParam(':s_id', $new_worker_id, PDO::PARAM_INT);
                if (!$stmt_add_to_staff->execute()) {
                    $pdo->rollBack();
                    $_SESSION['message'] = "Worker created, but failed to designate as staff. Rolled back.";
                    $_SESSION['message_type'] = "error";
                    header("Location: " . $redirect_url);
                    exit();
                }
            } else {
                 $pdo->rollBack();
                 $_SESSION['message'] = "Failed to retrieve new worker ID after insertion.";
                 $_SESSION['message_type'] = "error";
                 header("Location: " . $redirect_url);
                 exit();
            }
        }
        
        $pdo->commit();
        $_SESSION['message'] = "Staff member " . ($action === 'add' ? "added" : "updated") . " successfully.";
        $_SESSION['message_type'] = "success";
        header("Location: ../admin/manage_staff.php");
        exit();

    } else {
        $pdo->rollBack();
        $_SESSION['message'] = "Failed to " . ($action === 'add' ? "add" : "update") . " staff worker record.";
        $_SESSION['message_type'] = "error";
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database error processing staff: " . $e->getMessage());
    $_SESSION['message'] = "Database error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("General error processing staff: " . $e->getMessage());
    $_SESSION['message'] = "An unexpected error occurred: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}

header("Location: " . $redirect_url);
exit();
?>
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once '../includes/auth_check_admin.php'; 

if (!isset($pdo)) {
    $_SESSION['message'] = "CRITICAL: Database connection not available.";
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/profile.php"); 
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['update_profile'])) {
    $_SESSION['message'] = "Invalid request method.";
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/profile.php");
    exit();
}

$admin_w_id = $_POST['admin_w_id'] ?? null; 


if (!$admin_w_id || $admin_w_id != $_SESSION['admin_id']) {
    $_SESSION['message'] = "Profile update attempt for incorrect admin ID.";
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/profile.php");
    exit();
}


$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$designation = trim($_POST['designation'] ?? '');
$phone_no = trim($_POST['phone_no'] ?? '');
$address = trim($_POST['address'] ?? '');

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_new_password'] ?? '';

$redirect_url = "../admin/profile.php";


$errors = [];
if (empty($name)) $errors[] = "Full Name is required.";
if (empty($email)) $errors[] = "Email is required.";
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
if (empty($designation)) $errors[] = "Designation is required.";
if (empty($phone_no)) $errors[] = "Phone Number is required.";
if (empty($address)) $errors[] = "Address is required.";

$update_password = false;
if (!empty($new_password) || !empty($current_password) || !empty($confirm_new_password) ) {
    $update_password = true;
    if (empty($current_password)) $errors[] = "Current Password is required to change your password.";
    if (empty($new_password)) $errors[] = "New Password cannot be empty if you intend to change it.";
    elseif (strlen($new_password) < 6) $errors[] = "New Password must be at least 6 characters long.";
    elseif ($new_password !== $confirm_new_password) $errors[] = "New Passwords do not match.";
}


if (!empty($errors)) {
    $_SESSION['message'] = implode("<br>", $errors);
    $_SESSION['message_type'] = "error";
    header("Location: " . $redirect_url);
    exit();
}


try {
    $stmt_current_worker = $pdo->prepare("SELECT Password FROM Workers WHERE W_id = :admin_w_id");
    $stmt_current_worker->bindParam(':admin_w_id', $admin_w_id, PDO::PARAM_INT);
    $stmt_current_worker->execute();
    $worker_db_data = $stmt_current_worker->fetch(PDO::FETCH_ASSOC);

    if (!$worker_db_data) {
        $_SESSION['message'] = "Could not retrieve current admin data for verification.";
        $_SESSION['message_type'] = "error";
        header("Location: " . $redirect_url);
        exit();
    }


    if ($update_password) {
        if (!password_verify($current_password, $worker_db_data['Password'])) {
            $_SESSION['message'] = "Incorrect Current Password.";
            $_SESSION['message_type'] = "error";
            header("Location: " . $redirect_url);
            exit();
        }
    }

    $stmt_check_email = $pdo->prepare("SELECT W_id FROM Workers WHERE Email = :email AND W_id != :admin_w_id");
    $stmt_check_email->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt_check_email->bindParam(':admin_w_id', $admin_w_id, PDO::PARAM_INT);
    $stmt_check_email->execute();
    if ($stmt_check_email->fetch()) {
        $_SESSION['message'] = "This email address is already in use by another worker.";
        $_SESSION['message_type'] = "error";
        header("Location: " . $redirect_url);
        exit();
    }


    if ($update_password) {
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql_update = "UPDATE Workers SET Name = :name, Email = :email, Password = :password, 
                       Designation = :designation, Address = :address, Phone_No = :phone_no 
                       WHERE W_id = :admin_w_id";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->bindParam(':password', $hashed_new_password, PDO::PARAM_STR);
    } else {
        $sql_update = "UPDATE Workers SET Name = :name, Email = :email, 
                       Designation = :designation, Address = :address, Phone_No = :phone_no 
                       WHERE W_id = :admin_w_id";
        $stmt_update = $pdo->prepare($sql_update);
    }


    $stmt_update->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt_update->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt_update->bindParam(':designation', $designation, PDO::PARAM_STR);
    $stmt_update->bindParam(':address', $address, PDO::PARAM_STR);
    $stmt_update->bindParam(':phone_no', $phone_no, PDO::PARAM_STR);
    $stmt_update->bindParam(':admin_w_id', $admin_w_id, PDO::PARAM_INT);

    if ($stmt_update->execute()) {
        $_SESSION['message'] = "Your profile has been updated successfully.";
        $_SESSION['message_type'] = "success";
        if ($_SESSION['admin_name'] != $name) {
            $_SESSION['admin_name'] = $name;
        }
    } else {
        $_SESSION['message'] = "Failed to update your profile. SQL execution error.";
        $_SESSION['message_type'] = "error";
    }

} catch (PDOException $e) {
    error_log("Database error updating admin profile: " . $e->getMessage());
    $_SESSION['message'] = "Database error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
} catch (Exception $e) {
    error_log("General error updating admin profile: " . $e->getMessage());
    $_SESSION['message'] = "An unexpected error occurred: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}

header("Location: " . $redirect_url);
exit();
?>
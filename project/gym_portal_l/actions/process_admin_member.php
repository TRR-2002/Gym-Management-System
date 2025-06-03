<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start(); 
require_once '../includes/auth_check_admin.php'; 


if (!isset($pdo)) {
    $_SESSION['message'] = "CRITICAL: Database connection not available in processing script.";
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/manage_members.php"); 
    exit();
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = "Invalid request method.";
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/manage_members.php");
    exit();
}


$action = $_POST['action'] ?? 'add';
$member_id = isset($_POST['member_id']) ? filter_var($_POST['member_id'], FILTER_VALIDATE_INT) : null;

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? ''; 
$confirm_password = $_POST['confirm_password'] ?? ''; 
$dob = $_POST['dob'] ?? '';
$phone_no = trim($_POST['phone_no'] ?? '');
$address = trim($_POST['address'] ?? '');
$subscription_type = $_POST['subscription_type'] ?? '';
$p_id_input = trim($_POST['p_id'] ?? '');
$p_id = (!empty($p_id_input) && filter_var($p_id_input, FILTER_VALIDATE_INT)) ? (int)$p_id_input : null;


$redirect_url = "../admin/member_form.php?action=" . urlencode($action);
if ($member_id) {
    $redirect_url .= "&member_id=" . urlencode($member_id);
}

$errors = [];
if (empty($name)) $errors[] = "Full Name is required.";
if (empty($email)) $errors[] = "Email is required.";
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
if (empty($dob)) $errors[] = "Date of Birth is required.";
if (empty($phone_no)) $errors[] = "Phone Number is required.";
if (empty($address)) $errors[] = "Address is required.";

if ($action === 'add') {
    if (empty($password)) $errors[] = "Password is required for new members.";
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
        $stmt_check_email = $pdo->prepare("SELECT M_id FROM Members WHERE Email = :email");
        $stmt_check_email->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt_check_email->execute();
        if ($stmt_check_email->fetch()) {
            $pdo->rollBack();
            $_SESSION['message'] = "This email address is already registered.";
            $_SESSION['message_type'] = "error";
            header("Location: " . $redirect_url);
            exit();
        }
    } elseif ($action === 'edit' && $member_id) {
        $stmt_check_email = $pdo->prepare("SELECT M_id FROM Members WHERE Email = :email AND M_id != :member_id");
        $stmt_check_email->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt_check_email->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt_check_email->execute();
        if ($stmt_check_email->fetch()) {
            $pdo->rollBack();
            $_SESSION['message'] = "This email address is already in use by another member.";
            $_SESSION['message_type'] = "error";
            header("Location: " . $redirect_url);
            exit();
        }
    }

    if ($p_id !== null) {
        $stmt_check_plan = $pdo->prepare("SELECT P_id FROM Plan WHERE P_id = :p_id");
        $stmt_check_plan->bindParam(':p_id', $p_id, PDO::PARAM_INT);
        $stmt_check_plan->execute();
        if (!$stmt_check_plan->fetch()) {
            $pdo->rollBack();
            $_SESSION['message'] = "The specified Plan ID ({$p_id}) does not exist.";
            $_SESSION['message_type'] = "error";
            header("Location: " . $redirect_url);
            exit();
        }
    }


    if ($action === 'add') {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO Members (Name, Email, Password, Date_of_Birth, Address, Phone_No, Subscription_Type, P_id) 
                VALUES (:name, :email, :password, :dob, :address, :phone_no, :subscription_type, :p_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
    } elseif ($action === 'edit' && $member_id) {
        if (!empty($password)) { 
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE Members SET Name = :name, Email = :email, Password = :password, Date_of_Birth = :dob, 
                    Address = :address, Phone_No = :phone_no, Subscription_Type = :subscription_type, P_id = :p_id 
                    WHERE M_id = :member_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
        } else { 
            $sql = "UPDATE Members SET Name = :name, Email = :email, Date_of_Birth = :dob, 
                    Address = :address, Phone_No = :phone_no, Subscription_Type = :subscription_type, P_id = :p_id 
                    WHERE M_id = :member_id";
            $stmt = $pdo->prepare($sql);
        }
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    } else {
        throw new Exception("Invalid action or missing data for member processing.");
    }

    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':dob', $dob, PDO::PARAM_STR);
    $stmt->bindParam(':address', $address, PDO::PARAM_STR);
    $stmt->bindParam(':phone_no', $phone_no, PDO::PARAM_STR);
    $stmt->bindParam(':subscription_type', $subscription_type, PDO::PARAM_STR);
    $stmt->bindParam(':p_id', $p_id, ($p_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT));


    if ($stmt->execute()) {
        $pdo->commit();
        $_SESSION['message'] = "Member " . ($action === 'add' ? "added" : "updated") . " successfully.";
        $_SESSION['message_type'] = "success";
        header("Location: ../admin/manage_members.php");
        exit();
    } else {
        $pdo->rollBack();
        $_SESSION['message'] = "Failed to " . ($action === 'add' ? "add" : "update") . " member. SQL execution error.";
        $_SESSION['message_type'] = "error";
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database error processing member: " . $e->getMessage());
    $_SESSION['message'] = "Database error: " . $e->getMessage(); 
    $_SESSION['message_type'] = "error";
} catch (Exception $e) { 
     if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("General error processing member: " . $e->getMessage());
    $_SESSION['message'] = "An unexpected error occurred: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}

header("Location: " . $redirect_url);
exit();
?>
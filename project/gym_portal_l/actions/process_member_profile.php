<?php


session_start();
require_once '../includes/auth_check_member.php'; 
require_once '../includes/db_connect.php';     


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $m_id = $_SESSION['user_id']; 

    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');   
    $phone_no = trim($_POST['phone_no'] ?? ''); 


    $errors = [];
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid Email format.";
    }

    if (!empty($errors)) {
        $error_query_param = http_build_query(['error_msg' => implode("<br>", $errors)]);
        header("Location: ../member_my_profile.php?" . $error_query_param);
        exit();
    }

   



    $email_conflict_error = false;
    $db_check_error_flag = false;

    $sql_check_other_member_email = "SELECT M_id FROM Members WHERE Email = ? AND M_id != ?";
   
    if ($stmt_check_member = $mysqli->prepare($sql_check_other_member_email)) { 
        $stmt_check_member->bind_param("si", $email, $m_id);
        $stmt_check_member->execute();
        $stmt_check_member->store_result();
        if ($stmt_check_member->num_rows > 0) {
            $email_conflict_error = true;
        }
        $stmt_check_member->close();
    } else {
        error_log("MySQLi Prepare Error (Profile Member Email Check): " . $mysqli->error); 
        $db_check_error_flag = true;
    }

 
    if (!$email_conflict_error && !$db_check_error_flag) {
        $sql_check_worker_email = "SELECT W_id FROM Workers WHERE Email = ?";

        if ($stmt_check_worker = $mysqli->prepare($sql_check_worker_email)) {
            $stmt_check_worker->bind_param("s", $email);
            $stmt_check_worker->execute();
            $stmt_check_worker->store_result();
            if ($stmt_check_worker->num_rows > 0) {
                $email_conflict_error = true;
            }
            $stmt_check_worker->close();
        } else {
            error_log("MySQLi Prepare Error (Profile Worker Email Check): " . $mysqli->error); 
            $db_check_error_flag = true;
        }
    }

    if ($db_check_error_flag) {
        $error_query_param = http_build_query(['error_msg' => "Database error validating email. Please try again."]);
        if(isset($mysqli)) $mysqli->close();
        header("Location: ../member_my_profile.php?" . $error_query_param);
        exit();
    }
    if ($email_conflict_error) {
        $error_query_param = http_build_query(['error_msg' => "This Email address is already in use by another account."]);
        if(isset($mysqli)) $mysqli->close();
        header("Location: ../member_my_profile.php?" . $error_query_param);
        exit();
    }


    $sql_update_profile = "UPDATE Members SET Email = ?, Address = ?, Phone_No = ? WHERE M_id = ?";

    if ($stmt_update = $mysqli->prepare($sql_update_profile)) { 
        $address_to_bind = !empty($address) ? $address : null;
        $phone_no_to_bind = !empty($phone_no) ? $phone_no : null;

        $stmt_update->bind_param("sssi", $email, $address_to_bind, $phone_no_to_bind, $m_id);

        if ($stmt_update->execute()) {
            if ($stmt_update->affected_rows > 0) {
                $success_msg = "Profile updated successfully!";
            } else {
                $success_msg = "No changes were made to your profile information.";
            }
            $redirect_url_suffix = "?success_msg=" . urlencode($success_msg);
        } else {
            error_log("MySQLi Execute Error (Profile Update M_id: {$m_id}): " . $stmt_update->error);
            $error_msg = "Failed to update profile. Please try again.";
            $redirect_url_suffix = "?error_msg=" . urlencode($error_msg);
        }
        $stmt_update->close();
    } else {
        error_log("MySQLi Prepare Error (Profile Update M_id: {$m_id}): " . $mysqli->error); 
        $error_msg = "Database error preparing profile update. Please try again.";
        $redirect_url_suffix = "?error_msg=" . urlencode($error_msg);
    }

    if (isset($mysqli)) $mysqli->close();
    header("Location: ../member_my_profile.php" . $redirect_url_suffix);
    exit();

} else {
    if (isset($mysqli)) $mysqli->close(); 
    header("Location: ../member_my_profile.php");
    exit();
}
?>
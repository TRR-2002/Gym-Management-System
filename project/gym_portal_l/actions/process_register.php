<?php

session_start();

require_once '../includes/db_connect.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $date_of_birth = trim($_POST['date_of_birth'] ?? '');
    $address = trim($_POST['address'] ?? ''); 
    $phone_no = trim($_POST['phone_no'] ?? '');

   
    $errors = []; 
    if (empty($name)) { $errors[] = "Full Name is required."; }
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        
        $errors[] = "Invalid Email format.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    if ($password !== $confirm_password) { $errors[] = "Passwords do not match."; }
    if (empty($date_of_birth)) { $errors[] = "Date of Birth is required."; }


    if (!empty($errors)) {
        $_SESSION['form_data_register'] = $_POST; 

        $error_query_param = http_build_query(['error_msg' => implode("<br>", $errors)]);
        
        header("Location: ../register.php?" . $error_query_param);
        exit(); 
    }





    $email_already_exists = false;
    $db_check_error = false;


    $sql_check_member_email = "SELECT M_id FROM Members WHERE Email = ?";
    
    if ($stmt_check_member = $mysqli->prepare($sql_check_member_email)) {
        
        $stmt_check_member->bind_param("s", $email);
       
        $stmt_check_member->execute();
        
        $stmt_check_member->store_result();

        if ($stmt_check_member->num_rows > 0) {
            $email_already_exists = true;
        }

        $stmt_check_member->close();
    } else {
        
        error_log("MySQLi Prepare Error (Member Email Check): " . $mysqli->error);
        $db_check_error = true;
    }

    if (!$email_already_exists && !$db_check_error) {
        $sql_check_worker_email = "SELECT W_id FROM Workers WHERE Email = ?";
        if ($stmt_check_worker = $mysqli->prepare($sql_check_worker_email)) {
            $stmt_check_worker->bind_param("s", $email);
            $stmt_check_worker->execute();
            $stmt_check_worker->store_result();
            if ($stmt_check_worker->num_rows > 0) {
                $email_already_exists = true;
            }
            $stmt_check_worker->close();
        } else {
            error_log("MySQLi Prepare Error (Worker Email Check): " . $mysqli->error);
            $db_check_error = true;
        }
    }

    if ($db_check_error) {
        $_SESSION['form_data_register'] = $_POST;
        $error_query_param = http_build_query(['error_msg' => "Database error during email validation. Please try again."]);
        header("Location: ../register.php?" . $error_query_param);
        exit();
    }
    if ($email_already_exists) {
        $_SESSION['form_data_register'] = $_POST;
        $error_query_param = http_build_query(['error_msg' => "This Email address is already registered."]);
        header("Location: ../register.php?" . $error_query_param);
        exit();
    }

    // --- Insert New Member ---
    $sql_insert_member = "INSERT INTO Members (Name, Email, Password, Date_of_Birth, Address, Phone_No, Subscription_Type)
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
    if ($stmt_insert = $mysqli->prepare($sql_insert_member)) {
        $default_subscription = 'Inactive';
        $address_to_insert = !empty($address) ? $address : null; 
        $phone_no_to_insert = !empty($phone_no) ? $phone_no : null; 


        $stmt_insert->bind_param("sssssss",
            $name, $email, $password, $date_of_birth,
            $address_to_insert, $phone_no_to_insert, $default_subscription
        );

        if ($stmt_insert->execute()) {
            if (isset($_SESSION['form_data_register'])) {

                unset($_SESSION['form_data_register']); 
            }
            $stmt_insert->close();
            $mysqli->close();
            $success_msg = "Registration successful! Please log in.";
            header("Location: ../login.php?success_msg=" . urlencode($success_msg));
            exit();
        } else {

            error_log("MySQLi Execute Error (Member Insert): " . $stmt_insert->error);
            $_SESSION['form_data_register'] = $_POST;
            $error_query_param = http_build_query(['error_msg' => "Registration failed. Please try again."]);
            $stmt_insert->close();
            $mysqli->close();
            header("Location: ../register.php?" . $error_query_param);
            exit();
        }
    } else {
        error_log("MySQLi Prepare Error (Member Insert): " . $mysqli->error);
        $_SESSION['form_data_register'] = $_POST;
        $error_query_param = http_build_query(['error_msg' => "Database error preparing registration. Please try again."]);
        $mysqli->close();
        header("Location: ../register.php?" . $error_query_param);
        exit();
    }
} else {
   
    header("Location: ../register.php");
    exit();
}
?>
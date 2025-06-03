<?php


session_start();
require_once '../includes/db_connect.php'; 

$email = '';
$password = '';
$login_error_message = ''; 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $login_error_message = "Email and Password are required.";
        $_SESSION['form_input_login']['email'] = $email; 
    } else {
        $user_authenticated_and_role_defined = false;
        $redirect_url_on_success = '';

        $sql_member = "SELECT M_id, Name, Password, Subscription_Type, P_id, S_id FROM Members WHERE Email = ?";
        if ($stmt_member = $mysqli->prepare($sql_member)) {
            $stmt_member->bind_param("s", $email);
            $stmt_member->execute();
            $result_member = $stmt_member->get_result(); 

            if ($result_member->num_rows === 1) {
                $member = $result_member->fetch_assoc();
                if ($password === $member['Password']) {
                    session_regenerate_id(true); 

                    $_SESSION['user_id'] = $member['M_id'];
                    $_SESSION['user_name'] = $member['Name'];
                    $_SESSION['user_role'] = 'member';
                    $_SESSION['subscription_type'] = $member['Subscription_Type'];
                    $_SESSION['plan_id'] = $member['P_id'];
                    $_SESSION['assigned_staff_id'] = $member['S_id'];
                    if (isset($_SESSION['form_input_login'])) unset($_SESSION['form_input_login']); 
                    $user_authenticated_and_role_defined = true;
                    $redirect_url_on_success = '../member_dashboard.php';
                }
            }
            $stmt_member->close();
        } else {
            error_log("MySQLi Prepare Error (Member Login): " . $mysqli->error);
            $login_error_message = "Database error during login (M-P). Please try again.";
        }

        
        if (!$user_authenticated_and_role_defined && empty($login_error_message)) {
            $sql_worker = "SELECT W_id, Name, Password FROM Workers WHERE Email = ?";
            if ($stmt_worker = $mysqli->prepare($sql_worker)) {
                $stmt_worker->bind_param("s", $email);
                $stmt_worker->execute();
                $result_worker = $stmt_worker->get_result();

                if ($result_worker->num_rows === 1) {
                    $worker = $result_worker->fetch_assoc();
                    if ($password === $worker['Password']) {
                        $worker_w_id = $worker['W_id'];
                        $determined_role = null;

                        // Check if Worker is in Staff table
                        $sql_staff_check = "SELECT S_id FROM Staff WHERE S_id = ?";
                        if ($stmt_s_check = $mysqli->prepare($sql_staff_check)) {
                            $stmt_s_check->bind_param("i", $worker_w_id);
                            $stmt_s_check->execute();
                            $stmt_s_check->store_result(); // Important for num_rows check
                            if ($stmt_s_check->num_rows === 1) $determined_role = 'staff';
                            $stmt_s_check->close();
                        } else { error_log("MySQLi Prepare Error (Staff Check): " . $mysqli->error); $login_error_message = "Database error (S-RC)."; }

                        // Check Admin table if not staff and no error yet
                        if (!$determined_role && empty($login_error_message)) {
                            $sql_admin_check = "SELECT A_id FROM Admin WHERE A_id = ?";
                            if ($stmt_a_check = $mysqli->prepare($sql_admin_check)) {
                                $stmt_a_check->bind_param("i", $worker_w_id);
                                $stmt_a_check->execute();
                                $stmt_a_check->store_result();
                                if ($stmt_a_check->num_rows === 1) $determined_role = 'admin';
                                $stmt_a_check->close();
                            } else { error_log("MySQLi Prepare Error (Admin Check): " . $mysqli->error); $login_error_message = "Database error (A-RC)."; }
                        }

                        if ($determined_role && empty($login_error_message)) {
                            session_regenerate_id(true);
                            $_SESSION['user_id'] = $worker_w_id;
                            $_SESSION['user_name'] = $worker['Name'];
                            $_SESSION['user_role'] = $determined_role;
                            if (isset($_SESSION['form_input_login'])) unset($_SESSION['form_input_login']);
                            $user_authenticated_and_role_defined = true;

                            if ($determined_role === 'staff') {
                                $_SESSION['staff_s_id'] = $worker_w_id;
                                $redirect_url_on_success = '../staff_dashboard.php';
                            } elseif ($determined_role === 'admin') {
                                $redirect_url_on_success = '../index.php'; 
                            }
                        } elseif (empty($login_error_message)) { 
                            $login_error_message = "User role is undefined. Contact administrator.";
                        }
                    } 
                } 
                $stmt_worker->close();
            } else if (empty($login_error_message)) {
                error_log("MySQLi Prepare Error (Worker Login): " . $mysqli->error);
                $login_error_message = "Database error during login (W-P). Please try again.";
            }
        }


        if (!$user_authenticated_and_role_defined && empty($login_error_message)) {
            $login_error_message = "Invalid email or password.";
        }


        if (!empty($login_error_message)) {
            $_SESSION['form_input_login']['email'] = $email;
        }
    }


    if ($user_authenticated_and_role_defined && !empty($redirect_url_on_success)) {

        if (isset($mysqli)) $mysqli->close();
        header("Location: " . $redirect_url_on_success);
        exit();
    } else {

        if (empty($login_error_message)) $login_error_message = "Login attempt failed. Please try again."; // Default if somehow missed
        $error_query_param = http_build_query(['error_msg' => $login_error_message]);
        if (isset($mysqli)) $mysqli->close();
        header("Location: ../login.php?" . $error_query_param);
        exit();
    }

} else { 
    if (isset($mysqli)) $mysqli->close();
    header("Location: ../login.php");
    exit();
}

if (isset($mysqli)) $mysqli->close();
?>
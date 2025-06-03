<?php


session_start(); 

require_once '../includes/db_connect.php'; 


if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    error_log("CRITICAL: MySQLi connection object (\$mysqli) not available in admin_login_process.php.");

    header("Location: ../admin_login.php?error=" . urlencode("System database connection error. Please contact support."));
    exit();
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    
    header("Location: ../admin_login.php?error=" . urlencode("Invalid request method."));
    exit();
}


$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? ''); 


if (empty($email) || empty($password)) {
    $_SESSION['form_input_admin_login']['email'] = $email; 
    header("Location: ../admin_login.php?error=" . urlencode("Email and password are required."));
    exit();
}


$sql_admin_check = "SELECT w.W_id, w.Name, w.Email, w.Password
                    FROM Workers w
                    JOIN Admin a ON w.W_id = a.A_id
                    WHERE w.Email = ?";


if ($stmt = $mysqli->prepare($sql_admin_check)) { 

    $stmt->bind_param("s", $email);

    if (!$stmt->execute()) {
        error_log("MySQLi Execute Error (Admin Login - SQL execution): " . $stmt->error);
        $_SESSION['form_input_admin_login']['email'] = $email;
        $stmt->close();

        header("Location: ../admin_login.php?error=" . urlencode("Database query execution error."));
        exit();
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin_user = $result->fetch_assoc();


        if ($password === $admin_user['Password']) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin_user['W_id'];
            $_SESSION['admin_name'] = $admin_user['Name'];
            $_SESSION['admin_email'] = $admin_user['Email'];
            $_SESSION['user_role'] = 'admin'; 

            if (isset($_SESSION['form_input_admin_login'])) {
                unset($_SESSION['form_input_admin_login']);
            }

            $stmt->close();
            $mysqli->close();
            header("Location: ../admin/admin_dashboard.php");
            exit();
        } else {

            $_SESSION['form_input_admin_login']['email'] = $email;
            $stmt->close();
            $mysqli->close();
            header("Location: ../admin_login.php?error=" . urlencode("Invalid credentials."));
            exit();
        }
    } else {

        $_SESSION['form_input_admin_login']['email'] = $email;
        $stmt->close();
        $mysqli->close();
        header("Location: ../admin_login.php?error=" . urlencode("Invalid credentials or not an admin."));
        exit();
    }
} else {

    error_log("MySQLi Prepare Error (Admin Login - SQL prepare): " . $mysqli->error);

    header("Location: ../admin_login.php?error=" . urlencode("Database error occurred during login preparation."));
    exit();
}
?>
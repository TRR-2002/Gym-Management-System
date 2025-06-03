<?php


session_start();

require_once '../includes/auth_check_member.php';

require_once '../includes/db_connect.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $m_id = $_SESSION['user_id'];

    $category = trim($_POST['category'] ?? ''); 
    $text = trim($_POST['feedback_text'] ?? '');   

   
    $errors = [];
    $allowed_categories = ['General', 'Equipment', 'Classes', 'Staff', 'Facilities', 'Other'];
    if (empty($category) || !in_array($category, $allowed_categories)) {
        $errors[] = "Please select a valid feedback category.";
    }
    if (empty($text)) {
        $errors[] = "Feedback text cannot be empty.";
    }

    if (!empty($errors)) {

        $error_query_param = http_build_query(['error_msg' => implode("<br>", $errors)]);

        header("Location: ../member_submit_feedback.php?" . $error_query_param);
        exit();
    }


    $current_date = date('Y-m-d');

    $current_time = date('H:i:s');

    $sql_insert_feedback = "INSERT INTO Feedback (M_id, Category, Text, Date, Time) VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $mysqli->prepare($sql_insert_feedback)) {

        $stmt->bind_param("issss", $m_id, $category, $text, $current_date, $current_time);


        if ($stmt->execute()) {
            $stmt->close(); 
            $mysqli->close(); 
            $success_msg = "Thank you! Your feedback has been submitted successfully.";

            header("Location: ../member_dashboard.php?success_msg=" . urlencode($success_msg));
            exit();
        } else {

            error_log("MySQLi Execute Error (Feedback Insert): " . $stmt->error);
            $error_msg = "Failed to submit feedback. Please try again.";
        }
        $stmt->close();
    } else {

        error_log("MySQLi Prepare Error (Feedback Insert): " . $mysqli->error);
        $error_msg = "Database error during feedback submission. Please try again.";
    }


    $error_query_param = http_build_query(['error_msg' => $error_msg]);
    if (isset($mysqli)) $mysqli->close(); 
    header("Location: ../member_submit_feedback.php?" . $error_query_param);
    exit();

} else {
    header("Location: ../member_submit_feedback.php");
    exit();
}
?>
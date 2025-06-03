<?php


session_start();


if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'member') {
        header("Location: member_dashboard.php");
        exit();
    } elseif ($_SESSION['user_role'] === 'staff') {
        header("Location: staff_dashboard.php");
        exit();
    } elseif ($_SESSION['user_role'] === 'admin') {
        
        header("Location: index.php");
        exit();
    } else {
    

        session_unset();
        session_destroy();
    }
}

require_once 'includes/header.php'; 



if (isset($_GET['success_msg'])) {
    echo '<div class="message success">' . htmlspecialchars($_GET['success_msg']) . '</div>';
}
if (isset($_GET['error_msg'])) {
    echo '<div class="message error">' . htmlspecialchars($_GET['error_msg']) . '</div>';
}
?>

<div class="form-container">
    <h2>Member / Staff Login</h2>
    <p>Please enter your credentials.</p>

    <form action="actions/process_login.php" method="POST">
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required
                   value="<?php echo isset($_SESSION['form_input_login']['email']) ? htmlspecialchars($_SESSION['form_input_login']['email']) : ''; ?>">
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <button type="submit" class="button">Login</button>
        </div>
    </form>
    <p>New member? <a href="register.php">Register here</a>.</p>
    <hr style="margin-top: 25px; margin-bottom: 15px;">
    <p style="text-align:center;">
        <small>Are you an Administrator? <a href="admin_login.php" style="font-weight:bold;">Admin Login Portal</a></small>
    </p>
</div>

<?php
if (isset($_SESSION['form_input_login'])) {
    unset($_SESSION['form_input_login']);
}
require_once 'includes/footer.php'; 
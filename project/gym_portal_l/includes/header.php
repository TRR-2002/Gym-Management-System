<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = false;
$user_role = null;
$user_name = 'Guest'; 

if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    $is_logged_in = true;
    $user_role = $_SESSION['user_role']; 


    $user_name = htmlspecialchars($_SESSION['user_name'] ?? 'User');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav>
            <div id="branding">
                <h1><a href="index.php">Gym Portal</a></h1>
            </div>
            <ul>
                <?php if ($is_logged_in): ?>
                    <li>Welcome, <?php echo $user_name; ?> (<?php
                        
                        echo ucfirst($user_role);
                    ?>)!</li>
                    
                    <?php if ($user_role === 'member'): // Member-specific links ?>
                        <li><a href="member_dashboard.php">Dashboard</a></li>
                        <li><a href="member_my_plan.php">My Plan</a></li>
                        <li><a href="member_log_progress.php">Log Progress</a></li>
                        <li><a href="member_packages.php">Packages</a></li>
                        <li><a href="member_my_profile.php">My Profile</a></li>
                        <li><a href="member_submit_feedback.php">Submit Feedback</a></li>
                    <?php elseif ($user_role === 'staff'): // Staff-specific links ?>
                        <li><a href="staff_dashboard.php">Dashboard</a></li>
                        <li><a href="staff_manage_members.php">Manage Members</a></li>
                        <li><a href="staff_manage_plans.php">Manage Plans</a></li>
                        <li><a href="staff_my_profile.php">My Profile & Schedule</a></li>
                    <?php elseif ($user_role === 'admin'): /* Admin links placeholder */ ?>
                    <?php endif; ?>
                    
                    <li><a href="actions/process_logout.php">Logout</a></li>
                <?php else: // Display if user is a guest (not logged in) ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Member Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <div class="container">

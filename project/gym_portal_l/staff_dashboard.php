<?php


session_start();

require_once 'includes/auth_check_staff.php';

require_once 'includes/db_connect.php';


$s_id_staff = $_SESSION['staff_s_id'];

$staff_name = $_SESSION['user_name'];


$assigned_members_count = 0;
$created_plans_count = 0;
$todays_schedule_items = [];


$sql_members_count = "SELECT COUNT(*) AS member_count FROM Members WHERE S_id = ?";

if ($stmt_mc = $mysqli->prepare($sql_members_count)) {

    $stmt_mc->bind_param("i", $s_id_staff);

    
    $stmt_mc->execute();

    $result_mc = $stmt_mc->get_result();

    $row_mc = $result_mc->fetch_assoc();

    $assigned_members_count = $row_mc['member_count'];

    $stmt_mc->close();
} else {

    error_log("MySQLi Prepare Error (Staff Dashboard - Member Count): " . $mysqli->error);

}


$sql_plans_count = "SELECT COUNT(*) AS plan_count FROM Plan WHERE S_id = ?";
if ($stmt_pc = $mysqli->prepare($sql_plans_count)) {
    $stmt_pc->bind_param("i", $s_id_staff);
    $stmt_pc->execute();
    $result_pc = $stmt_pc->get_result();
    $row_pc = $result_pc->fetch_assoc();
    $created_plans_count = $row_pc['plan_count'];
    $stmt_pc->close();
} else {
    error_log("MySQLi Prepare Error (Staff Dashboard - Plan Count): " . $mysqli->error);
}

$today_date_string = date('Y-m-d');
$sql_schedule = "SELECT Start_Time, End_Time FROM Staff_Routine
                 WHERE S_id = ? AND Date = ? ORDER BY Start_Time ASC";
if ($stmt_schedule = $mysqli->prepare($sql_schedule)) {

    $stmt_schedule->bind_param("is", $s_id_staff, $today_date_string);
    $stmt_schedule->execute();
    $result_schedule = $stmt_schedule->get_result();

    $todays_schedule_items = $result_schedule->fetch_all(MYSQLI_ASSOC);
    $stmt_schedule->close();
} else {
    error_log("MySQLi Prepare Error (Staff Dashboard - Schedule Fetch): " . $mysqli->error);
}


require_once 'includes/header.php';
?>

<h2>Staff Dashboard</h2>
<p>Welcome, <?php

    echo htmlspecialchars($staff_name);
?>!</p>

<div class="dashboard-widgets">
    <div class="widget">
        <h3>My Assigned Members</h3>
        <p>You are currently assigned to <strong><?php echo $assigned_members_count; ?></strong> member(s).</p>
        <a href="staff_manage_members.php" class="button">Manage Members</a>
    </div>

    <div class="widget">
        <h3>My Created Plans</h3>
        <p>You have created <strong><?php echo $created_plans_count; ?></strong> fitness plan(s).</p>
        <a href="staff_manage_plans.php" class="button">Manage Plans</a>
    </div>

    <div class="widget">
        <h3>Today's Schedule (<?php echo date("l, F j, Y");?>)</h3>
        <?php if (!empty($todays_schedule_items)): ?>
            <ul>
                <?php foreach ($todays_schedule_items as $item):?>
                    <li>
                        <strong><?php

                            echo date("g:i A", strtotime($item['Start_Time'])); ?>
                        - <?php echo date("g:i A", strtotime($item['End_Time'])); ?></strong>
                        <?php 
                            
                        ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else:?>
            <p>No tasks scheduled for today in the system.</p>
        <?php endif; ?>
        <a href="staff_my_profile.php#schedule" class="button">View Full Profile & Schedule</a>

    </div>
</div>

<?php

require_once 'includes/footer.php';

?>
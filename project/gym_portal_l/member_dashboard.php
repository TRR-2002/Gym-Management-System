<?php


session_start();
require_once 'includes/auth_check_member.php';
require_once 'includes/db_connect.php';
require_once 'includes/utilities.php';

$m_id = $_SESSION['user_id'];




$subscription_type = $_SESSION['subscription_type'] ?? 'Inactive';
$member_name = htmlspecialchars($_SESSION['user_name'] ?? 'Member'); 
$p_id = $_SESSION['plan_id'] ?? null;
$s_id_assigned_staff = $_SESSION['assigned_staff_id'] ?? null;

$plan_details = null;
$assigned_staff_name = 'Not Assigned';
$progression_percentage = null;


$is_subscription_active = (strtolower($subscription_type) !== 'inactive' && $subscription_type !== null);

// ONLY if subs act + plan assign
if ($is_subscription_active && $p_id) {
    $sql_plan = "SELECT Plan_Name, Starting_Weight, Current_Weight, Goal_Weight
                 FROM Plan WHERE P_id = ?";
    if ($stmt_plan = $mysqli->prepare($sql_plan)) {
        $stmt_plan->bind_param("i", $p_id);
        $stmt_plan->execute();
        $result_plan = $stmt_plan->get_result();
        if ($result_plan->num_rows === 1) {
            $plan_details = $result_plan->fetch_assoc();
            if ($plan_details) {
                $progression_percentage = calculate_progression_percentage(
                    $plan_details['Starting_Weight'],
                    $plan_details['Current_Weight'],
                    $plan_details['Goal_Weight']
                );
            }
        }
        $stmt_plan->close();
    } else {
        error_log("MySQLi Prepare Error (Member Dashboard - Plan Fetch): " . $mysqli->error);
    }
}


if ($s_id_assigned_staff) {
    $sql_staff = "SELECT Name FROM Workers WHERE W_id = ?";
    if ($stmt_staff = $mysqli->prepare($sql_staff)) {
        $stmt_staff->bind_param("i", $s_id_assigned_staff);
        $stmt_staff->execute();
        $result_staff = $stmt_staff->get_result();
        if ($result_staff->num_rows === 1) {
            $staff_data = $result_staff->fetch_assoc();
            $assigned_staff_name = htmlspecialchars($staff_data['Name']);
        }
        $stmt_staff->close();
    } else {
        error_log("MySQLi Prepare Error (Member Dashboard - Staff Fetch): " . $mysqli->error);
    }
}

require_once 'includes/header.php';


if (isset($_GET['success_msg'])) {
    echo '<div class="message success">' . htmlspecialchars($_GET['success_msg']) . '</div>';
}
if (isset($_GET['error_msg'])) {
    echo '<div class="message error">' . htmlspecialchars($_GET['error_msg']) . '</div>';
}
if (isset($_GET['info_msg'])) {
    echo '<div class="message info">' . htmlspecialchars($_GET['info_msg']) . '</div>';
}
?>

<h2>Member Dashboard</h2>
<p>Welcome back, <?php echo $member_name; ?>!</p>
<p>Your Subscription: <strong><?php echo htmlspecialchars(ucfirst($subscription_type)); ?></strong></p>
<p>Assigned Trainer: <strong><?php echo $assigned_staff_name; ?></strong></p>

<div class="dashboard-widgets">
    <div class="widget">
        <h3>My Current Plan</h3>
        <?php if ($is_subscription_active): ?>
            <?php if ($p_id && $plan_details): ?>
                <h4><?php echo htmlspecialchars($plan_details['Plan_Name']); ?></h4>
                <p><strong>Goal Weight:</strong> <?php echo htmlspecialchars($plan_details['Goal_Weight'] ?? 'N/A'); ?> kg</p>
                <p><strong>Current Weight:</strong> <?php echo htmlspecialchars($plan_details['Current_Weight'] ?? 'N/A'); ?> kg</p>
                <?php if ($plan_details['Starting_Weight'] !== null): ?>
                     <p><strong>Starting Weight (this plan):</strong> <?php echo htmlspecialchars($plan_details['Starting_Weight']); ?> kg</p>
                <?php else: ?>
                    <p><small>Log your weight to set starting weight for this plan.</small></p>
                <?php endif; ?>

                <?php if ($progression_percentage !== null): ?>
                    <p><strong>Progress:</strong> <?php echo $progression_percentage; ?>% towards goal</p>
                    <div class="progress-bar-container">
                         <div class="progress-bar" style="width: <?php echo max(0, min(100, $progression_percentage)); ?>%;">
                            <?php echo round($progression_percentage, 1); ?>%
                         </div>
                    </div>
                <?php elseif(isset($plan_details['Starting_Weight']) && isset($plan_details['Current_Weight']) && isset($plan_details['Goal_Weight'])) : ?>
                     <p>Progress calculation pending or goal met/maintained.</p>
                <?php endif; ?>
                <a href="member_my_plan.php" class="button">View Full Plan Details</a>
            <?php elseif ($p_id && !$plan_details): ?>
                 <p>Could not load your plan details at this moment. Please try again or contact support.</p>
            <?php else: ?>
                <p>You do not have a fitness plan assigned yet. Please contact staff or check back later.</p>
            <?php endif; ?>
        <?php else:?>
            <p>Your subscription is inactive. Please <a href="member_packages.php" id="activatePackageLink" class="button button-activate">activate a package</a> to access fitness plans.</p>
        <?php endif; ?>
    </div>

    <div class="widget">
        <h3>Log Progress</h3>
        <?php if ($is_subscription_active && $p_id):?>
            <p>Update your current weight to track your fitness journey.</p>
            <a href="member_log_progress.php" class="button">Log My Weight</a>
        <?php else: ?>
            <p>Activate a subscription and get an assigned plan to log your progress.</p>
        <?php endif; ?>
    </div>

    <div class="widget">
        <h3>Account & Subscription</h3>
        <a href="member_my_profile.php" class="button">My Profile</a>
        <a href="member_packages.php" class="button">Manage Subscription</a>
    </div>

    <div class="widget">
        <h3>Feedback</h3>
        <p>We value your opinion! Share your thoughts on our services.</p>
        <a href="member_submit_feedback.php" class="button">Give Feedback</a>
    </div>
</div>
<?php
require_once 'includes/footer.php';
?>
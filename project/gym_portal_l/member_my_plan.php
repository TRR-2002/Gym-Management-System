<?php


session_start();
require_once 'includes/auth_check_member.php';
require_once 'includes/db_connect.php'; 
require_once 'includes/utilities.php';

$m_id = $_SESSION['user_id'];
$p_id = $_SESSION['plan_id'] ?? null;
$is_active_subscription = (strtolower($_SESSION['subscription_type'] ?? 'inactive') !== 'inactive');

$plan_details = null;
$plan_routine_items = [];
$progression_percentage = null;
$assigned_staff_name = 'Not Assigned';
$plan_creator_name = 'Unknown';

if ($is_active_subscription && $p_id) {
    $sql_plan = "
        SELECT p.Plan_Name, p.Starting_Weight, p.Current_Weight, p.Goal_Weight, p.Diet, w_creator.Name AS PlanCreatorName
        FROM Plan p
        LEFT JOIN Staff s_creator ON p.S_id = s_creator.S_id
        LEFT JOIN Workers w_creator ON s_creator.S_id = w_creator.W_id
        WHERE p.P_id = ?
    ";
    if ($stmt_plan = $mysqli->prepare($sql_plan)) {
        $stmt_plan->bind_param("i", $p_id);
        $stmt_plan->execute();
        $result_plan = $stmt_plan->get_result();
        if ($result_plan->num_rows === 1) {
            $plan_details = $result_plan->fetch_assoc();
            if ($plan_details) {
                $plan_creator_name = htmlspecialchars($plan_details['PlanCreatorName'] ?? 'Unknown');
                $progression_percentage = calculate_progression_percentage(
                    $plan_details['Starting_Weight'],
                    $plan_details['Current_Weight'],
                    $plan_details['Goal_Weight']
                );


                $sql_routine = "SELECT Day, Time, Exercise FROM Plan_Routine WHERE P_id = ?
                                ORDER BY FIELD(Day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), Time ASC";
                if ($stmt_routine = $mysqli->prepare($sql_routine)) {
                    $stmt_routine->bind_param("i", $p_id);
                    $stmt_routine->execute();
                    $result_routine = $stmt_routine->get_result();
                    $plan_routine_items = $result_routine->fetch_all(MYSQLI_ASSOC);
                    $stmt_routine->close();
                } else {
                    error_log("MySQLi Prepare Error (Member My Plan - Routine Fetch): " . $mysqli->error);
                }
            }
        }
        $stmt_plan->close();
    } else {
        error_log("MySQLi Prepare Error (Member My Plan - Plan Fetch): " . $mysqli->error);
        $_SESSION['message'] = "Error fetching plan details.";
        $_SESSION['message_type'] = "error";
    }

    if (isset($_SESSION['assigned_staff_id']) && $_SESSION['assigned_staff_id']) {
        $sql_assigned_staff = "SELECT Name FROM Workers WHERE W_id = ?";
        if ($stmt_as = $mysqli->prepare($sql_assigned_staff)) {
            $stmt_as->bind_param("i", $_SESSION['assigned_staff_id']);
            $stmt_as->execute();
            $result_as = $stmt_as->get_result();
            if ($result_as->num_rows === 1) {
                $staff_data = $result_as->fetch_assoc();
                $assigned_staff_name = htmlspecialchars($staff_data['Name']);
            }
            $stmt_as->close();
        } else {
             error_log("MySQLi Prepare Error (Member My Plan - Assigned Staff Fetch): " . $mysqli->error);
        }
    }
}

require_once 'includes/header.php';

?>
<h2>My Fitness Plan</h2>

<?php if (!$is_active_subscription): ?>
    <div class="message warning">Your subscription is not active. Please <a href="member_packages.php">activate a subscription</a> to view your personalized plan.</div>
<?php elseif (!$p_id): ?>
    <div class="message info">You do not currently have a fitness plan assigned. Please contact staff or check your dashboard.</div>
<?php elseif (!$plan_details): ?>
    <div class="message error">Could not load your plan details at this moment. If this issue persists, please contact support.</div>
<?php else: ?>
    <h3>Plan: <?php echo htmlspecialchars($plan_details['Plan_Name']); ?></h3>
    <p><strong>Assigned Trainer:</strong> <?php echo $assigned_staff_name; ?></p>
    <p><small><em>(Plan originally created by: <?php echo $plan_creator_name; ?>)</em></small></p>

    <h4>Plan Overview & Progress</h4>
    <p><strong>Goal Weight:</strong> <?php echo htmlspecialchars($plan_details['Goal_Weight'] ?? 'N/A'); ?> kg</p>
    <p><strong>Current Weight:</strong> <?php echo htmlspecialchars($plan_details['Current_Weight'] ?? 'N/A'); ?> kg</p>
    <?php if ($plan_details['Starting_Weight'] !== null): ?>
        <p><strong>Starting Weight (this plan):</strong> <?php echo htmlspecialchars($plan_details['Starting_Weight']); ?> kg</p>
    <?php endif; ?>

    <?php if ($progression_percentage !== null): ?>
        <p><strong>Your Progress:</strong> <?php echo $progression_percentage; ?>% towards goal</p>
         <div class="progress-bar-container">
             <div class="progress-bar" style="width: <?php echo max(0, min(100, $progression_percentage)); ?>%;">
                <?php echo round($progression_percentage, 1); ?>%
             </div>
        </div>
    <?php elseif(isset($plan_details['Starting_Weight']) && isset($plan_details['Current_Weight']) && isset($plan_details['Goal_Weight'])): ?>
        <p>Progress calculation pending or goal met/maintained.</p>
    <?php else: ?>
        <p>Log your current weight on the "Log Progress" page to see your progress percentage for this plan.</p>
    <?php endif; ?>

    <h4>Dietary Guidelines</h4>
    <div><?php echo nl2br(htmlspecialchars($plan_details['Diet'] ?? 'No dietary guidelines specified for this plan.')); ?></div>

    <h4>Exercise Routine</h4>
    <?php if (!empty($plan_routine_items)): ?>
        <table>
            <thead>
                <tr><th>Day</th><th>Time</th><th>Exercise</th></tr>
            </thead>
            <tbody>
                <?php foreach ($plan_routine_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['Day']); ?></td>
                        <td><?php echo htmlspecialchars($item['Time'] ?? 'Flexible'); ?></td>
                        <td><?php echo htmlspecialchars($item['Exercise']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No specific exercise routine details found for this plan. Please consult your trainer.</p>
    <?php endif; ?>
<?php endif; ?>

<p style="margin-top: 20px;"><a href="member_dashboard.php" class="button">« Back to Dashboard</a></p>
<?php
require_once 'includes/footer.php';
?>
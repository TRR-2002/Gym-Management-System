<?php


session_start();
require_once 'includes/auth_check_staff.php'; 
require_once 'includes/db_connect.php';    
require_once 'includes/utilities.php';      


$s_id_staff_viewer = $_SESSION['staff_s_id'];

$m_id_member_to_view = filter_input(INPUT_GET, 'm_id', FILTER_VALIDATE_INT);


if (!$m_id_member_to_view) {

    $error_msg = "Invalid Member ID specified.";
    header("Location: staff_manage_members.php?error_msg=" . urlencode($error_msg));
    exit();
}

















$member_profile = null;                
$member_assigned_plan = null;          
$member_assigned_plan_routine = [];    
$progression_percentage = null;        
$staff_created_plans_for_assignment = []; 


$sql_member_profile = "
    SELECT m.M_id, m.Name AS MemberName, m.Email AS MemberEmail, m.Date_of_Birth, m.Address, m.Phone_No,
           m.Subscription_Type, m.P_id AS Current_P_id, m.S_id AS Current_Assigned_S_id,
           w_assigned.Name AS CurrentAssignedStaffName
    FROM Members m
    LEFT JOIN Staff s_assigned ON m.S_id = s_assigned.S_id
    LEFT JOIN Workers w_assigned ON s_assigned.S_id = w_assigned.W_id
    WHERE m.M_id = ?
";
if ($stmt_mp = $mysqli->prepare($sql_member_profile)) {
    $stmt_mp->bind_param("i", $m_id_member_to_view);
    $stmt_mp->execute();
    $result_mp = $stmt_mp->get_result();
    if ($result_mp->num_rows === 1) {
        $member_profile = $result_mp->fetch_assoc();
    }
    $stmt_mp->close();
} else {
    error_log("MySQLi Prepare Error (Staff Member Details - Profile Fetch): " . $mysqli->error);
    $_SESSION['temp_error_msg_smd'] = "Database error fetching member profile."; 
}

if (!$member_profile) {
    $error_msg = $_SESSION['temp_error_msg_smd'] ?? "Member not found.";
    unset($_SESSION['temp_error_msg_smd']);
    header("Location: staff_manage_members.php?error_msg=" . urlencode($error_msg));
    exit();
}















if ($member_profile['Current_Assigned_S_id'] !== null && $member_profile['Current_Assigned_S_id'] != $s_id_staff_viewer) {
    $error_msg = "You do not have permission to manage this member as they are assigned to another trainer.";
    header("Location: staff_manage_members.php?error_msg=" . urlencode($error_msg));
    exit();
}


if ($member_profile['Current_P_id']) {





    $sql_plan = "
        SELECT p.Plan_Name, p.Starting_Weight, p.Current_Weight, p.Goal_Weight, p.Diet,
               w_creator.Name AS PlanCreatorName
        FROM Plan p
        LEFT JOIN Staff s_creator ON p.S_id = s_creator.S_id
        LEFT JOIN Workers w_creator ON s_creator.S_id = w_creator.W_id
        WHERE p.P_id = ?
    ";






    if ($stmt_p = $mysqli->prepare($sql_plan)) {
        $stmt_p->bind_param("i", $member_profile['Current_P_id']);
        $stmt_p->execute();
        $result_p = $stmt_p->get_result();
        if ($result_p->num_rows === 1) {
            $member_assigned_plan = $result_p->fetch_assoc();
            if ($member_assigned_plan) {
                $progression_percentage = calculate_progression_percentage(
                    $member_assigned_plan['Starting_Weight'],
                    $member_assigned_plan['Current_Weight'],




                    $member_assigned_plan['Goal_Weight']
                );
                // Fetch routine for the assigned plan
                $sql_routine = "SELECT Day, Time, Exercise FROM Plan_Routine WHERE P_id = ?
                                ORDER BY FIELD(Day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), Time ASC";
                if ($stmt_r = $mysqli->prepare($sql_routine)) {
                    $stmt_r->bind_param("i", $member_profile['Current_P_id']);
                    $stmt_r->execute();
                    $result_r = $stmt_r->get_result();
                    $member_assigned_plan_routine = $result_r->fetch_all(MYSQLI_ASSOC);
                    $stmt_r->close();
                } else { error_log("MySQLi Prepare Error (Staff Member Details - Routine): " . $mysqli->error); }
            }
        }
        $stmt_p->close();
    } else { error_log("MySQLi Prepare Error (Staff Member Details - Plan): " . $mysqli->error); }
}

$sql_staff_plans = "SELECT P_id, Plan_Name FROM Plan WHERE S_id = ? ORDER BY Plan_Name ASC";
if ($stmt_sp = $mysqli->prepare($sql_staff_plans)) {
    $stmt_sp->bind_param("i", $s_id_staff_viewer);
    $stmt_sp->execute();
    $result_sp = $stmt_sp->get_result();
    $staff_created_plans_for_assignment = $result_sp->fetch_all(MYSQLI_ASSOC);
    $stmt_sp->close();
} else { error_log("MySQLi Prepare Error (Staff Member Details - Staff Plans): " . $mysqli->error); }


require_once 'includes/header.php';

if (isset($_GET['error_msg'])) {
    echo '<div class="message error">' . htmlspecialchars($_GET['error_msg']) . '</div>';
}
if (isset($_GET['success_msg'])) {
    echo '<div class="message success">' . htmlspecialchars($_GET['success_msg']) . '</div>';
}
if (isset($_GET['info_msg'])) {
    echo '<div class="message info">' . htmlspecialchars($_GET['info_msg']) . '</div>';
}
?>

<h2>Member Details: <?php echo htmlspecialchars($member_profile['MemberName']); // MemberName from query ?></h2>

<div class="member-profile-staff-view section">
    <h3>Profile Information</h3>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($member_profile['MemberEmail']); ?></p>
    <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($member_profile['Date_of_Birth'] ? date("F j, Y", strtotime($member_profile['Date_of_Birth'])) : 'N/A'); ?></p>
    <p><strong>Address:</strong> <?php echo htmlspecialchars($member_profile['Address'] ?? 'N/A'); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($member_profile['Phone_No'] ?? 'N/A'); ?></p>
    <p><strong>Subscription:</strong> <?php echo htmlspecialchars(ucfirst($member_profile['Subscription_Type'] ?? 'N/A')); ?></p>
    <p><strong>Currently Assigned Trainer:</strong>
        <?php echo htmlspecialchars($member_profile['CurrentAssignedStaffName'] ?? 'None (Assigning a plan will assign to you)'); ?>
    </p>
</div>
<hr>

<div class="section">
    <h3>Current Fitness Plan & Progress</h3>
    <?php if ($member_assigned_plan): ?>
        <h4>Plan: <?php echo htmlspecialchars($member_assigned_plan['Plan_Name']); ?></h4>
        <p><small><em>(Plan originally created by: <?php echo htmlspecialchars($member_assigned_plan['PlanCreatorName'] ?? 'Unknown'); ?>)</em></small></p>
        <p><strong>Goal Weight:</strong> <?php echo htmlspecialchars($member_assigned_plan['Goal_Weight'] ?? 'N/A'); ?> kg</p>
        <p><strong>Current Weight:</strong> <?php echo htmlspecialchars($member_assigned_plan['Current_Weight'] ?? 'N/A'); ?> kg</p>
        <?php if ($member_assigned_plan['Starting_Weight'] !== null) : ?>
             <p><strong>Starting Weight (this plan):</strong> <?php echo htmlspecialchars($member_assigned_plan['Starting_Weight']); ?> kg</p>
        <?php else: ?>
             <p><small>Member has not yet logged their weight for this plan.</small></p>
        <?php endif; ?>

        <?php if ($progression_percentage !== null): ?>
            <p><strong>Progress: <?php echo $progression_percentage; ?>%</strong> towards goal.</p>
            <div class="progress-bar-container">
                 <div class="progress-bar" style="width: <?php echo max(0, min(100, $progression_percentage)); ?>%;">
                    <?php echo round($progression_percentage,1); ?>%
                 </div>
            </div>
        <?php endif; ?>

        <h4>Dietary Guidelines</h4>
        <div><?php echo nl2br(htmlspecialchars($member_assigned_plan['Diet'] ?? 'N/A')); ?></div>

        <h4>Exercise Routine</h4>
        <?php if (!empty($member_assigned_plan_routine)): ?>
            <table>
                <thead><tr><th>Day</th><th>Time</th><th>Exercise</th></tr></thead>
                <tbody>
                    <?php foreach ($member_assigned_plan_routine as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['Day']); ?></td>
                            <td><?php echo htmlspecialchars($item['Time'] ?? 'Flexible'); ?></td>
                            <td><?php echo htmlspecialchars($item['Exercise']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No exercise routine defined for this member's current plan.</p>
        <?php endif; ?>
    <?php else: ?>
        <p>This member does not currently have an active fitness plan assigned.</p>
    <?php endif; ?>
</div>

<hr>
<div class="section form-container">
    <h3>Assign or Change Member's Plan</h3>
    <?php if (strtolower($member_profile['Subscription_Type'] ?? 'inactive') === 'inactive'): ?>
        <p class="message warning">This member's subscription is Inactive. They can be assigned a plan, but may not be able to fully utilize it until their subscription is active.</p>
    <?php endif; ?>

    <form action="actions/process_staff_assign_plan.php" method="POST">
        <input type="hidden" name="member_m_id" value="<?php echo $m_id_member_to_view; ?>">
    
        <input type="hidden" name="redirect_to" value="../staff_member_details.php?m_id=<?php echo $m_id_member_to_view; ?>">
        <div>
            <label for="plan_p_id">Select Plan to Assign (from your created plans):</label>
            <select name="plan_p_id" id="plan_p_id" required>
                <option value="">-- Select a Plan --</option>
                <?php if (!empty($staff_created_plans_for_assignment)): ?>
                    <?php foreach ($staff_created_plans_for_assignment as $plan_option): ?>
                        <option value="<?php echo $plan_option['P_id']; ?>"
                            <?php
                                echo ($member_profile['Current_P_id'] == $plan_option['P_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($plan_option['Plan_Name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>You have not created any plans yet to assign.</option>
                <?php endif; ?>
            </select>
        </div>
        <?php 
        if ($member_profile['Current_Assigned_S_id'] != $s_id_staff_viewer && $member_profile['Current_Assigned_S_id'] !== null): ?>
            <p class="message warning">Note: This member is currently assigned to another trainer. Assigning a plan will reassign them to you.</p>
        <?php elseif ($member_profile['Current_Assigned_S_id'] === null): ?>
             <p class="message info">Note: This member is currently unassigned. Assigning a plan will assign them to you.</p>
        <?php endif; ?>
        <button type="submit" class="button button-positive" <?php echo empty($staff_created_plans_for_assignment) ? 'disabled' : ''; ?>>
            <?php echo $member_profile['Current_P_id'] ? 'Change Assigned Plan' : 'Assign Plan to Member'; ?>
        </button>
        <?php if ($member_profile['Current_P_id']): ?>
             <span style="margin-left:10px; font-style:italic;"><small>(This will replace their current plan assignment if different.)</small></span>
        <?php endif; ?>
    </form>
    <p><small>Assigning a plan also makes you the primary trainer for this member. The assigned plan's Starting/Current weights will be reset for this member to log fresh progress.</small></p>
</div>

<p style="margin-top: 20px;"><a href="staff_manage_members.php" class="button">« Back to Manage Members</a></p>

<?php
require_once 'includes/footer.php';

?>
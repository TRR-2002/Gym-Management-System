<?php









session_start();




require_once 'includes/auth_check_staff.php'; 
require_once 'includes/db_connect.php';     
require_once 'includes/utilities.php';      


$s_id_staff = $_SESSION['staff_s_id'];


$assigned_members_list = [];


$sql_assigned_members = "
    SELECT m.M_id, m.Name AS MemberName, m.Email AS MemberEmail, m.Subscription_Type,
           p.P_id AS Assigned_P_id, p.Plan_Name AS Assigned_Plan_Name,
           p.Starting_Weight, p.Current_Weight, p.Goal_Weight
    FROM Members m
    LEFT JOIN Plan p ON m.P_id = p.P_id
    WHERE m.S_id = ?
    ORDER BY m.Name ASC 
";

if ($stmt_assigned = $mysqli->prepare($sql_assigned_members)) {
    
    $stmt_assigned->bind_param("i", $s_id_staff);

    $stmt_assigned->execute();

    $result_assigned = $stmt_assigned->get_result();

    $assigned_members_list = $result_assigned->fetch_all(MYSQLI_ASSOC);

    $stmt_assigned->close();
} else {

    error_log("MySQLi Prepare Error (Staff Manage Members - Fetch): " . $mysqli->error);

    $_SESSION['temp_error_msg'] = "Error fetching your assigned members list.";
}

require_once 'includes/header.php';


if (isset($_SESSION['temp_error_msg'])) {
    echo '<div class="message error">' . htmlspecialchars($_SESSION['temp_error_msg']) . '</div>';
    unset($_SESSION['temp_error_msg']); 
}
?>

<h2>My Assigned Members</h2>

<?php if (!empty($assigned_members_list)): ?>
    <table>
        <thead>
            <tr>
                <th>Member Name</th>
                <th>Email</th>
                <th>Subscription</th>
                <th>Assigned Plan</th>
                <th>Progress</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($assigned_members_list as $member):?>
                <tr>
                    <td><?php echo htmlspecialchars($member['MemberName']);?></td>
                    <td><?php echo htmlspecialchars($member['MemberEmail']); ?></td>
                    <td><?php

                        echo htmlspecialchars(ucfirst($member['Subscription_Type'] ?? 'N/A'));
                    ?></td>
                    <td><?php echo htmlspecialchars($member['Assigned_Plan_Name'] ?? 'None'); ?></td>
                    <td>
                        <?php

                        if ($member['Assigned_P_id']) {

                            $progression = calculate_progression_percentage(
                                $member['Starting_Weight'],
                                $member['Current_Weight'],
                                $member['Goal_Weight']
                            );
                            
                            echo ($progression !== null) ? $progression . '%' : '<small>Log weight</small>';
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </td>
                    <td>
                        <?php ?>
                        <a href="staff_member_details.php?m_id=<?php echo $member['M_id'];?>" class="button">View / Manage</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>You do not have any members currently assigned to you.</p>
<?php endif; ?>



<p style="margin-top: 20px;"><a href="staff_dashboard.php" class="button">« Back to Staff Dashboard</a></p>

<?php
require_once 'includes/footer.php';

?>
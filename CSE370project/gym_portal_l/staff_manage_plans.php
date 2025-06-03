<?php


session_start();

require_once 'includes/auth_check_staff.php';
require_once 'includes/db_connect.php';    

$s_id_staff = $_SESSION['staff_s_id'];

$staff_created_plans_list = [];


$sql_fetch_plans = "SELECT P_id, Plan_Name, Goal_Weight, Diet
                    FROM Plan
                    WHERE S_id = ?  
                    ORDER BY Plan_Name ASC 
                   ";


if ($stmt = $mysqli->prepare($sql_fetch_plans)) {

    $stmt->bind_param("i", $s_id_staff);

    $stmt->execute();

    $result = $stmt->get_result();

    $staff_created_plans_list = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
} else {

    error_log("MySQLi Prepare Error (Staff Manage Plans - Fetch): " . $mysqli->error);

}

require_once 'includes/header.php';

if (isset($_GET['error_msg'])) {
    echo '<div class="message error">' . htmlspecialchars($_GET['error_msg']) . '</div>';
}
if (isset($_GET['success_msg'])) {
    echo '<div class="message success">' . htmlspecialchars($_GET['success_msg']) . '</div>';
}
?>

<h2>Manage My Fitness Plans</h2>
<p><a href="staff_create_plan.php" class="button button-positive">Create New Fitness Plan</a></p>

<?php if (!empty($staff_created_plans_list)):?>
    <table>
        <thead>
            <tr>






                <th>Plan Name</th>
                <th>Default Goal Weight (Template)</th>
                <th>Diet Summary (First ~50 Chars)</th>
                <th>Actions</th>






            </tr>
        </thead>
        <tbody>
            <?php foreach ($staff_created_plans_list as $plan):?>
                <tr>
                    <td><?php echo htmlspecialchars($plan['Plan_Name']); ?></td>
                    <td><?php echo htmlspecialchars($plan['Goal_Weight'] ?? 'N/A'); ?> kg</td>
                    <td>
                        <?php
                        $diet_summary = $plan['Diet'] ?? 'No diet specified.';

                        echo htmlspecialchars(mb_substr(strip_tags($diet_summary), 0, 50));

                        if (mb_strlen(strip_tags($diet_summary)) > 50) {
                            echo "...";
                        }
                        ?>
                    </td>
                    <td>
                        <a href="staff_edit_plan.php?p_id=<?php echo $plan['P_id'];?>" class="button">Edit</a>
                        <a href="actions/process_staff_delete_plan.php?p_id=<?php echo $plan['P_id'];?>"
                           class="button button-danger"
                           onclick="return confirm('Are you sure you want to delete the plan \'<?php


                                echo htmlspecialchars(addslashes($plan['Plan_Name']));
                           ?>\'? This will unassign it from any members and cannot be undone.');">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p>You have not created any fitness plans yet.
        <a href="staff_create_plan.php">Why not create one now?</a>
    </p>
<?php endif; ?>

<p style="margin-top: 20px;"><a href="staff_dashboard.php" class="button">« Back to Staff Dashboard</a></p>

<?php
require_once 'includes/footer.php'; 

?>
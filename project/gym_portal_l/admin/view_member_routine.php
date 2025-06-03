<?php
$page_title = "View Member's Fitness Routine";
require_once '../includes/auth_check_admin.php';
require_once '../includes/admin_header.php'; 
if (!isset($_GET['member_id']) || !filter_var($_GET['member_id'], FILTER_VALIDATE_INT)) {
    $_SESSION['message'] = "Invalid or missing Member ID.";
    $_SESSION['message_type'] = "error";
    header("Location: manage_members.php");
    exit();
}
$member_id = (int)$_GET['member_id'];

$member_data = null;
$plan_details = null;
$plan_routines = [];

try {

    $stmt_member = $pdo->prepare("SELECT M_id, Name, Email, P_id FROM Members WHERE M_id = :member_id");
    $stmt_member->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $stmt_member->execute();
    $member_data = $stmt_member->fetch(PDO::FETCH_ASSOC);

    if (!$member_data) {
        $_SESSION['message'] = "Member with ID {$member_id} not found.";
        $_SESSION['message_type'] = "error";
        header("Location: manage_members.php");
        exit();
    }
    $page_title = "Routine for " . htmlspecialchars($member_data['Name']);


    if ($member_data['P_id']) {
        $assigned_plan_id = $member_data['P_id'];


        $stmt_plan = $pdo->prepare("SELECT P_id, Diet, Goal_Weight, Current_Weight, S_id 
                                     FROM Plan WHERE P_id = :plan_id");
        $stmt_plan->bindParam(':plan_id', $assigned_plan_id, PDO::PARAM_INT);
        $stmt_plan->execute();
        $plan_details = $stmt_plan->fetch(PDO::FETCH_ASSOC);

        if ($plan_details) {

            $stmt_routines = $pdo->prepare("SELECT Day, Time, Exercise 
                                             FROM Plan_Routine 
                                             WHERE P_id = :plan_id 
                                             ORDER BY FIELD(Day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), Time ASC");
            $stmt_routines->bindParam(':plan_id', $assigned_plan_id, PDO::PARAM_INT);
            $stmt_routines->execute();
            $plan_routines = $stmt_routines->fetchAll(PDO::FETCH_ASSOC);
        } else {

            $_SESSION['message'] = "Warning: Member is assigned Plan ID {$assigned_plan_id}, but this plan was not found in the database.";
            $_SESSION['message_type'] = "warning"; 
        }
    }

} catch (PDOException $e) {
    error_log("Error on view member routine page: " . $e->getMessage());
    $page_db_error = "Database error fetching routine details: " . $e->getMessage();
   
}


$days_of_week_ordered = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

?>

<div class="container-fluid">
  
    <?php if ($member_data): ?>
        <h1 class="h3 mb-1 text-gray-800"><?php echo htmlspecialchars($page_title); ?></h1>
        <p class="mb-3">
            <strong>Member ID:</strong> <?php echo htmlspecialchars($member_data['M_id']); ?> | 
            <strong>Email:</strong> <?php echo htmlspecialchars($member_data['Email']); ?>
        </p>
    <?php else: ?>
        <h1 class="h3 mb-2 text-gray-800">View Member Routine</h1>
    <?php endif; ?>

    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-' . ($_SESSION['message_type'] == 'success' ? 'success' : ($_SESSION['message_type'] == 'warning' ? 'warning' : 'danger')) . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($_SESSION['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    if (isset($page_db_error)) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($page_db_error) . '</div>';
    }
    ?>

    <?php if ($member_data && $member_data['P_id'] && $plan_details): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Assigned Plan Details (Plan ID: <?php echo htmlspecialchars($plan_details['P_id']); ?>)
                     <a href="plan_form.php?action=edit&plan_id=<?php echo $plan_details['P_id']; ?>" class="btn btn-sm btn-outline-info float-end">
                        <i class="bi bi-pencil-square"></i> Edit This Plan Template
                    </a>
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Goal Weight:</strong> <?php echo htmlspecialchars($plan_details['Goal_Weight'] ?? 'N/A'); ?> kg</p>
                        <p><strong>Member's Current Logged Weight for this Plan:</strong> <?php echo htmlspecialchars($plan_details['Current_Weight'] ?? 'Not logged yet'); ?> kg</p>
                    </div>
                    <div class="col-md-6">
                        <?php if ($plan_details['S_id']): ?>
                        <p><strong>Plan Managed by Staff ID:</strong> <?php echo htmlspecialchars($plan_details['S_id']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <h5>Dietary Guidelines:</h5>
                <p style="white-space: pre-wrap; background-color: #f8f9fa; padding: 10px; border-radius: 4px;"><?php echo htmlspecialchars($plan_details['Diet'] ?? 'No diet information available.'); ?></p>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Exercise Routine Schedule</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($plan_routines)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Exercise</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($plan_routines as $routine): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($routine['Day']); ?></td>
                                    <td><?php echo htmlspecialchars($routine['Time']); ?></td>
                                    <td><?php echo htmlspecialchars($routine['Exercise']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">No exercise routines found for this member's assigned plan.</div>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($member_data && !$member_data['P_id']): ?>
        <div class="alert alert-warning">
            This member (<?php echo htmlspecialchars($member_data['Name']); ?>) does not currently have a fitness plan assigned.
            <br>
            <a href="assign_plan_to_member.php?member_id=<?php echo $member_data['M_id']; ?>" class="btn btn-sm btn-success mt-2">
                <i class="bi bi-journal-plus"></i> Assign a Plan
            </a>
        </div>
    <?php elseif ($member_data && $member_data['P_id'] && !$plan_details): ?>
        
        <div class="alert alert-danger">
             The plan (ID: <?php echo htmlspecialchars($member_data['P_id']); ?>) assigned to this member could not be loaded. It may have been deleted.
             Please assign a new plan.
             <br>
            <a href="assign_plan_to_member.php?member_id=<?php echo $member_data['M_id']; ?>" class="btn btn-sm btn-warning mt-2">
                <i class="bi bi-journal-plus"></i> Re-assign Plan
            </a>
        </div>
    <?php endif; ?>
    
    <a href="manage_members.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Back to Manage Members</a>

</div>


<?php
require_once '../includes/admin_footer.php';
?>
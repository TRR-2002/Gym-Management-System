<?php
$page_title = "Assign Specific Plan to Members";
require_once '../includes/auth_check_admin.php';
require_once '../includes/admin_header.php'; 

if (!isset($_GET['plan_id']) || !filter_var($_GET['plan_id'], FILTER_VALIDATE_INT)) {
    $_SESSION['message'] = "Invalid or missing Plan ID.";
    $_SESSION['message_type'] = "error";
    header("Location: manage_plans.php");
    exit();
}
$plan_id_to_assign = (int)$_GET['plan_id'];

$plan_data = null;
$all_members = [];

try {
   
    $stmt_plan = $pdo->prepare("SELECT P_id, Diet, Goal_Weight FROM Plan WHERE P_id = :plan_id");
    $stmt_plan->bindParam(':plan_id', $plan_id_to_assign, PDO::PARAM_INT);
    $stmt_plan->execute();
    $plan_data = $stmt_plan->fetch(PDO::FETCH_ASSOC);

    if (!$plan_data) {
        $_SESSION['message'] = "Plan with ID {$plan_id_to_assign} not found.";
        $_SESSION['message_type'] = "error";
        header("Location: manage_plans.php");
        exit();
    }


    $stmt_members = $pdo->query("SELECT M_id, Name, Email, P_id AS Current_Plan_ID FROM Members ORDER BY Name ASC");
    $all_members = $stmt_members->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error on assign specific plan page: " . $e->getMessage());
    $_SESSION['message'] = "Database error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
  
}

?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800"><?php echo $page_title; ?></h1>

    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-' . ($_SESSION['message_type'] == 'success' ? 'success' : 'danger') . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($_SESSION['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    ?>

    <?php if ($plan_data): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Assigning Plan ID: <?php echo htmlspecialchars($plan_data['P_id']); ?>
            </h6>
        </div>
        <div class="card-body">
            <p><strong>Goal Weight:</strong> <?php echo htmlspecialchars($plan_data['Goal_Weight'] ?? 'N/A'); ?> kg</p>
            <p><strong>Diet Snippet:</strong> <?php echo htmlspecialchars(substr($plan_data['Diet'] ?? 'N/A', 0, 150)) . (strlen($plan_data['Diet'] ?? '') > 150 ? '...' : ''); ?></p>
            <a href="plan_form.php?action=edit&plan_id=<?php echo $plan_data['P_id']; ?>" class="btn btn-sm btn-outline-info mb-3">View/Edit Full Plan Details</a>
            <hr>

            <h5>Select Members to Assign This Plan To:</h5>
            <?php if (!empty($all_members)): ?>
            <form action="../actions/process_assign_specific_plan.php" method="POST">
                <input type="hidden" name="plan_id_to_assign" value="<?php echo htmlspecialchars($plan_id_to_assign); ?>">
                
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-hover" id="memberSelectionTable">
                        <thead>
                            <tr>
                                <th style="width: 5%;"><input type="checkbox" id="selectAllMembers"></th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Currently Assigned Plan ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_members as $member): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input member-checkbox" 
                                               name="member_ids[]" value="<?php echo htmlspecialchars($member['M_id']); ?>"
                                               <?php echo ($member['Current_Plan_ID'] == $plan_id_to_assign) ? 'checked disabled' : ''; ?> >
                                        <?php if ($member['Current_Plan_ID'] == $plan_id_to_assign): ?>
                                            <small class="text-muted">(Already Assigned)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($member['Name']); ?></td>
                                    <td><?php echo htmlspecialchars($member['Email']); ?></td>
                                    <td>
                                        <?php 
                                            echo htmlspecialchars($member['Current_Plan_ID'] ?? 'None'); 
                                            if ($member['Current_Plan_ID'] && $member['Current_Plan_ID'] != $plan_id_to_assign) {
                                                echo ' <small class="text-warning">(Will be overwritten)</small>';
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <button type="submit" name="assign_to_selected_members" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Assign Plan to Selected Members
                </button>
                 <a href="manage_plans.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel & Back to Plans
                </a>
            </form>
            <?php else: ?>
                <div class="alert alert-warning">No members found in the system to assign this plan to.</div>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-danger">Plan data could not be loaded.</div>
        <a href="manage_plans.php" class="btn btn-secondary">Back to Manage Plans</a>
    <?php endif; ?>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAllMembers');
    const memberCheckboxes = document.querySelectorAll('.member-checkbox:not(:disabled)');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            memberCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    }

    memberCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (!checkbox.checked) {
                if(selectAllCheckbox) selectAllCheckbox.checked = false;
            } else {
             
                let allChecked = true;
                memberCheckboxes.forEach(cb => {
                    if (!cb.checked) allChecked = false;
                });
                if(selectAllCheckbox) selectAllCheckbox.checked = allChecked;
            }
        });
    });
});
</script>

<?php
require_once '../includes/admin_footer.php';
?>
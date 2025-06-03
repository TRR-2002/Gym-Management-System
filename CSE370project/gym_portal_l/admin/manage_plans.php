<?php
$page_title = "Manage Fitness Plans";
require_once '../includes/auth_check_admin.php';
require_once '../includes/admin_header.php';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['plan_id'])) {
    $plan_id_to_delete = filter_input(INPUT_GET, 'plan_id', FILTER_VALIDATE_INT);
    if ($plan_id_to_delete) {
        try {
            $pdo->beginTransaction();

            $stmt_delete_routines = $pdo->prepare("DELETE FROM Plan_Routine WHERE P_id = :plan_id");
            $stmt_delete_routines->bindParam(':plan_id', $plan_id_to_delete, PDO::PARAM_INT);
            $stmt_delete_routines->execute();

            $stmt_unassign_members = $pdo->prepare("UPDATE Members SET P_id = NULL WHERE P_id = :plan_id");
            $stmt_unassign_members->bindParam(':plan_id', $plan_id_to_delete, PDO::PARAM_INT);
            $stmt_unassign_members->execute();
            
            $stmt_delete_plan = $pdo->prepare("DELETE FROM Plan WHERE P_id = :plan_id");
            $stmt_delete_plan->bindParam(':plan_id', $plan_id_to_delete, PDO::PARAM_INT);
            
            if ($stmt_delete_plan->execute()) {
                $pdo->commit();
                $_SESSION['message'] = "Plan (ID: {$plan_id_to_delete}) and its associated routines deleted successfully. Members previously on this plan have been unassigned.";
                $_SESSION['message_type'] = "success";
            } else {
                $pdo->rollBack();
                $_SESSION['message'] = "Failed to delete plan.";
                $_SESSION['message_type'] = "error";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error deleting plan: " . $e->getMessage());
            $_SESSION['message'] = "Database error deleting plan. Check logs. Details: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Invalid plan ID for deletion.";
        $_SESSION['message_type'] = "error";
    }
    header("Location: manage_plans.php");
    exit();
}

try {
    $stmt = $pdo->query("SELECT p.P_id, p.Goal_Weight, p.Diet, p.S_id, w.Name as StaffName,
                         (SELECT COUNT(*) FROM Plan_Routine pr WHERE pr.P_id = p.P_id) as RoutineCount,
                         (SELECT COUNT(*) FROM Members m WHERE m.P_id = p.P_id) as AssignedMemberCount
                         FROM Plan p
                         LEFT JOIN Workers w ON p.S_id = w.W_id 
                         ORDER BY p.P_id ASC"); 
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $plans = [];
    $page_error = "Database error fetching plans: " . $e->getMessage();
    error_log($page_error);
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800"><?php echo $page_title; ?></h1>
    <p class="mb-4">Create, view, edit, and delete fitness plan templates. These plans can then be assigned to members.</p>

    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-' . ($_SESSION['message_type'] == 'success' ? 'success' : 'danger') . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($_SESSION['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    if (isset($page_error)) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($page_error) . '</div>';
    }
    ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Plan Templates
                <a href="plan_form.php?action=add" class="btn btn-primary btn-sm float-end">
                    <i class="bi bi-plus-circle"></i> Add New Plan Template
                </a>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTablePlans" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Plan ID (P_id)</th>
                            <th>Goal Weight (kg)</th>
                            <th>Diet Snippet</th>
                            <th>Created by Staff</th>
                            <th># Routines</th>
                            <th># Members Assigned</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($plans)): ?>
                            <?php foreach ($plans as $plan): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($plan['P_id']); ?></td>
                                <td><?php echo htmlspecialchars($plan['Goal_Weight'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(substr($plan['Diet'] ?? 'N/A', 0, 70)) . (strlen($plan['Diet'] ?? '') > 70 ? '...' : ''); ?></td>
                                <td><?php echo htmlspecialchars($plan['StaffName'] ?? ($plan['S_id'] ? 'Staff ID: '.$plan['S_id'] : 'N/A')); ?></td>
                                <td><?php echo htmlspecialchars($plan['RoutineCount']); ?></td>
                                <td><?php echo htmlspecialchars($plan['AssignedMemberCount']); ?></td>
                                <td>
                                    <a href="plan_form.php?action=edit&plan_id=<?php echo $plan['P_id']; ?>" class="btn btn-info btn-sm mb-1" title="Edit Plan & Routines">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <a href="assign_specific_plan.php?plan_id=<?php echo $plan['P_id']; ?>" class="btn btn-success btn-sm mb-1" title="Assign This Plan to Member(s)">
                                        <i class="bi bi-person-plus"></i> Assign
                                    </a>
                                    <a href="manage_plans.php?action=delete&plan_id=<?php echo $plan['P_id']; ?>" 
                                       class="btn btn-danger btn-sm mb-1" title="Delete Plan"
                                       onclick="return confirm('Are you sure you want to delete this plan (ID: <?php echo $plan['P_id']; ?>) and all its routines? Members will be unassigned from this plan.');">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No plan templates found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/admin_footer.php';
?>
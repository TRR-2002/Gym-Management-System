<?php
$page_title = "Assign Plan to Member";
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
$available_plans = [];
$current_plan_details = null;

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

    $stmt_plans = $pdo->query("SELECT P_id, Diet, Goal_Weight, S_id FROM Plan ORDER BY P_id ASC");
    $available_plans = $stmt_plans->fetchAll(PDO::FETCH_ASSOC);

    if ($member_data['P_id']) {
        $stmt_current_plan = $pdo->prepare("SELECT P_id, Diet, Goal_Weight, S_id FROM Plan WHERE P_id = :p_id");
        $stmt_current_plan->bindParam(':p_id', $member_data['P_id'], PDO::PARAM_INT);
        $stmt_current_plan->execute();
        $current_plan_details = $stmt_current_plan->fetch(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    error_log("Error on assign plan page: " . $e->getMessage());
    $_SESSION['message'] = "Database error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_plan'])) {
    $selected_plan_id_input = $_POST['plan_id'] ?? '';
    $selected_plan_id = (!empty($selected_plan_id_input) && filter_var($selected_plan_id_input, FILTER_VALIDATE_INT)) ? (int)$selected_plan_id_input : null;

    if ($selected_plan_id_input === '' || $selected_plan_id_input === 'none') {
        $selected_plan_id = null;
    }

    try {
        if ($selected_plan_id !== null) {
            $stmt_check_plan = $pdo->prepare("SELECT P_id FROM Plan WHERE P_id = :p_id");
            $stmt_check_plan->bindParam(':p_id', $selected_plan_id, PDO::PARAM_INT);
            $stmt_check_plan->execute();
            if (!$stmt_check_plan->fetch()) {
                $_SESSION['message'] = "Invalid Plan ID selected. The plan does not exist.";
                $_SESSION['message_type'] = "error";
                header("Location: assign_plan_to_member.php?member_id=" . $member_id);
                exit();
            }
        }

        $stmt_update_member = $pdo->prepare("UPDATE Members SET P_id = :p_id WHERE M_id = :member_id");
        $stmt_update_member->bindParam(':p_id', $selected_plan_id, ($selected_plan_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT));
        $stmt_update_member->bindParam(':member_id', $member_id, PDO::PARAM_INT);

        if ($stmt_update_member->execute()) {
            $_SESSION['message'] = "Plan " . ($selected_plan_id ? "ID {$selected_plan_id}" : "None") . " assigned to member " . htmlspecialchars($member_data['Name']) . " successfully.";
            $_SESSION['message_type'] = "success";
            
            $member_data['P_id'] = $selected_plan_id;
            if ($selected_plan_id) {
                 $stmt_current_plan = $pdo->prepare("SELECT P_id, Diet, Goal_Weight, S_id FROM Plan WHERE P_id = :p_id");
                 $stmt_current_plan->bindParam(':p_id', $member_data['P_id'], PDO::PARAM_INT);
                 $stmt_current_plan->execute();
                 $current_plan_details = $stmt_current_plan->fetch(PDO::FETCH_ASSOC);
            } else {
                $current_plan_details = null;
            }
        } else {
            $_SESSION['message'] = "Failed to assign plan.";
            $_SESSION['message_type'] = "error";
        }
    } catch (PDOException $e) {
        error_log("Error assigning plan: " . $e->getMessage());
        $_SESSION['message'] = "Database error assigning plan: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }
    header("Location: assign_plan_to_member.php?member_id=" . $member_id);
    exit();
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

    <?php if ($member_data): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Member: <?php echo htmlspecialchars($member_data['Name']); ?> (ID: <?php echo htmlspecialchars($member_data['M_id']); ?>)
            </h6>
        </div>
        <div class="card-body">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($member_data['Email']); ?></p>
            
            <?php if ($current_plan_details): ?>
                <div class="alert alert-info">
                    <strong>Currently Assigned Plan (ID: <?php echo htmlspecialchars($current_plan_details['P_id']); ?>):</strong><br>
                    Goal Weight: <?php echo htmlspecialchars($current_plan_details['Goal_Weight'] ?? 'N/A'); ?> kg<br>
                    Diet Snippet: <?php echo htmlspecialchars(substr($current_plan_details['Diet'] ?? 'N/A', 0, 100)) . (strlen($current_plan_details['Diet'] ?? '') > 100 ? '...' : ''); ?><br>
                    <?php if ($current_plan_details['S_id']): ?>
                        <small class="text-muted">Managed by Staff ID: <?php echo htmlspecialchars($current_plan_details['S_id']); ?></small><br>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p class="text-muted"><em>No plan currently assigned to this member.</em></p>
            <?php endif; ?>

            <hr>
            <h5>Assign a New Plan or Change Current Plan:</h5>
            <?php if (!empty($available_plans)): ?>
            <form action="assign_plan_to_member.php?member_id=<?php echo $member_id; ?>" method="POST">
                <div class="row align-items-end">
                    <div class="col-md-8 mb-3">
                        <label for="plan_id" class="form-label">Select a Plan:</label>
                        <select class="form-select" id="plan_id" name="plan_id">
                            <option value="none">-- Unassign Plan (None) --</option>
                            <?php foreach ($available_plans as $plan): ?>
                                <?php
                                $diet_snippet = htmlspecialchars(substr($plan['Diet'] ?? 'No diet info', 0, 30));
                                if (strlen($plan['Diet'] ?? '') > 30) $diet_snippet .= "...";
                                $goal_weight = htmlspecialchars($plan['Goal_Weight'] ?? 'N/A');
                                $staff_id_info = $plan['S_id'] ? " (Staff: " . htmlspecialchars($plan['S_id']) . ")" : "";

                                $plan_display_name = "Plan ID " . htmlspecialchars($plan['P_id']) . 
                                                     " (Goal: {$goal_weight}kg, Diet: {$diet_snippet}){$staff_id_info}";
                                
                                $selected = '';
                                if (isset($_GET['preselect_plan_id']) && $_GET['preselect_plan_id'] == $plan['P_id']) {
                                    $selected = 'selected';
                                } elseif (!isset($_GET['preselect_plan_id']) && $member_data['P_id'] == $plan['P_id']) {
                                    $selected = 'selected';
                                }
                                ?>
                                <option value="<?php echo htmlspecialchars($plan['P_id']); ?>" <?php echo $selected; ?>>
                                    <?php echo $plan_display_name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <button type="submit" name="assign_plan" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle"></i> Assign/Update Plan
                        </button>
                    </div>
                </div>
            </form>
            <?php else: ?>
                <div class="alert alert-warning">
                    No fitness plans are currently available in the system. 
                    <a href="manage_plans.php?action=add">Create a new plan first.</a>
                </div>
            <?php endif; ?>
            
            <hr>
            <a href="manage_members.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Back to Manage Members</a>
            <a href="manage_plans.php" class="btn btn-info float-end"><i class="bi bi-journals"></i> Go to Manage All Plans</a>

        </div>
    </div>
    <?php else: ?>
        <?php if (isset($_SESSION['message']) && $_SESSION['message_type'] == 'error'): ?>
        <?php else: ?>
            <div class="alert alert-danger">Member data could not be loaded or an error occurred.</div>
        <?php endif; ?>
        <a href="manage_members.php" class="btn btn-secondary">Back to Manage Members</a>
    <?php endif; ?>
</div>

<?php
require_once '../includes/admin_footer.php';
?>
<?php
$action = $_GET['action'] ?? 'add';
$plan_id = null;
$plan_data = null;
$plan_routines = [];
$available_staff = [];

if ($action === 'edit') {
    if (!isset($_GET['plan_id']) || !filter_var($_GET['plan_id'], FILTER_VALIDATE_INT)) {
        $_SESSION['message'] = "Invalid or missing Plan ID for editing.";
        $_SESSION['message_type'] = "error";
        header("Location: manage_plans.php");
        exit();
    }
    $plan_id = (int)$_GET['plan_id'];
    $page_title = "Edit Fitness Plan (ID: {$plan_id})";
} else {
    $page_title = "Add New Fitness Plan";
}

require_once '../includes/auth_check_admin.php';
require_once '../includes/admin_header.php';

try {
    $stmt_staff = $pdo->query("SELECT w.W_id, w.Name FROM Workers w JOIN Staff s ON w.W_id = s.S_id ORDER BY w.Name ASC");
    $available_staff = $stmt_staff->fetchAll(PDO::FETCH_ASSOC);

    if ($action === 'edit' && $plan_id) {
        $stmt_plan = $pdo->prepare("SELECT P_id, Diet, Goal_Weight, S_id, Current_Weight FROM Plan WHERE P_id = :plan_id");
        $stmt_plan->bindParam(':plan_id', $plan_id, PDO::PARAM_INT);
        $stmt_plan->execute();
        $plan_data = $stmt_plan->fetch(PDO::FETCH_ASSOC);

        if (!$plan_data) {
            $_SESSION['message'] = "Plan with ID {$plan_id} not found.";
            $_SESSION['message_type'] = "error";
            header("Location: manage_plans.php");
            exit();
        }

        $stmt_routines = $pdo->prepare("SELECT Day, Time, Exercise FROM Plan_Routine WHERE P_id = :plan_id ORDER BY FIELD(Day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), Time ASC");
        $stmt_routines->bindParam(':plan_id', $plan_id, PDO::PARAM_INT);
        $stmt_routines->execute();
        $plan_routines = $stmt_routines->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error fetching data for plan form: " . $e->getMessage());
    $_SESSION['message'] = "Database error loading plan form data: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}

$days_of_week = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800"><?php echo $page_title; ?></h1>
    <p class="mb-4">
        <?php echo ($action === 'edit' ? 'Update the details and routines for this fitness plan.' : 'Fill out the form to create a new fitness plan template.'); ?>
    </p>

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

    <form action="../actions/process_admin_plan.php" method="POST">
        <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
        <?php if ($action === 'edit' && $plan_id): ?>
            <input type="hidden" name="plan_id" value="<?php echo htmlspecialchars($plan_id); ?>">
        <?php endif; ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Plan Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="goal_weight" class="form-label">Goal Weight (kg) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="goal_weight" name="goal_weight" step="0.1" min="0"
                               value="<?php echo htmlspecialchars($plan_data['Goal_Weight'] ?? ''); ?>" required placeholder="e.g., 70.5">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="s_id" class="form-label">Assign to Staff (Optional)</label>
                        <select class="form-select" id="s_id" name="s_id">
                            <option value="">-- None --</option>
                            <?php foreach ($available_staff as $staff): ?>
                                <option value="<?php echo htmlspecialchars($staff['W_id']); ?>" 
                                    <?php echo (isset($plan_data['S_id']) && $plan_data['S_id'] == $staff['W_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($staff['Name']); ?> (ID: <?php echo htmlspecialchars($staff['W_id']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                 <div class="mb-3">
                    <label for="current_weight_note" class="form-label">Initial Current Weight (kg) (Optional)</label>
                    <input type="number" class="form-control" id="current_weight" name="current_weight" step="0.1" min="0"
                           value="<?php echo htmlspecialchars($plan_data['Current_Weight'] ?? ''); ?>" placeholder="Initial weight if known">
                    <small class="form-text text-muted">This is an initial value for the plan template; members will log their own current weight.</small>
                </div>
                <div class="mb-3">
                    <label for="diet" class="form-label">Diet Plan / Guidelines <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="diet" name="diet" rows="5" required><?php echo htmlspecialchars($plan_data['Diet'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Plan Routines (Exercise Schedule)</h6>
                <button type="button" class="btn btn-success btn-sm" id="addRoutineRow">
                    <i class="bi bi-plus-circle"></i> Add Routine Entry
                </button>
            </div>
            <div class="card-body">
                <div id="routineContainer" class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th style="width: 20%;">Day <span class="text-danger">*</span></th>
                                <th style="width: 20%;">Time <span class="text-danger">*</span></th>
                                <th style="width: 50%;">Exercise <span class="text-danger">*</span></th>
                                <th style="width: 10%;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="routineTableBody">
                            <?php if (!empty($plan_routines)): ?>
                                <?php foreach ($plan_routines as $index => $routine): ?>
                                    <tr class="routine-row">
                                        <td>
                                            <select class="form-select form-select-sm" name="routines[<?php echo $index; ?>][day]" required>
                                                <?php foreach ($days_of_week as $day): ?>
                                                    <option value="<?php echo $day; ?>" <?php echo ($routine['Day'] == $day) ? 'selected' : ''; ?>><?php echo $day; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control form-control-sm" name="routines[<?php echo $index; ?>][time]" value="<?php echo htmlspecialchars($routine['Time']); ?>" placeholder="e.g., 09:00 AM or Morning" required></td>
                                        <td><input type="text" class="form-control form-control-sm" name="routines[<?php echo $index; ?>][exercise]" value="<?php echo htmlspecialchars($routine['Exercise']); ?>" placeholder="e.g., Bench Press 3x10" required></td>
                                        <td><button type="button" class="btn btn-danger btn-sm removeRoutineRow"><i class="bi bi-trash"></i></button></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (empty($plan_routines) && $action == 'add'): ?>
                    <p class="text-muted text-center mt-2">No routines added yet. Click "Add Routine Entry" to begin.</p>
                <?php endif; ?>
            </div>
        </div>

        <hr>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle"></i> <?php echo ($action === 'edit' ? 'Update Plan' : 'Create Plan'); ?>
        </button>
        <a href="manage_plans.php" class="btn btn-secondary">
            <i class="bi bi-x-circle"></i> Cancel
        </a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const routineContainer = document.getElementById('routineTableBody');
    const addRoutineButton = document.getElementById('addRoutineRow');
    let routineIndex = <?php echo !empty($plan_routines) ? count($plan_routines) : 0; ?>; 

    addRoutineButton.addEventListener('click', function () {
        const newRow = document.createElement('tr');
        newRow.classList.add('routine-row');
        newRow.innerHTML = `
            <td>
                <select class="form-select form-select-sm" name="routines[${routineIndex}][day]" required>
                    <?php foreach ($days_of_week as $day): ?>
                        <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input type="text" class="form-control form-control-sm" name="routines[${routineIndex}][time]" placeholder="e.g., 10:00 AM or Afternoon" required></td>
            <td><input type="text" class="form-control form-control-sm" name="routines[${routineIndex}][exercise]" placeholder="e.g., Squats 4x8" required></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRoutineRow"><i class="bi bi-trash"></i></button></td>
        `;
        routineContainer.appendChild(newRow);
        routineIndex++;
    });

    routineContainer.addEventListener('click', function (e) {
        if (e.target.closest('.removeRoutineRow')) {
            e.target.closest('tr').remove();
        }
    });
});
</script>

<?php
require_once '../includes/admin_footer.php';
?>
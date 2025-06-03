<?php
$action = $_GET['action'] ?? 'add'; 
$staff_id = null; 
$staff_data = null; 

if ($action === 'edit') {
    if (!isset($_GET['staff_id']) || !filter_var($_GET['staff_id'], FILTER_VALIDATE_INT)) {
        $_SESSION['message'] = "Invalid or missing Staff ID for editing.";
        $_SESSION['message_type'] = "error";
        header("Location: manage_staff.php");
        exit();
    }
    $staff_id = (int)$_GET['staff_id'];
    $page_title = "Edit Staff Member (ID: {$staff_id})";
} else {
    $page_title = "Add New Staff Member";
}

require_once '../includes/auth_check_admin.php';
require_once '../includes/admin_header.php'; 

if ($action === 'edit' && $staff_id) {
    try {
        $stmt = $pdo->prepare("SELECT w.* 
                               FROM Workers w 
                               JOIN Staff s ON w.W_id = s.S_id 
                               WHERE w.W_id = :staff_id");
        $stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
        $stmt->execute();
        $staff_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$staff_data) {
            $_SESSION['message'] = "Staff member with ID {$staff_id} not found or is not designated as staff.";
            $_SESSION['message_type'] = "error";
            header("Location: manage_staff.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error fetching staff for edit: " . $e->getMessage());
        $_SESSION['message'] = "Database error fetching staff data. Check logs.";
        $_SESSION['message_type'] = "error";
        header("Location: manage_staff.php");
        exit();
    }
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800"><?php echo $page_title; ?></h1>
    <p class="mb-4">
        <?php echo ($action === 'edit' ? 'Update the details for this staff member.' : 'Fill out the form to add a new staff member.'); ?>
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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Staff Details</h6>
        </div>
        <div class="card-body">
            <form action="../actions/process_admin_staff.php" method="POST">
                <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
                <?php if ($action === 'edit' && $staff_id): ?>
                    <input type="hidden" name="staff_id" value="<?php echo htmlspecialchars($staff_id); ?>"> 
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($staff_data['Name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?php echo htmlspecialchars($staff_data['Email'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">
                            Password <?php echo ($action === 'add' ? '<span class="text-danger">*</span>' : '(Leave blank to keep current)'); ?>
                        </label>
                        <input type="password" class="form-control" id="password" name="password" 
                               <?php echo ($action === 'add' ? 'required minlength="6"' : 'minlength="6"'); ?>>
                        <?php if ($action === 'edit'): ?>
                            <small class="form-text text-muted">Enter a new password only if you want to change it.</small>
                        <?php endif; ?>
                    </div>
                     <?php if ($action === 'add'): ?>
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="designation" class="form-label">Designation <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="designation" name="designation"
                               value="<?php echo htmlspecialchars($staff_data['Designation'] ?? ''); ?>" required placeholder="e.g., Trainer, Front Desk, Manager">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone_no" class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone_no" name="phone_no"
                               value="<?php echo htmlspecialchars($staff_data['Phone_No'] ?? ''); ?>" required
                               pattern="[0-9\s\-\+\(\)]+" title="Enter a valid phone number">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($staff_data['Address'] ?? ''); ?></textarea>
                </div>
                
                <hr>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> <?php echo ($action === 'edit' ? 'Update Staff' : 'Add Staff'); ?>
                </button>
                <a href="manage_staff.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </form>
        </div>
    </div>
</div>

<?php
require_once '../includes/admin_footer.php';
?>
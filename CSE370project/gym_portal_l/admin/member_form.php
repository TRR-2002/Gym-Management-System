<?php
$action = $_GET['action'] ?? 'add'; 
$member_id = null;
$member_data = null; 

if ($action === 'edit') {
    if (!isset($_GET['member_id']) || !filter_var($_GET['member_id'], FILTER_VALIDATE_INT)) {
        $_SESSION['message'] = "Invalid or missing Member ID for editing.";
        $_SESSION['message_type'] = "error";
        header("Location: manage_members.php");
        exit();
    }
    $member_id = (int)$_GET['member_id'];
    $page_title = "Edit Member (ID: {$member_id})";
} else {
    $page_title = "Add New Member";
}

require_once '../includes/auth_check_admin.php';
require_once '../includes/admin_header.php'; 

if ($action === 'edit' && $member_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM Members WHERE M_id = :member_id");
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt->execute();
        $member_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$member_data) {
            $_SESSION['message'] = "Member with ID {$member_id} not found.";
            $_SESSION['message_type'] = "error";
            header("Location: manage_members.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error fetching member for edit: " . $e->getMessage());
        $_SESSION['message'] = "Database error fetching member data. Check logs.";
        $_SESSION['message_type'] = "error";
        header("Location: manage_members.php");
        exit();
    }
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800"><?php echo $page_title; ?></h1>
    <p class="mb-4">
        <?php echo ($action === 'edit' ? 'Update the details for this member.' : 'Fill out the form to add a new member.'); ?>
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
     if (isset($_GET['error_message'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars(urldecode($_GET['error_message'])) . '</div>';
    }
    ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Member Details</h6>
        </div>
        <div class="card-body">
            <form action="../actions/process_admin_member.php" method="POST">
                <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
                <?php if ($action === 'edit' && $member_id): ?>
                    <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($member_id); ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($member_data['Name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?php echo htmlspecialchars($member_data['Email'] ?? ''); ?>" required>
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
                        <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="dob" name="dob"
                               value="<?php echo htmlspecialchars($member_data['Date_of_Birth'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone_no" class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone_no" name="phone_no"
                               value="<?php echo htmlspecialchars($member_data['Phone_No'] ?? ''); ?>" required
                               pattern="[0-9\s\-\+\(\)]+" title="Enter a valid phone number">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($member_data['Address'] ?? ''); ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="subscription_type" class="form-label">Subscription Type</label>
                        <select class="form-select" id="subscription_type" name="subscription_type">
                            <option value="" <?php echo (!isset($member_data['Subscription_Type']) || empty($member_data['Subscription_Type'])) ? 'selected' : ''; ?>>Inactive / None</option>
                            <option value="Basic Monthly" <?php echo (isset($member_data['Subscription_Type']) && $member_data['Subscription_Type'] == 'Basic Monthly') ? 'selected' : ''; ?>>Basic Monthly</option>
                            <option value="Premium Monthly" <?php echo (isset($member_data['Subscription_Type']) && $member_data['Subscription_Type'] == 'Premium Monthly') ? 'selected' : ''; ?>>Premium Monthly</option>
                            <option value="Premium Annual" <?php echo (isset($member_data['Subscription_Type']) && $member_data['Subscription_Type'] == 'Premium Annual') ? 'selected' : ''; ?>>Premium Annual</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="p_id" class="form-label">Assigned Plan ID (Optional)</label>
                        <input type="number" class="form-control" id="p_id" name="p_id"
                               value="<?php echo htmlspecialchars($member_data['P_id'] ?? ''); ?>"
                               placeholder="e.g., 1 (Leave blank if no plan)">
                        <small class="form-text text-muted">Assign a plan by its ID. Manage plans separately.</small>
                    </div>
                </div>

                <hr>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> <?php echo ($action === 'edit' ? 'Update Member' : 'Add Member'); ?>
                </button>
                <a href="manage_members.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </form>
        </div>
    </div>
</div>

<?php
require_once '../includes/admin_footer.php';
?>
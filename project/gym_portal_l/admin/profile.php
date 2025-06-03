<?php
$page_title = "My Admin Profile";
require_once '../includes/auth_check_admin.php'; 
require_once '../includes/admin_header.php';     

$admin_w_id = $_SESSION['admin_id']; 
$admin_data = null;

try {
    $stmt = $pdo->prepare("SELECT w.W_id, w.Name, w.Email, w.Designation, w.Address, w.Phone_No 
                           FROM Workers w
                           JOIN Admin a ON w.W_id = a.A_id
                           WHERE w.W_id = :admin_w_id");
    $stmt->bindParam(':admin_w_id', $admin_w_id, PDO::PARAM_INT);
    $stmt->execute();
    $admin_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin_data) {
        $_SESSION['message'] = "Admin profile not found or access issue.";
        $_SESSION['message_type'] = "error";
        header("Location: admin_dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching admin profile: " . $e->getMessage());
    $_SESSION['message'] = "Database error fetching your profile. Check logs.";
    $_SESSION['message_type'] = "error";
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800"><?php echo $page_title; ?></h1>
    <p class="mb-4">View and update your personal information and password.</p>

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

    <?php if ($admin_data): ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Update Your Details</h6>
                </div>
                <div class="card-body">
                    <form action="../actions/process_admin_profile.php" method="POST">
                        <input type="hidden" name="admin_w_id" value="<?php echo htmlspecialchars($admin_w_id); ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($admin_data['Name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo htmlspecialchars($admin_data['Email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                             <div class="col-md-6 mb-3">
                                <label for="designation" class="form-label">Designation <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="designation" name="designation"
                                       value="<?php echo htmlspecialchars($admin_data['Designation'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone_no" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone_no" name="phone_no"
                                       value="<?php echo htmlspecialchars($admin_data['Phone_No'] ?? ''); ?>" required
                                       pattern="[0-9\s\-\+\(\)]+" title="Enter a valid phone number">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($admin_data['Address'] ?? ''); ?></textarea>
                        </div>
                        
                        <hr>
                        <h6 class="mt-4 mb-3">Change Password (Optional)</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" minlength="6">
                                <small class="form-text text-muted">Required if changing password.</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" minlength="6">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" minlength="6">
                            </div>
                        </div>
                        
                        <hr>
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Profile
                        </button>
                        <a href="admin_dashboard.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
             <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Information</h6>
                </div>
                <div class="card-body">
                    <p><strong>Admin ID (W_id):</strong> <?php echo htmlspecialchars($admin_data['W_id']); ?></p>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($admin_data['Name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($admin_data['Email']); ?></p>
                    <p><strong>Designation:</strong> <?php echo htmlspecialchars($admin_data['Designation']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($admin_data['Phone_No']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($admin_data['Address']); ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-warning">Could not load your profile data. Please try again or contact support.</div>
    <?php endif; ?>
</div>

<?php
require_once '../includes/admin_footer.php';
?>
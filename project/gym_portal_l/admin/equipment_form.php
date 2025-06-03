<?php
$action = $_GET['action'] ?? 'add';
$equipment_id = null;
$equipment_data = null;

if ($action === 'edit') {
    if (!isset($_GET['equipment_id']) || !filter_var($_GET['equipment_id'], FILTER_VALIDATE_INT)) {
        $_SESSION['message'] = "Invalid or missing Equipment ID for editing.";
        $_SESSION['message_type'] = "error";
        header("Location: manage_equipment.php");
        exit();
    }
    $equipment_id = (int)$_GET['equipment_id'];
    $page_title = "Edit Equipment (ID: {$equipment_id})";
} else {
    $page_title = "Add New Equipment";
}

require_once '../includes/auth_check_admin.php';
require_once '../includes/admin_header.php';

if ($action === 'edit' && $equipment_id) {
    try {
        
        $stmt = $pdo->prepare("SELECT E_id, E_name, Cost, Quantity, Date_of_Purchase, V_name, V_contact FROM Equipment WHERE E_id = :equipment_id");
        $stmt->bindParam(':equipment_id', $equipment_id, PDO::PARAM_INT);
        $stmt->execute();
        $equipment_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$equipment_data) {
            $_SESSION['message'] = "Equipment with ID {$equipment_id} not found.";
            $_SESSION['message_type'] = "error";
            header("Location: manage_equipment.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error fetching equipment for edit: " . $e->getMessage());
        $_SESSION['message'] = "Database error fetching equipment data. Check logs.";
        $_SESSION['message_type'] = "error";
        header("Location: manage_equipment.php");
        exit();
    }
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800"><?php echo $page_title; ?></h1>
    <p class="mb-4">
        <?php echo ($action === 'edit' ? 'Update the details for this piece of equipment.' : 'Fill out the form to add new equipment.'); ?>
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
            <h6 class="m-0 font-weight-bold text-primary">Equipment Details</h6>
        </div>
        <div class="card-body">
            <form action="../actions/process_admin_equipment.php" method="POST">
                <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
                <?php if ($action === 'edit' && $equipment_id): ?>
                    <input type="hidden" name="equipment_id" value="<?php echo htmlspecialchars($equipment_id); ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="e_name" class="form-label">Equipment Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="e_name" name="e_name" 
                           value="<?php echo htmlspecialchars($equipment_data['E_name'] ?? ''); ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cost" class="form-label">Cost (e.g., 1250.99)</label>
                        <input type="number" class="form-control" id="cost" name="cost" step="0.01" min="0"
                               value="<?php echo htmlspecialchars($equipment_data['Cost'] ?? ''); ?>" placeholder="0.00">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="0"
                               value="<?php echo htmlspecialchars($equipment_data['Quantity'] ?? '1'); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="date_of_purchase" class="form-label">Date of Purchase</label>
                    <input type="date" class="form-control" id="date_of_purchase" name="date_of_purchase"
                           value="<?php echo htmlspecialchars($equipment_data['Date_of_Purchase'] ?? ''); ?>">
                </div>
                
                <hr>
                <h5 class="mt-4 mb-3">Vendor Information (Optional)</h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="v_name" class="form-label">Vendor Name</label>
                        <input type="text" class="form-control" id="v_name" name="v_name"
                               value="<?php echo htmlspecialchars($equipment_data['V_name'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="v_contact" class="form-label">Vendor Contact (Phone/Email)</label>
                        <input type="text" class="form-control" id="v_contact" name="v_contact"
                               value="<?php echo htmlspecialchars($equipment_data['V_contact'] ?? ''); ?>">
                    </div>
                </div>

                <hr>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> <?php echo ($action === 'edit' ? 'Update Equipment' : 'Add Equipment'); ?>
                </button>
                <a href="manage_equipment.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </form>
        </div>
    </div>
</div>


<?php
require_once '../includes/admin_footer.php';
?>
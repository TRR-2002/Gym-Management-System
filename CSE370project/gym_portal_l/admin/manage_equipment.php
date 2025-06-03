<?php
$page_title = "Manage Equipment";
require_once '../includes/auth_check_admin.php';
require_once '../includes/admin_header.php'; 

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['equipment_id'])) {
    $equipment_id_to_delete = filter_input(INPUT_GET, 'equipment_id', FILTER_VALIDATE_INT);
    if ($equipment_id_to_delete) {
        try {
            

            $sql_delete_equipment = "DELETE FROM Equipment WHERE E_id = :equipment_id";
            $stmt_delete_equipment = $pdo->prepare($sql_delete_equipment);
            $stmt_delete_equipment->bindParam(':equipment_id', $equipment_id_to_delete, PDO::PARAM_INT);
            
            if ($stmt_delete_equipment->execute()) {
                $_SESSION['message'] = "Equipment (ID: {$equipment_id_to_delete}) deleted successfully.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Failed to delete equipment.";
                $_SESSION['message_type'] = "error";
            }
        } catch (PDOException $e) {
            error_log("Error deleting equipment: " . $e->getMessage());
            
            if ($e->getCode() == '23000') { 
                 $_SESSION['message'] = "Cannot delete equipment (ID: {$equipment_id_to_delete}) as it might be referenced elsewhere (e.g., in use, maintenance logs). Please resolve dependencies first.";
            } else {
                $_SESSION['message'] = "Database error deleting equipment. Check logs.";
            }
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Invalid equipment ID for deletion.";
        $_SESSION['message_type'] = "error";
    }
    header("Location: manage_equipment.php");
    exit();
}


try {
    $stmt = $pdo->query("SELECT E_id, Name, Cost, Quantity, Date_of_Purchase, V_name, V_contact FROM Equipment ORDER BY Name ASC");
    $equipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $equipments = [];
    $page_error = "Database error fetching equipment: " . $e->getMessage();
    error_log($page_error);
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800"><?php echo $page_title; ?></h1>
    <p class="mb-4">Manage gym equipment inventory, including purchase details and vendor information.</p>

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
                Equipment List
                <a href="equipment_form.php?action=add" class="btn btn-primary btn-sm float-end">
                    <i class="bi bi-plus-circle"></i> Add New Equipment
                </a>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableEquipment" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name (Name)</th>
                            <th>Cost</th>
                            <th>Quantity</th>
                            <th>Purchase Date</th>
                            <th>Vendor Name</th>
                            <th>Vendor Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($equipments)): ?>
                            <?php foreach ($equipments as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['E_id']); ?></td>
                                <td><?php echo htmlspecialchars($item['Name'] ?? 'N/A'); ?></td> 
                                <td><?php echo htmlspecialchars(number_format((float)($item['Cost'] ?? 0), 2)); ?></td>
                                <td><?php echo htmlspecialchars($item['Quantity'] ?? 0); ?></td>
                                <td><?php echo htmlspecialchars($item['Date_of_Purchase'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['V_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['V_contact'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="equipment_form.php?action=edit&equipment_id=<?php echo $item['E_id']; ?>" class="btn btn-info btn-sm mb-1" title="Edit Equipment">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="manage_equipment.php?action=delete&equipment_id=<?php echo $item['E_id']; ?>" 
                                       class="btn btn-danger btn-sm mb-1" title="Delete Equipment"
                                       onclick="return confirm('Are you sure you want to delete this equipment (ID: <?php echo $item['E_id']; ?>)?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No equipment found.</td>
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
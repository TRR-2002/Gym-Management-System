<?php
$page_title = "Manage Staff";
require_once '../includes/auth_check_admin.php';
require_once '../includes/admin_header.php'; 

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['staff_id'])) {
    $staff_id_to_delete = filter_input(INPUT_GET, 'staff_id', FILTER_VALIDATE_INT);
    if ($staff_id_to_delete) {
        try {
            $pdo->beginTransaction();


            $stmt_delete_staff_role = $pdo->prepare("DELETE FROM Staff WHERE S_id = :staff_id");
            $stmt_delete_staff_role->bindParam(':staff_id', $staff_id_to_delete, PDO::PARAM_INT);
            $stmt_delete_staff_role->execute();

            $stmt_nullify_plans = $pdo->prepare("UPDATE Plan SET S_id = NULL WHERE S_id = :staff_id");
            $stmt_nullify_plans->bindParam(':staff_id', $staff_id_to_delete, PDO::PARAM_INT);
            $stmt_nullify_plans->execute();
            

            $stmt_delete_worker = $pdo->prepare("DELETE FROM Workers WHERE W_id = :staff_id");
            $stmt_delete_worker->bindParam(':staff_id', $staff_id_to_delete, PDO::PARAM_INT);
            
            if ($stmt_delete_worker->execute()) {
                $pdo->commit();
                $_SESSION['message'] = "Staff member (ID: {$staff_id_to_delete}) deleted successfully.";
                $_SESSION['message_type'] = "success";
            } else {
                $pdo->rollBack();
                $_SESSION['message'] = "Failed to delete staff worker record.";
                $_SESSION['message_type'] = "error";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error deleting staff: " . $e->getMessage());
            $_SESSION['message'] = "Database error deleting staff member. Check logs. Details: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Invalid staff ID for deletion.";
        $_SESSION['message_type'] = "error";
    }
    header("Location: manage_staff.php");
    exit();
}

// Fetch all staff members
try {
    $stmt = $pdo->query("SELECT w.W_id, w.Name, w.Email, w.Designation, w.Phone_No 
                         FROM Workers w
                         JOIN Staff s ON w.W_id = s.S_id
                         ORDER BY w.Name ASC");
    $staff_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $staff_members = [];
    $page_error = "Database error fetching staff members: " . $e->getMessage();
    error_log($page_error);
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800"><?php echo $page_title; ?></h1>
    <p class="mb-4">Manage staff and trainer accounts. Add new staff, edit existing details, or remove them.</p>

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
                Staff List
                <a href="staff_form.php?action=add" class="btn btn-primary btn-sm float-end">
                    <i class="bi bi-plus-circle"></i> Add New Staff
                </a>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableStaff" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID (W_id)</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Designation</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($staff_members)): ?>
                            <?php foreach ($staff_members as $staff): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($staff['W_id']); ?></td>
                                <td><?php echo htmlspecialchars($staff['Name']); ?></td>
                                <td><?php echo htmlspecialchars($staff['Email']); ?></td>
                                <td><?php echo htmlspecialchars($staff['Designation'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($staff['Phone_No'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="staff_form.php?action=edit&staff_id=<?php echo $staff['W_id']; ?>" class="btn btn-info btn-sm mb-1" title="Edit Staff">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="view_staff_routine.php?staff_id=<?php echo $staff['W_id']; ?>" class="btn btn-secondary btn-sm mb-1" title="View Staff Routine/Schedule">
                                        <i class="bi bi-calendar-week"></i>
                                    </a>
                                    <a href="manage_staff.php?action=delete&staff_id=<?php echo $staff['W_id']; ?>" 
                                       class="btn btn-danger btn-sm mb-1" title="Delete Staff"
                                       onclick="return confirm('Are you sure you want to delete this staff member (ID: <?php echo $staff['W_id']; ?>)? This action will remove their staff role and worker record.');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No staff members found.</td>
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
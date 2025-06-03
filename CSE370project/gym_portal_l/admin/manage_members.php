<?php
$page_title = "Manage Members";
require_once '../includes/auth_check_admin.php'; 
require_once '../includes/admin_header.php';    


if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['member_id'])) {
    $member_id_to_delete = filter_input(INPUT_GET, 'member_id', FILTER_VALIDATE_INT);
    if ($member_id_to_delete) {
        try {
            

            $pdo->beginTransaction();

            
            $stmt_delete_feedback = $pdo->prepare("DELETE FROM Feedback WHERE M_id = :member_id");
            $stmt_delete_feedback->bindParam(':member_id', $member_id_to_delete, PDO::PARAM_INT);
            $stmt_delete_feedback->execute();

            
            $sql_delete_member = "DELETE FROM Members WHERE M_id = :member_id";
            $stmt_delete_member = $pdo->prepare($sql_delete_member);
            $stmt_delete_member->bindParam(':member_id', $member_id_to_delete, PDO::PARAM_INT);
            
            if ($stmt_delete_member->execute()) {
                $pdo->commit();
                $_SESSION['message'] = "Member (ID: {$member_id_to_delete}) and their associated data deleted successfully.";
                $_SESSION['message_type'] = "success";
            } else {
                $pdo->rollBack();
                $_SESSION['message'] = "Failed to delete member.";
                $_SESSION['message_type'] = "error";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error deleting member: " . $e->getMessage());
            $_SESSION['message'] = "Database error deleting member. Check logs.";
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Invalid member ID for deletion.";
        $_SESSION['message_type'] = "error";
    }
    header("Location: manage_members.php"); 
    exit();
}


try {
    
    $stmt = $pdo->query("SELECT M_id, Name, Email, Date_of_Birth, Address, Phone_No, Subscription_Type, P_id FROM Members ORDER BY Name ASC");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $members = []; 
    $page_error = "Database error fetching members: " . $e->getMessage();
    error_log($page_error); 
}
?>

<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800"><?php echo $page_title; ?></h1>
    <p class="mb-4">View, add, edit, or delete member accounts. You can also manage their fitness plans and routines.</p>

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
                Members List
                <a href="member_form.php?action=add" class="btn btn-primary btn-sm float-end">
                    <i class="bi bi-plus-circle"></i> Add New Member
                </a>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableMembers" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Subscription</th>
                            <th>DOB</th>
                            <th>Address</th>
                            <th>Plan ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($members)): ?>
                            <?php foreach ($members as $member): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['M_id']); ?></td>
                                <td><?php echo htmlspecialchars($member['Name']); ?></td>
                                <td><?php echo htmlspecialchars($member['Email']); ?></td>
                                <td><?php echo htmlspecialchars($member['Phone_No']); ?></td>
                                <td><?php echo htmlspecialchars($member['Subscription_Type'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($member['Date_of_Birth']); ?></td>
                                <td><?php echo htmlspecialchars($member['Address']); ?></td>
                                <td><?php echo htmlspecialchars($member['P_id'] ?? 'None'); ?></td>
                                <td>
                                    <a href="member_form.php?action=edit&member_id=<?php echo $member['M_id']; ?>" class="btn btn-info btn-sm mb-1" title="Edit Member">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="assign_plan_to_member.php?member_id=<?php echo $member['M_id']; ?>" class="btn btn-success btn-sm mb-1" title="Assign/Edit Plan">
                                        <i class="bi bi-journal-text"></i>
                                    </a>
                                    <a href="view_member_routine.php?member_id=<?php echo $member['M_id']; ?>" class="btn btn-secondary btn-sm mb-1" title="View Routine">
                                        <i class="bi bi-calendar-week"></i>
                                    </a>
                                    <a href="manage_members.php?action=delete&member_id=<?php echo $member['M_id']; ?>" 
                                       class="btn btn-danger btn-sm mb-1" title="Delete Member"
                                       onclick="return confirm('Are you sure you want to delete this member (ID: <?php echo $member['M_id']; ?>) and all their associated data? This action cannot be undone.');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No members found.</td>
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
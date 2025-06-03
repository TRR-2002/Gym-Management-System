
<?php
$page_title = "View Member Feedback";
require_once '../includes/auth_check_admin.php';
require_once '../includes/admin_header.php'; 

if (isset($_GET['action']) && $_GET['action'] == 'resolve' && isset($_GET['feedback_id'])) {
    $feedback_id_to_resolve = filter_input(INPUT_GET, 'feedback_id', FILTER_VALIDATE_INT);
    if ($feedback_id_to_resolve) {
        try {
            $sql_delete_feedback = "DELETE FROM Feedback WHERE F_id = :feedback_id";
            $stmt_delete_feedback = $pdo->prepare($sql_delete_feedback);
            $stmt_delete_feedback->bindParam(':feedback_id', $feedback_id_to_resolve, PDO::PARAM_INT);
            
            if ($stmt_delete_feedback->execute()) {
                $_SESSION['message'] = "Feedback (ID: {$feedback_id_to_resolve}) marked as resolved and removed successfully.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Failed to resolve feedback.";
                $_SESSION['message_type'] = "error";
            }
        } catch (PDOException $e) {
            error_log("Error resolving feedback: " . $e->getMessage());
            $_SESSION['message'] = "Database error resolving feedback. Check logs.";
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Invalid feedback ID for resolution.";
        $_SESSION['message_type'] = "error";
    }
    header("Location: view_feedback.php"); 
    exit();
}

try {
    $stmt = $pdo->query("SELECT f.F_id, f.Category, f.Time, f.Date, f.Text, f.M_id, m.Name AS MemberName 
                         FROM Feedback f
                         LEFT JOIN Members m ON f.M_id = m.M_id
                         ORDER BY f.Date DESC, f.Time DESC");
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $feedbacks = [];
    $page_error = "Database error fetching feedback: " . $e->getMessage();
    error_log($page_error);
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800"><?php echo $page_title; ?></h1>
    <p class="mb-4">Review feedback submitted by members. Resolving a feedback item will remove it from this list.</p>

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
            <h6 class="m-0 font-weight-bold text-primary">Feedback List</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($feedbacks)): ?>
                <div class="list-group">
                    <?php foreach ($feedbacks as $item): ?>
                        <div class="list-group-item list-group-item-action flex-column align-items-start mb-3 border rounded">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1 text-primary"><?php echo htmlspecialchars($item['Category'] ?? 'General'); ?></h5>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($item['Date'] ? date("M d, Y", strtotime($item['Date'])) : 'N/A'); ?>
                                    at <?php echo htmlspecialchars($item['Time'] ? date("h:i A", strtotime($item['Time'])) : 'N/A'); ?>
                                </small>
                            </div>
                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($item['Text'] ?? 'No feedback text.')); ?></p>
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <small class="text-muted">
                                    Submitted by: 
                                    <?php if ($item['M_id'] && $item['MemberName']): ?>
                                        <?php echo htmlspecialchars($item['MemberName']); ?> (Member ID: <?php echo htmlspecialchars($item['M_id']); ?>)
                                    <?php elseif ($item['M_id']): ?>
                                        Member ID: <?php echo htmlspecialchars($item['M_id']); ?> (Name not found)
                                    <?php else: ?>
                                        Anonymous / System
                                    <?php endif; ?>
                                </small>
                                <a href="view_feedback.php?action=resolve&feedback_id=<?php echo $item['F_id']; ?>" 
                                   class="btn btn-success btn-sm" title="Mark as Resolved"
                                   onclick="return confirm('Are you sure you want to mark this feedback (ID: <?php echo $item['F_id']; ?>) as resolved? This will remove it from the list.');">
                                    <i class="bi bi-check2-circle"></i> Mark as Resolved
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">No pending feedback at this time.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once '../includes/admin_footer.php';
?>
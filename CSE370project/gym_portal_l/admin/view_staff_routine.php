<?php
$page_title = "View/Manage Staff Routine";
require_once '../includes/auth_check_admin.php';
require_once '../includes/admin_header.php'; 

if (!isset($_GET['staff_id']) || !filter_var($_GET['staff_id'], FILTER_VALIDATE_INT)) {
    $_SESSION['message'] = "Invalid or missing Staff ID.";
    $_SESSION['message_type'] = "error";
    header("Location: manage_staff.php");
    exit();
}
$staff_s_id = (int)$_GET['staff_id']; 

$staff_member_data = null;
$staff_routines = [];

try {

    $stmt_staff = $pdo->prepare("SELECT W_id, Name, Email, Designation FROM Workers WHERE W_id = :staff_s_id");
    $stmt_staff->bindParam(':staff_s_id', $staff_s_id, PDO::PARAM_INT);
    $stmt_staff->execute();
    $staff_member_data = $stmt_staff->fetch(PDO::FETCH_ASSOC);

    if (!$staff_member_data) {
        $_SESSION['message'] = "Staff member with ID {$staff_s_id} not found.";
        $_SESSION['message_type'] = "error";
        header("Location: manage_staff.php");
        exit();
    }
    $page_title = "Routine for " . htmlspecialchars($staff_member_data['Name']);



    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_routine'])) {
            $date = $_POST['date'] ?? null;
            $start_time = $_POST['start_time'] ?? null;
            $end_time = $_POST['end_time'] ?? null;

            if (empty($date) || empty($start_time) || empty($end_time)) {
                $_SESSION['message'] = "Date, Start Time, and End Time are required to add a routine.";
                $_SESSION['message_type'] = "error";
            } elseif (strtotime($end_time) <= strtotime($start_time)) {
                 $_SESSION['message'] = "End Time must be after Start Time.";
                 $_SESSION['message_type'] = "error";
            } else {
                
                try {
                    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM Staff_Routine WHERE S_id = :s_id AND Date = :date AND Start_Time = :start_time AND End_Time = :end_time");
                    $stmt_check->execute([':s_id' => $staff_s_id, ':date' => $date, ':start_time' => $start_time, ':end_time' => $end_time]);
                    if ($stmt_check->fetchColumn() > 0) {
                         $_SESSION['message'] = "This exact routine entry already exists.";
                         $_SESSION['message_type'] = "error";
                    } else {
                        $sql_add = "INSERT INTO Staff_Routine (S_id, Date, Start_Time, End_Time) VALUES (:s_id, :date, :start_time, :end_time)";
                        $stmt_add = $pdo->prepare($sql_add);
                        $stmt_add->execute([
                            ':s_id' => $staff_s_id,
                            ':date' => $date,
                            ':start_time' => $start_time,
                            ':end_time' => $end_time
                        ]);
                        $_SESSION['message'] = "Routine entry added successfully.";
                        $_SESSION['message_type'] = "success";
                    }
                } catch (PDOException $e) {
                    $_SESSION['message'] = "Database error adding routine: " . $e->getMessage();
                    $_SESSION['message_type'] = "error";
                }
            }
            header("Location: view_staff_routine.php?staff_id=" . $staff_s_id); 
            exit();

        } elseif (isset($_POST['delete_routine_s_id']) && isset($_POST['delete_routine_date']) && isset($_POST['delete_routine_start_time']) && isset($_POST['delete_routine_end_time'])) {
            $del_s_id = filter_var($_POST['delete_routine_s_id'], FILTER_VALIDATE_INT);
            $del_date = $_POST['delete_routine_date'];
            $del_start_time = $_POST['delete_routine_start_time'];
            $del_end_time = $_POST['delete_routine_end_time'];

            if ($del_s_id == $staff_s_id) {
                try {
                    $sql_delete = "DELETE FROM Staff_Routine WHERE S_id = :s_id AND Date = :date AND Start_Time = :start_time AND End_Time = :end_time";
                    $stmt_delete = $pdo->prepare($sql_delete);
                    $stmt_delete->execute([
                        ':s_id' => $del_s_id,
                        ':date' => $del_date,
                        ':start_time' => $del_start_time,
                        ':end_time' => $del_end_time
                    ]);
                    if ($stmt_delete->rowCount() > 0) {
                        $_SESSION['message'] = "Routine entry deleted successfully.";
                        $_SESSION['message_type'] = "success";
                    } else {
                        $_SESSION['message'] = "Routine entry not found or already deleted.";
                        $_SESSION['message_type'] = "warning";
                    }
                } catch (PDOException $e) {
                     $_SESSION['message'] = "Database error deleting routine: " . $e->getMessage();
                     $_SESSION['message_type'] = "error";
                }
            } else {
                 $_SESSION['message'] = "Unauthorized attempt to delete routine.";
                 $_SESSION['message_type'] = "error";
            }
            header("Location: view_staff_routine.php?staff_id=" . $staff_s_id); 
            exit();
        }
    }


    $stmt_routines = $pdo->prepare("SELECT S_id, Date, Start_Time, End_Time FROM Staff_Routine WHERE S_id = :staff_s_id ORDER BY Date ASC, Start_Time ASC");
    $stmt_routines->bindParam(':staff_s_id', $staff_s_id, PDO::PARAM_INT);
    $stmt_routines->execute();
    $staff_routines = $stmt_routines->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error on staff routine page: " . $e->getMessage());
    $_SESSION['message'] = "Database error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    
}

?>

<div class="container-fluid">
    
    <?php if ($staff_member_data): ?>
        <h1 class="h3 mb-1 text-gray-800"><?php echo htmlspecialchars($page_title); ?></h1>
        <p class="mb-2">
            <strong>ID:</strong> <?php echo htmlspecialchars($staff_member_data['W_id']); ?> | 
            <strong>Email:</strong> <?php echo htmlspecialchars($staff_member_data['Email']); ?> | 
            <strong>Designation:</strong> <?php echo htmlspecialchars($staff_member_data['Designation']); ?>
        </p>
    <?php else: ?>
        <h1 class="h3 mb-2 text-gray-800">Staff Routine Management</h1>
    <?php endif; ?>
    <p class="mb-4">View and manage the work schedule for this staff member.</p>

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
            <h6 class="m-0 font-weight-bold text-primary">Add New Routine Entry</h6>
        </div>
        <div class="card-body">
            <form action="view_staff_routine.php?staff_id=<?php echo $staff_s_id; ?>" method="POST">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-3">
                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="start_time" name="start_time" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="end_time" name="end_time" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button type="submit" name="add_routine" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i> Add Entry
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Scheduled Routines</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableStaffRoutines" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($staff_routines)): ?>
                            <?php foreach ($staff_routines as $routine): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date("D, M j, Y", strtotime($routine['Date']))); ?></td>
                                <td><?php echo htmlspecialchars(date("g:i A", strtotime($routine['Start_Time']))); ?></td>
                                <td><?php echo htmlspecialchars(date("g:i A", strtotime($routine['End_Time']))); ?></td>
                                <td>
                                    <?php
                                    try {
                                        $start = new DateTime($routine['Start_Time']);
                                        $end = new DateTime($routine['End_Time']);
                                        if ($end < $start) { 
                                            $end->modify('+1 day');
                                        }
                                        $interval = $start->diff($end);
                                        echo $interval->format('%h hr %i min');
                                    } catch (Exception $ex) {
                                        echo "N/A";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <form action="view_staff_routine.php?staff_id=<?php echo $staff_s_id; ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="delete_routine_s_id" value="<?php echo htmlspecialchars($routine['S_id']); ?>">
                                        <input type="hidden" name="delete_routine_date" value="<?php echo htmlspecialchars($routine['Date']); ?>">
                                        <input type="hidden" name="delete_routine_start_time" value="<?php echo htmlspecialchars($routine['Start_Time']); ?>">
                                        <input type="hidden" name="delete_routine_end_time" value="<?php echo htmlspecialchars($routine['End_Time']); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete Routine Entry"
                                                onclick="return confirm('Are you sure you want to delete this routine entry: <?php echo htmlspecialchars($routine['Date']) . ' from ' . htmlspecialchars($routine['Start_Time']) . ' to ' . htmlspecialchars($routine['End_Time']); ?>?');">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No routines scheduled for this staff member.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <a href="manage_staff.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Back to Manage Staff</a>
</div>


<?php
require_once '../includes/admin_footer.php';
?>
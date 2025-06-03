<?php


session_start();
require_once 'includes/auth_check_staff.php';
require_once 'includes/db_connect.php';

$s_id_staff = $_SESSION['staff_s_id'];
$staff_details = null;
$staff_certifications = [];
$staff_schedule_items = [];

try {

    $sql_worker_details = "SELECT Name, Email, Designation, Address, Phone_No
                           FROM Workers WHERE W_id = ?";
    if ($stmt_details = $mysqli->prepare($sql_worker_details)) {
        $stmt_details->bind_param("i", $s_id_staff);
        $stmt_details->execute();
        $result_details = $stmt_details->get_result();
        $staff_details = $result_details->fetch_assoc();
        $stmt_details->close();
    } else {
        error_log("MySQLi Prepare Error (Staff Profile - Worker Details): " . $mysqli->error);
        $_SESSION['temp_error_smp'] = "Error fetching your personal details.";
    }

    if ($staff_details) {
  
        $sql_certifications = "SELECT Certification FROM Certifications WHERE W_id = ? ORDER BY Certification ASC";
        if ($stmt_certs = $mysqli->prepare($sql_certifications)) {
            $stmt_certs->bind_param("i", $s_id_staff);
            $stmt_certs->execute();
            $result_certs = $stmt_certs->get_result();
            while ($row_cert = $result_certs->fetch_assoc()) {
                $staff_certifications[] = $row_cert['Certification'];
            }
            $stmt_certs->close();
        } else {
            error_log("MySQLi Prepare Error (Staff Profile - Certifications): " . $mysqli->error);
        }

  
        $sql_schedule = "SELECT Date, Start_Time, End_Time
                         FROM Staff_Routine
                         WHERE S_id = ? AND Date >= CURDATE() - INTERVAL 7 DAY
                         ORDER BY Date ASC, Start_Time ASC";
        if ($stmt_schedule = $mysqli->prepare($sql_schedule)) {
            $stmt_schedule->bind_param("i", $s_id_staff);
            $stmt_schedule->execute();
            $result_schedule = $stmt_schedule->get_result();
            $staff_schedule_items = $result_schedule->fetch_all(MYSQLI_ASSOC);
            $stmt_schedule->close();
        } else {
            error_log("MySQLi Prepare Error (Staff Profile - Schedule): " . $mysqli->error);
        }
    } else if (!isset($_SESSION['temp_error_smp'])) {
        $_SESSION['temp_error_smp'] = "Could not retrieve your profile information.";
    }

} catch (Exception $e) {
    error_log("Staff My Profile General Exception (S_id: {$s_id_staff}): " . $e->getMessage());
    $_SESSION['temp_error_smp'] = "An unexpected error occurred.";
}

require_once 'includes/header.php';

if (isset($_SESSION['temp_error_smp'])) {
    echo '<div class="message error">' . htmlspecialchars($_SESSION['temp_error_smp']) . '</div>';
    unset($_SESSION['temp_error_smp']);

    if (!$staff_details) {
        echo '<p><a href="staff_dashboard.php" class="button">« Back to Staff Dashboard</a></p>';
        require_once 'includes/footer.php';
        exit();
    }
}
?>

<h2>My Staff Profile & Schedule</h2>

<?php if ($staff_details): ?>
    <div class="profile-view section">
        <h3>My Details</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($staff_details['Name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($staff_details['Email']); ?></p>
        <p><strong>Designation:</strong> <?php echo htmlspecialchars($staff_details['Designation'] ?? 'N/A'); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($staff_details['Address'] ?? 'N/A'); ?></p>
        <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($staff_details['Phone_No'] ?? 'N/A'); ?></p>
    </div>

    <hr>
    <div class="section">
        <h3>My Certifications</h3>
        <?php if (!empty($staff_certifications)): ?>
            <ul>
                <?php foreach ($staff_certifications as $certification): ?>
                    <li><?php echo htmlspecialchars($certification); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No certifications are currently listed for you in the system.</p>
        <?php endif; ?>
        <p><small>Certifications are typically managed by the gym administration.</small></p>
    </div>

    <hr>
    <div class="section" id="schedule">
        <h3>My Work Schedule</h3>
        <?php if (!empty($staff_schedule_items)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day of Week</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staff_schedule_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date("M j, Y", strtotime($item['Date']))); ?></td>
                            <td><?php echo htmlspecialchars(date("l", strtotime($item['Date']))); ?></td>
                            <td><?php echo htmlspecialchars(date("g:i A", strtotime($item['Start_Time']))); ?></td>
                            <td><?php echo htmlspecialchars(date("g:i A", strtotime($item['End_Time']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You do not have any work schedule entries recorded for the recent past or upcoming period.</p>
        <?php endif; ?>
        <p><small>Your work schedule is generally set by the gym administration.</small></p>
    </div>
<?php else: ?>
  
    <p>Could not display your profile information at this time.</p>
<?php endif; ?>

<p style="margin-top: 20px;"><a href="staff_dashboard.php" class="button">« Back to Staff Dashboard</a></p>

<?php
require_once 'includes/footer.php';
?>
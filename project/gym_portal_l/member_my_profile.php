<?php


session_start();
require_once 'includes/auth_check_member.php';
require_once 'includes/db_connect.php'; // $mysqli

$m_id = $_SESSION['user_id'];

$current_member_subscription_type_from_session = $_SESSION['subscription_type'] ?? 'Inactive';
$member_details = null; 

$sql = "SELECT Name, Email, Date_of_Birth, Address, Phone_No, Subscription_Type
        FROM Members WHERE M_id = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $m_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $member_details = $result->fetch_assoc();

    } else {

        error_log("CRITICAL: Member M_id {$m_id} from session not found in database.");
        $_SESSION['message_profile_error'] = "Your profile data could not be found. Please log out and log in again.";
    }
    $stmt->close();
} else {
    error_log("MySQLi Prepare Error (Member My Profile - Fetch): " . $mysqli->error);
    $_SESSION['message_profile_error'] = "A database error occurred while fetching your profile.";
}

require_once 'includes/header.php';


if (isset($_SESSION['message_profile_error'])) {
    echo '<div class="message error">' . htmlspecialchars($_SESSION['message_profile_error']) . '</div>';
    unset($_SESSION['message_profile_error']); // Clear after display.
    // If core details failed, might not show the rest of the form.
    if (!$member_details) {
        echo '<p><a href="member_dashboard.php" class="button">« Back to Dashboard</a></p>';
        require_once 'includes/footer.php';
        exit();
    }
}
if (isset($_GET['error_msg'])) {
    echo '<div class="message error">' . htmlspecialchars($_GET['error_msg']) . '</div>';
}
if (isset($_GET['success_msg'])) {
    echo '<div class="message success">' . htmlspecialchars($_GET['success_msg']) . '</div>';
}
?>

<h2>My Profile</h2>

<?php if ($member_details): // Check if core details were fetched ?>
    <div class="profile-view">
        <h3>Current Details</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($member_details['Name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($member_details['Email']); ?></p>
        <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($member_details['Date_of_Birth'] ? date("F j, Y", strtotime($member_details['Date_of_Birth'])) : 'Not Set'); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($member_details['Address'] ?? 'Not Set'); ?></p>
        <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($member_details['Phone_No'] ?? 'Not Set'); ?></p>
        <p><strong>Subscription Type:</strong> <?php echo htmlspecialchars(ucfirst($current_member_subscription_type_from_session)); // Display from session ?></p>
    </div>

    <hr style="margin: 20px 0;">

    <div class="form-container profile-edit-form">
        <h3>Update Profile</h3>
        <p><small>Name and Date of Birth cannot be changed here. Email, Address, and Phone Number can be updated.</small></p>
        <form action="actions/process_member_profile.php" method="POST">
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo htmlspecialchars($member_details['Email']); ?>">
            </div>
            <div>
                <label for="address">Address: (Optional)</label>
                <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($member_details['Address'] ?? ''); ?></textarea>
            </div>
            <div>
                <label for="phone_no">Phone Number: (Optional)</label>
                <input type="text" id="phone_no" name="phone_no"
                       value="<?php echo htmlspecialchars($member_details['Phone_No'] ?? ''); ?>">
            </div>
            <div>
                <button type="submit" class="button">Update Profile</button>
            </div>
        </form>
    </div>
<?php elseif (!isset($_SESSION['message_profile_error']) && !isset($_GET['error_msg'])): ?>
    <p>Could not display your profile information at this time. Please <a href="member_dashboard.php">return to dashboard</a> or try again later.</p>
<?php endif; ?>

<p style="margin-top: 20px;"><a href="member_dashboard.php" class="button">« Back to Dashboard</a></p>

<?php
require_once 'includes/footer.php';
?>
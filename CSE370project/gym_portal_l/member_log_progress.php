<?php


session_start();
require_once 'includes/auth_check_member.php';
require_once 'includes/db_connect.php'; 

$m_id = $_SESSION['user_id'];
$p_id = $_SESSION['plan_id'] ?? null; 
$is_active_subscription = (strtolower($_SESSION['subscription_type'] ?? 'inactive') !== 'inactive');
$plan_context = null;

if ($is_active_subscription && $p_id) {

    $sql = "SELECT Plan_Name, Starting_Weight, Current_Weight, Goal_Weight FROM Plan WHERE P_id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $p_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $plan_context = $result->fetch_assoc();
        } else {

            $_SESSION['message'] = "Assigned plan details could not be found.";
            $_SESSION['message_type'] = "error";
        }
        $stmt->close();
    } else {
        error_log("MySQLi Prepare Error (Member Log Progress - Plan Fetch): " . $mysqli->error);
        $_SESSION['message'] = "Error fetching your plan data. Please try again.";
        $_SESSION['message_type'] = "error";
    }
}

require_once 'includes/header.php';
?>

<h2>Log Your Progress</h2>

<?php if (!$is_active_subscription): ?>
    <div class="message warning">Your subscription is not active. Please <a href="member_packages.php">activate a subscription</a> to log progress.</div>
<?php elseif (!$p_id): ?>
    <div class="message info">You do not have a fitness plan assigned. Progress cannot be logged until a plan is active.</div>
<?php elseif (!$plan_context): ?>
    <div class="message error">Could not load your current plan's weight data. Unable to log progress at this time.</div>
<?php else: ?>
    <h3>Plan: <?php echo htmlspecialchars($plan_context['Plan_Name']); ?></h3>
    <p><strong>Your Goal Weight:</strong> <?php echo htmlspecialchars($plan_context['Goal_Weight'] ?? 'Not Set'); ?> kg</p>
    <p><strong>Your Last Logged Weight:</strong> <?php echo htmlspecialchars($plan_context['Current_Weight'] ?? 'Not Yet Logged'); ?> kg</p>

    <?php if ($plan_context['Starting_Weight'] === null): ?>
        <p class="message info">This will be your first weight entry for this plan.
            The weight you enter now will be recorded as your <strong>Starting Weight</strong> for this plan.</p>
    <?php else: ?>
        <p><strong>Your Starting Weight (for this plan):</strong> <?php echo htmlspecialchars($plan_context['Starting_Weight']); ?> kg</p>
    <?php endif; ?>

    <form action="actions/process_member_progress.php" method="POST" class="form-container">
        <div>
            <label for="current_weight">Enter Your New Current Weight (kg):</label>
            <input type="number" id="current_weight" name="current_weight" step="0.1" required min="1"
                   placeholder="e.g., 75.5"
                   value="<?php echo htmlspecialchars($plan_context['Current_Weight'] ?? ''); ?>">
        </div>
        <div>
            <button type="submit" class="button">Log My Weight</button>
        </div>
    </form>
<?php endif; ?>

<p style="margin-top: 20px;"><a href="member_dashboard.php" class="button">« Back to Dashboard</a></p>

<?php
require_once 'includes/footer.php';
?>
<?php


session_start();
require_once 'includes/auth_check_member.php';


require_once 'includes/header.php';

$feedback_categories = ['General', 'Equipment', 'Classes', 'Staff', 'Facilities', 'Other'];
?>

<h2>Submit Feedback</h2>
<p>We appreciate your input! Please let us know your thoughts or concerns.</p>

<div class="form-container">
    <form action="actions/process_member_feedback.php" method="POST">
        <div>
            <label for="category">Feedback Category:</label>
            <select id="category" name="category" required>
                <option value="">-- Select a Category --</option>
                <?php foreach ($feedback_categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="feedback_text">Your Feedback:</label>
            <textarea id="feedback_text" name="feedback_text" rows="6" required
                      placeholder="Please provide details here..."></textarea>
        </div>
        <div>
            <button type="submit" class="button">Submit Feedback</button>
        </div>
    </form>
</div>

<p style="margin-top: 20px;"><a href="member_dashboard.php" class="button">« Back to Dashboard</a></p>

<?php
require_once 'includes/footer.php';
?>
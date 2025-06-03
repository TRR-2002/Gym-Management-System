<?php


session_start();
require_once 'includes/auth_check_staff.php';


require_once 'includes/header.php';
?>

<h2>Create New Fitness Plan</h2>
<p>Define a new plan template. This plan can then be assigned to members.</p>

<form action="actions/process_staff_create_plan.php" method="POST" id="createPlanForm" class="form-container">
    <fieldset>
        <legend>Plan Details</legend>
        <div>
            <label for="plan_name">Plan Name:</label>
            <input type="text" id="plan_name" name="plan_name" required
                   placeholder="e.g., Beginner Weight Loss - Phase 1"
                   value="<?php echo isset($_SESSION['form_data_create_plan']['plan_name']) ? htmlspecialchars($_SESSION['form_data_create_plan']['plan_name']) : ''; ?>">
        </div>
        <div>
            <label for="template_starting_weight">Template Starting Weight (kg, Optional):</label>
            <input type="number" id="template_starting_weight" name="template_starting_weight" step="0.1" min="1"
                   placeholder="e.g., 80.0"
                   value="<?php echo isset($_SESSION['form_data_create_plan']['template_starting_weight']) ? htmlspecialchars($_SESSION['form_data_create_plan']['template_starting_weight']) : ''; ?>">
            <small>This is a general starting point for the plan, not member-specific yet.</small>
        </div>
        <div>
            <label for="goal_weight">Default Goal Weight (kg, Optional):</label>
            <input type="number" id="goal_weight" name="goal_weight" step="0.1" min="1"
                   placeholder="e.g., 70.0"
                   value="<?php echo isset($_SESSION['form_data_create_plan']['goal_weight']) ? htmlspecialchars($_SESSION['form_data_create_plan']['goal_weight']) : ''; ?>">
        </div>
        <div>
            <label for="diet">Dietary Guidelines / Plan Diet:</label>
            <textarea id="diet" name="diet" rows="5"
                      placeholder="Describe the diet associated with this plan..."><?php echo isset($_SESSION['form_data_create_plan']['diet']) ? htmlspecialchars($_SESSION['form_data_create_plan']['diet']) : ''; ?></textarea>
        </div>
    </fieldset>

    <fieldset>
        <legend>Exercise Routine (Add items as needed)</legend>
        <div id="routineItemsContainer">
            <div class="routine-item">
                <label>Day:
                    <input type="text" name="day[]" placeholder="e.g., Monday, Wednesday">
                </label>
                <label>Time: (Optional)
                    <input type="text" name="time[]" placeholder="e.g., Morning, 9:00 AM">
                </label>
                <label>Exercise / Activity:
                    <input type="text" name="exercise[]" placeholder="e.g., 30 mins Cardio, Strength Training">
                </label>
                <button type="button" class="remove-routine-item button-danger" style="display:none;">Remove This Item</button>
            </div>
        </div>
        <button type="button" id="addRoutineItem" class="button" style="margin-top:10px;">+ Add Another Exercise Item</button>
    </fieldset>

    <div style="margin-top: 20px;">
        <button type="submit" class="button button-positive">Create Plan</button>
        <a href="staff_manage_plans.php" class="button">Cancel</a>
    </div>
</form>

<script>















document.addEventListener('DOMContentLoaded', function() {


    const container = document.getElementById('routineItemsContainer');
    const addButton = document.getElementById('addRoutineItem');
    
    const firstItem = container.querySelector('.routine-item');

    function updateRemoveButtonsVisibility() {
        const items = container.querySelectorAll('.routine-item');
        items.forEach((item) => {
            const removeBtn = item.querySelector('.remove-routine-item');
            if (removeBtn) {
                removeBtn.style.display = items.length > 1 ? 'inline-block' : 'none';
            }
        });
    }

    addButton.addEventListener('click', function() {
        if (firstItem) {
            const newItem = firstItem.cloneNode(true);
            newItem.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
            container.appendChild(newItem);
            updateRemoveButtonsVisibility();
        }
    });

    container.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-routine-item')) {
            if (container.querySelectorAll('.routine-item').length > 1) {
                event.target.closest('.routine-item').remove();
                updateRemoveButtonsVisibility();
            }
        }
    });
    updateRemoveButtonsVisibility();
});
</script>

<?php
if (isset($_SESSION['form_data_create_plan'])) {
    unset($_SESSION['form_data_create_plan']);
}
require_once 'includes/footer.php';
?>
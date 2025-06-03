<?php


session_start();

require_once 'includes/auth_check_staff.php';
require_once 'includes/db_connect.php';     

$s_id_staff = $_SESSION['staff_s_id'];

$p_id_to_edit = filter_input(INPUT_GET, 'p_id', FILTER_VALIDATE_INT);


if (!$p_id_to_edit) {
    $error_msg = "No valid Plan ID provided for editing.";

    header("Location: staff_manage_plans.php?error_msg=" . urlencode($error_msg));
    exit(); 
}


$plan_data_to_edit = null;         
$plan_routine_items_to_edit = [];  
$page_error_message = '';          


try {

    $sql_fetch_plan = "SELECT P_id, Plan_Name, S_id, Starting_Weight, Goal_Weight, Diet
                       FROM Plan WHERE P_id = ? AND S_id = ?";

    if ($stmt_plan = $mysqli->prepare($sql_fetch_plan)) {

        $stmt_plan->bind_param("ii", $p_id_to_edit, $s_id_staff);

        $stmt_plan->execute();

        $result_plan = $stmt_plan->get_result();

        if ($result_plan->num_rows === 1) {

            $plan_data_to_edit = $result_plan->fetch_assoc();
        } else {

            $page_error_message = "Plan not found, or you do not have permission to edit this plan.";
        }

        $stmt_plan->close();
    } else {

        error_log("MySQLi Prepare Error (Staff Edit Plan - Plan Fetch): " . $mysqli->error);
        $page_error_message = "Error preparing to fetch plan data.";
    }


    if ($plan_data_to_edit && empty($page_error_message)) {
 











        $sql_fetch_routines = "SELECT Day, Time, Exercise FROM Plan_Routine WHERE P_id = ?
                               ORDER BY FIELD(Day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), Time ASC";
        if ($stmt_routines = $mysqli->prepare($sql_fetch_routines)) {
            $stmt_routines->bind_param("i", $p_id_to_edit);
            $stmt_routines->execute();
            $result_routines = $stmt_routines->get_result();

            $plan_routine_items_to_edit = $result_routines->fetch_all(MYSQLI_ASSOC);
            $stmt_routines->close();
        } else {
            error_log("MySQLi Prepare Error (Staff Edit Plan - Routines Fetch): " . $mysqli->error);
            $page_error_message .= (empty($page_error_message) ? "" : "<br>") . "Error fetching plan routine items.";
        }
    }
} catch (Exception $e) { 
    error_log("Staff Edit Plan General Exception (P_id: {$p_id_to_edit}): " . $e->getMessage());
    $page_error_message = "An unexpected error occurred while loading plan data for editing.";
}



if (!$plan_data_to_edit && !empty($page_error_message)) {
   

    header("Location: staff_manage_plans.php?error_msg=" . urlencode($page_error_message));
    exit();
}


require_once 'includes/header.php';


if (!empty($page_error_message)) {
    echo '<div class="message error">' . htmlspecialchars($page_error_message) . '</div>';
}
if (isset($_GET['error_msg'])) { 
    echo '<div class="message error">' . htmlspecialchars($_GET['error_msg']) . '</div>';
}












$form_data = $_SESSION['form_data_edit_plan'] ?? $plan_data_to_edit; 
$form_routines = $_SESSION['form_data_edit_plan']['routines'] ?? $plan_routine_items_to_edit; 

?>

<h2>Edit Fitness Plan: <?php echo htmlspecialchars($form_data['Plan_Name'] ?? 'N/A'); ?></h2>

<?php if ($plan_data_to_edit): ?>
<form action="actions/process_staff_edit_plan.php" method="POST" id="editPlanForm" class="form-container">

    <input type="hidden" name="p_id_to_edit" value="<?php echo htmlspecialchars($plan_data_to_edit['P_id']); ?>">

    <fieldset>
        <legend>Plan Details</legend>
        <div>
            <label for="plan_name">Plan Name:</label>
            <input type="text" id="plan_name" name="plan_name" required
                   value="<?php echo htmlspecialchars($form_data['Plan_Name'] ?? ''); ?>">
        </div>
        <div>
            <label for="template_starting_weight">Template Starting Weight (kg, Optional):</label>
            <input type="number" id="template_starting_weight" name="template_starting_weight" step="0.1" min="1"
                   value="<?php echo htmlspecialchars($form_data['Starting_Weight'] ?? ''); ?>">
        </div>
        <div>
            <label for="goal_weight">Default Goal Weight (kg, Optional):</label>
            <input type="number" id="goal_weight" name="goal_weight" step="0.1" min="1"
                   value="<?php echo htmlspecialchars($form_data['Goal_Weight'] ?? ''); ?>">
        </div>
        <div>
            <label for="diet">Dietary Guidelines / Plan Diet:</label>
            <textarea id="diet" name="diet" rows="5"><?php echo htmlspecialchars($form_data['Diet'] ?? ''); ?></textarea>
        </div>
    </fieldset>

    <fieldset>
        <legend>Exercise Routine (Modify or Add items)</legend>
        <p><small>Changes made here will replace the entire existing routine for this plan upon saving.</small></p>
        <div id="routineItemsContainerEdit">





            <?php if (!empty($form_routines)): ?>
                <?php foreach ($form_routines as $item): ?>
                    <div class="routine-item">





                        <label>Day:
                            <input type="text" name="day[]" value="<?php echo htmlspecialchars($item['Day'] ?? ''); ?>" placeholder="e.g., Monday">
                        </label>
                        <label>Time: (Optional)
                            <input type="text" name="time[]" value="<?php echo htmlspecialchars($item['Time'] ?? ''); ?>" placeholder="e.g., Morning">
                        </label>
                        <label>Exercise / Activity:
                            <input type="text" name="exercise[]" value="<?php echo htmlspecialchars($item['Exercise'] ?? ''); ?>" placeholder="e.g., 30 mins Cardio">
                        </label>
                        <button type="button" class="remove-routine-item button-danger">Remove This Item</button>
                    </div>
                <?php endforeach; ?>






                 <div class="routine-item">
                    <label>Day: <input type="text" name="day[]" placeholder="e.g., Monday"></label>
                    <label>Time: (Optional) <input type="text" name="time[]" placeholder="e.g., Morning"></label>
                    <label>Exercise / Activity: <input type="text" name="exercise[]" placeholder="e.g., 30 mins Cardio"></label>
                    <button type="button" class="remove-routine-item button-danger" style="display:none;">Remove This Item</button>
                </div>
            <?php endif; ?>
        </div>
        <button type="button" id="addRoutineItemEdit" class="button" style="margin-top:10px;">+ Add Another Exercise Item</button>
    </fieldset>

    <div style="margin-top: 20px;">
        <button type="submit" class="button button-positive">Save Changes to Plan</button>
        <a href="staff_manage_plans.php" class="button">Cancel</a>
    </div>
</form>

<script>







document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('routineItemsContainerEdit');
    const addButton = document.getElementById('addRoutineItemEdit');
    
    function getTemplateItemHTML() {

        const firstItem = container.querySelector('.routine-item');
        if (firstItem) {
            const clone = firstItem.cloneNode(true);

            clone.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
            const removeBtn = clone.querySelector('.remove-routine-item');

            if(removeBtn) removeBtn.style.display = 'inline-block'; 
            return clone;
        } else { 

            const div = document.createElement('div');
            div.classList.add('routine-item');





            //brocode
            div.innerHTML = `
                <label>Day: <input type="text" name="day[]" placeholder="e.g., Monday"></label>
                <label>Time: (Optional) <input type="text" name="time[]" placeholder="e.g., Morning"></label>
                <label>Exercise / Activity: <input type="text" name="exercise[]" placeholder="e.g., 30 mins Cardio"></label>
                <button type="button" class="remove-routine-item button-danger" style="display:none;">Remove This Item</button>
            `;
            return div;
        }
    }


    function updateRemoveButtonsVisibilityEdit() {
        const items = container.querySelectorAll('.routine-item');
        items.forEach((item) => {
            const removeBtn = item.querySelector('.remove-routine-item');
            if (removeBtn) { 
                removeBtn.style.display = items.length > 1 ? 'inline-block' : 'none';
            }
        });
    }


    addButton.addEventListener('click', function() {
        const newItem = getTemplateItemHTML();
        container.appendChild(newItem);
        updateRemoveButtonsVisibilityEdit();
    });


    container.addEventListener('click', function(event) {

        if (event.target.classList.contains('remove-routine-item')) {

            if (container.querySelectorAll('.routine-item').length > 1) {

                event.target.closest('.routine-item').remove();
                updateRemoveButtonsVisibilityEdit(); // Refresh visibility.
            } else {

                event.target.closest('.routine-item').querySelectorAll('input[type="text"]').forEach(input => input.value = '');

            }
        }
    });

    if (container.children.length === 0) { 
        container.appendChild(getTemplateItemHTML());
    }
    updateRemoveButtonsVisibilityEdit();
});
</script>


    <?php if (empty($page_error_message) && !isset($_GET['error_msg'])): ?>
        <p class="message error">Could not load plan data. It may not exist or you may not have permission.</p>
    <?php endif; ?>
    <p><a href="staff_manage_plans.php" class="button">« Back to Manage Plans</a></p>
<?php endif; ?>

<?php

if (isset($_SESSION['form_data_edit_plan'])) {
    unset($_SESSION['form_data_edit_plan']);
}
require_once 'includes/footer.php';

?>
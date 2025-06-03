<?php
session_start();

if (isset($_SESSION['user_id'])) {

    header("Location: index.php");
    
    exit();
}


require_once 'includes/header.php';


$form_data = $_SESSION['form_data_register'] ?? []; 


if (isset($_GET['error_msg'])) {
    echo '<div class="message error">' . htmlspecialchars($_GET['error_msg']) . '</div>';
}
?>

<div class="form-container">
    <h2>Member Registration</h2>
    <p>Create your member account to access gym services and plans.</p>

    
    <form action="actions/process_register.php" method="POST">
        <div>
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required
                   value="<?php

                        echo isset($form_data['name']) ? htmlspecialchars($form_data['name']) : '';
                   ?>">
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required
                   value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required minlength="6">
            <small>Minimum 6 characters.</small>
        </div>
        <div>
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
        </div>
        <div>
            <label for="date_of_birth">Date of Birth:</label>
            <input type="date" id="date_of_birth" name="date_of_birth" required
                   value="<?php echo isset($form_data['date_of_birth']) ? htmlspecialchars($form_data['date_of_birth']) : ''; ?>">
        </div>
        <div>
            <label for="address">Address: (Optional)</label>

            <textarea id="address" name="address" rows="3"><?php
                echo isset($form_data['address']) ? htmlspecialchars($form_data['address']) : '';
            ?></textarea>
        </div>
        <div>
            <label for="phone_no">Phone Number: (Optional)</label>
            <input type="text" id="phone_no" name="phone_no"
                   value="<?php echo isset($form_data['phone_no']) ? htmlspecialchars($form_data['phone_no']) : ''; ?>">
        </div>
        <div>
            <button type="submit" class="button">Register</button>
        </div>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
</div>

<?php

if (isset($_SESSION['form_data_register'])) {
    
    unset($_SESSION['form_data_register']);
}


require_once 'includes/footer.php';
?>
<?php

session_start();

if (isset($_SESSION['admin_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header("Location: admin/admin_dashboard.php"); 
    exit();
}

$page_title = "Admin Portal Login";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Gym Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="css/admin.css">
    <style>

        body { display: flex; flex-direction:column; align-items: center; padding-top: 40px; padding-bottom: 40px; background-color: #e9ecef; min-height: 100vh; }
        .form-signin { width: 100%; max-width: 400px; padding: 25px; margin: auto; background-color: #fff; border-radius: 0.75rem; box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,.1); }
        .admin-portal-header { text-align:center; margin-bottom: 20px; }
        
        .admin-portal-header h1 { font-weight: 300; }
    </style>
</head>
<body>
    <main class="form-signin">
        <div class="admin-portal-header">

            <h1>Admin Portal</h1>
        </div>

        <form action="actions/admin_login_process.php" method="POST">
            <h2 class="h4 mb-3 fw-normal text-center">Please sign in</h2>

            <?php

            if (isset($_GET['error'])) {
                echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars(urldecode($_GET['error'])) . '</div>';
            }
            ?>

            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required
                       value="<?php echo isset($_SESSION['form_input_admin_login']['email']) ? htmlspecialchars($_SESSION['form_input_admin_login']['email']) : ''; ?>">
                <label for="email">Admin Email address</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
            <button class="w-100 btn btn-lg btn-primary" type="submit">Sign in</button>
            <p class="mt-3 text-center">
                <a href="login.php">Return to Member/Staff Login</a>
            </p>
            <p class="mt-4 mb-3 text-muted text-center">© Gym Admin <?php echo date("Y"); ?></p>
        </form>
    </main>

<?php
// Clear stored email input after displaying the form.
if (isset($_SESSION['form_input_admin_login'])) {
    unset($_SESSION['form_input_admin_login']);
}
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
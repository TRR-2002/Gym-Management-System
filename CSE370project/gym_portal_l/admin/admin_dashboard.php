<?php
$page_title = "Admin Dashboard"; 
require_once '../includes/auth_check_admin.php';
require_once '../includes/admin_header.php'; 
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <p class="lead">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>! From here you can manage all aspects of the gym portal.</p>
            <hr>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Members</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Manage Users</div>
                            <small class="card-text">View, add, edit, delete member accounts. Assign plans & view routines.</small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <a href="manage_members.php" class="stretched-link"></a>
                </div>
                 <div class="card-footer text-center">
                    <a href="manage_members.php" class="btn btn-sm btn-primary">Go to Members <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Staff & Trainers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Manage Staff</div>
                             <small class="card-text">Manage staff/trainer accounts, roles, schedules, and view their routines.</small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-badge-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                     <a href="manage_staff.php" class="stretched-link"></a>
                </div>
                <div class="card-footer text-center">
                    <a href="manage_staff.php" class="btn btn-sm btn-success">Go to Staff <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Equipment</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Manage Inventory</div>
                            <small class="card-text">Track gym equipment, inventory, and maintenance schedules.</small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-tools fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <a href="manage_equipment.php" class="stretched-link"></a>
                </div>
                 <div class="card-footer text-center">
                    <a href="manage_equipment.php" class="btn btn-sm btn-info">Go to Equipment <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Feedback</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Member Feedback</div>
                            <small class="card-text">Review feedback submitted by members to improve services.</small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-chat-left-text-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <a href="view_feedback.php" class="stretched-link"></a>
                </div>
                <div class="card-footer text-center">
                     <a href="view_feedback.php" class="btn btn-sm btn-warning">View Feedback <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Fitness Plans</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Manage Plans</div>
                            <small class="card-text">Create, edit, and assign fitness plans and exercise routines.</small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-journal-richtext fa-2x text-gray-300"></i>
                        </div>
                    </div>
                     <a href="manage_plans.php" class="stretched-link"></a>
                </div>
                <div class="card-footer text-center">
                    <a href="manage_plans.php" class="btn btn-sm btn-secondary">Go to Plans <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Analytics</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">System Reports</div>
                             <small class="card-text">View reports and analytics on members, subscriptions, etc. (Graphs)</small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-graph-up-arrow fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <a href="analytics.php" class="stretched-link"></a>
                </div>
                <div class="card-footer text-center">
                    <a href="analytics.php" class="btn btn-sm btn-danger">View Analytics <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
        </div>


    </div> 
</div> 

<?php
require_once '../includes/admin_footer.php';
?>
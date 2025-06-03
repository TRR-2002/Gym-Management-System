<?php
$page_title = "Gym Analytics & Statistics";
require_once '../includes/auth_check_admin.php';
require_once '../includes/admin_header.php'; 

$total_members = 0;
$total_staff = 0;
$total_plans = 0;
$total_equipment_items = 0; 
$total_equipment_types = 0; 
$feedback_count = 0;
$package_distribution = [];
$active_members = 0; 

try {
    $stmt_members = $pdo->query("SELECT COUNT(*) FROM Members");
    $total_members = $stmt_members->fetchColumn();

    $stmt_staff = $pdo->query("SELECT COUNT(*) FROM Staff s JOIN Workers w ON s.S_id = w.W_id");
    $total_staff = $stmt_staff->fetchColumn();

    $stmt_plans = $pdo->query("SELECT COUNT(*) FROM Plan");
    $total_plans = $stmt_plans->fetchColumn();

    $stmt_equipment_types = $pdo->query("SELECT COUNT(*) FROM Equipment");
    $total_equipment_types = $stmt_equipment_types->fetchColumn();

    $stmt_equipment_items = $pdo->query("SELECT SUM(Quantity) FROM Equipment");
    $total_equipment_items = $stmt_equipment_items->fetchColumn() ?? 0; 

    $stmt_feedback = $pdo->query("SELECT COUNT(*) FROM Feedback");
    $feedback_count = $stmt_feedback->fetchColumn();

    $stmt_packages = $pdo->query("SELECT Subscription_Type, COUNT(*) as count 
                                   FROM Members 
                                   WHERE Subscription_Type IS NOT NULL AND Subscription_Type != '' AND Subscription_Type != 'Inactive'
                                   GROUP BY Subscription_Type");
    $raw_package_data = $stmt_packages->fetchAll(PDO::FETCH_ASSOC);

    $active_members = 0;
    foreach ($raw_package_data as $pkg_data) {
        $active_members += $pkg_data['count'];
    }
    
    if ($active_members > 0) {
        foreach ($raw_package_data as $pkg_data) {
            $package_distribution[] = [
                'type' => htmlspecialchars($pkg_data['Subscription_Type']),
                'count' => (int)$pkg_data['count'],
                'percentage' => round(((int)$pkg_data['count'] / $active_members) * 100, 2)
            ];
        }
    } elseif ($total_members > 0) { 
         $stmt_inactive = $pdo->query("SELECT COUNT(*) FROM Members WHERE Subscription_Type IS NULL OR Subscription_Type = '' OR Subscription_Type = 'Inactive'");
         $inactive_count = $stmt_inactive->fetchColumn();
         if ($inactive_count == $total_members) {
            $package_distribution[] = [
                'type' => 'Inactive/None',
                'count' => (int)$inactive_count,
                'percentage' => 100.00
            ];
         }
    }

    $stmt_members_with_plan = $pdo->query("SELECT COUNT(*) FROM Members WHERE P_id IS NOT NULL");
    $members_with_plan_count = $stmt_members_with_plan->fetchColumn();

    $average_routines_per_plan = 0;
    if ($total_plans > 0) {
        $stmt_avg_routines = $pdo->query("SELECT AVG(RoutineCount) 
                                          FROM (SELECT COUNT(*) as RoutineCount FROM Plan_Routine GROUP BY P_id) as PlanRoutineCounts");
        $average_routines_per_plan = $stmt_avg_routines->fetchColumn() ?? 0;
    }

} catch (PDOException $e) {
    $db_error = "Database error fetching analytics: " . $e->getMessage();
    error_log($db_error);
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800"><?php echo $page_title; ?></h1>
    <p class="mb-4">An overview of key metrics for the gym management system.</p>

    <?php
    if (isset($_SESSION['message'])) { 
        echo '<div class="alert alert-' . ($_SESSION['message_type'] == 'success' ? 'success' : 'danger') . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($_SESSION['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    if (isset($db_error)) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($db_error) . '</div>';
    }
    ?>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Members</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_members; ?></div>
                        </div>
                        <div class="col-auto"><i class="bi bi-people-fill fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Staff</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_staff; ?></div>
                        </div>
                        <div class="col-auto"><i class="bi bi-person-badge-fill fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Fitness Plan Templates</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_plans; ?></div>
                        </div>
                        <div class="col-auto"><i class="bi bi-journal-richtext fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Feedback</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $feedback_count; ?></div>
                        </div>
                        <div class="col-auto"><i class="bi bi-chat-left-text-fill fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Active Member Package Distribution</h6>
                </div>
                <div class="card-body">
                    <?php if ($active_members > 0 && !empty($package_distribution)): ?>
                        <p class="small text-muted">Total Active Members: <?php echo $active_members; ?> (out of <?php echo $total_members; ?> total members)</p>
                        <?php foreach ($package_distribution as $package_stat): ?>
                            <h4 class="small font-weight-bold"><?php echo $package_stat['type']; ?> 
                                <span class="float-end"><?php echo $package_stat['count']; ?> (<?php echo $package_stat['percentage']; ?>%)</span></h4>
                            <div class="progress mb-4">
                                <div class="progress-bar bg-info" role="progressbar" 
                                     style="width: <?php echo $package_stat['percentage']; ?>%" 
                                     aria-valuenow="<?php echo $package_stat['percentage']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif($total_members > 0 && !empty($package_distribution) && $package_distribution[0]['type'] == 'Inactive/None'): ?>
                         <p class="text-center text-muted mt-3">All <?php echo $total_members; ?> members are currently inactive or have no package.</p>
                    <?php else: ?>
                        <p class="text-center text-muted mt-3">No active member package data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Additional Statistics</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Equipment Types
                            <span class="badge bg-secondary rounded-pill"><?php echo $total_equipment_types; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Equipment Items (Sum of Quantities)
                            <span class="badge bg-secondary rounded-pill"><?php echo $total_equipment_items; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Members with an Assigned Fitness Plan
                            <span class="badge bg-primary rounded-pill"><?php echo $members_with_plan_count ?? 0; ?> / <?php echo $total_members; ?></span>
                        </li>
                         <li class="list-group-item d-flex justify-content-between align-items-center">
                            Average Routines per Plan Template
                            <span class="badge bg-info rounded-pill"><?php echo round($average_routines_per_plan ?? 0, 1); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
</div>

<?php
require_once '../includes/admin_footer.php';
?>
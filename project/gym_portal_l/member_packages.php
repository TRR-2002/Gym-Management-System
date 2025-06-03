<?php


session_start();
require_once 'includes/auth_check_member.php';



$current_subscription_type = $_SESSION['subscription_type'] ?? 'Inactive';


$available_packages = [
    [
        'name' => 'Basic Monthly',        
        'price' => '$30/month',
        'description' => 'Access to gym facilities during standard hours. Basic workout plans.',
        'value' => 'Basic Monthly'       
    ],
    [
        'name' => 'Premium Monthly',
        'price' => '$50/month',
        'description' => 'Full access including all classes, extended hours, and personalized plan options.',
        'value' => 'Premium Monthly'
    ],
    [
        'name' => 'Premium Annual',
        'price' => '$500/year',
        'description' => 'Best value! Full premium access for a year at a discounted rate.',
        'value' => 'Premium Annual'
    ],
];

require_once 'includes/header.php';

if (isset($_GET['error_msg'])) {
    echo '<div class="message error">' . htmlspecialchars($_GET['error_msg']) . '</div>';
}
?>

<h2>Subscription Packages</h2>
<p>Your Current Subscription: <strong><?php echo htmlspecialchars(ucfirst($current_subscription_type)); ?></strong></p>

<div class="package-container">
    <?php foreach ($available_packages as $package): ?>
        <div class="package">
            <h3><?php echo htmlspecialchars($package['name']); ?></h3>
            <p class="price"><?php echo htmlspecialchars($package['price']); ?></p>
            <p><?php echo htmlspecialchars($package['description']); ?></p>
            <?php

            $is_current_package = (strtolower($current_subscription_type) === strtolower($package['value']));
            ?>
            <?php if ($is_current_package): ?>
                <button type="button" class="button" disabled>Currently Active</button>
            <?php else: ?>
                <form action="actions/process_member_package.php" method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="package_type" value="<?php echo htmlspecialchars($package['value']); ?>">
                    <button type="submit" class="button button-activate">
                        <?php

                        $is_currently_inactive = (strtolower($current_subscription_type) === 'inactive' || $current_subscription_type === null);
                        echo $is_currently_inactive ? 'Activate This Plan' : 'Switch to This Plan';
                        ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<p style="margin-top: 20px;"><a href="member_dashboard.php" class="button">« Back to Dashboard</a></p>

<?php
require_once 'includes/footer.php';
?>
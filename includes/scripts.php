<?php
/**
 * Global JavaScript Scripts
 * Ensures consistent animations and behavior across all panels
 */

// Calculate root path dynamically for assets
$depth = count(explode('/', trim($_SERVER['PHP_SELF'], '/'))) - ( (strpos($_SERVER['PHP_SELF'], 'gym_management') !== false) ? 2 : 1 );
$root = str_repeat('../', max(0, $depth));
?>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $root; ?>assets/js/main.js?v=3.5"></script>

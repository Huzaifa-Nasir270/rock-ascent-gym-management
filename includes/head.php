<?php
/**
 * Global Head Meta & Links
 * Ensures consistent design across all panels
 */

// Calculate root path dynamically for assets
$depth = count(explode('/', trim($_SERVER['PHP_SELF'], '/'))) - ( (strpos($_SERVER['PHP_SELF'], 'gym_management') !== false) ? 2 : 1 );
$root = str_repeat('../', max(0, $depth));
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="<?php echo $root; ?>assets/css/style.css?v=4.0">

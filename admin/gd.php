<?php
if (extension_loaded('gd') && function_exists('gd_info')) {
    echo "GD is installed and enabled.\n";
    print_r(gd_info());
} else {
    echo "GD is not installed or enabled.\n";
}
?>
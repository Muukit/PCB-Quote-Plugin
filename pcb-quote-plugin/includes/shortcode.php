<?php
if ( ! defined('ABSPATH') ) exit;
add_shortcode( 'pcb_quote_form', function() {
    ob_start();
    include PCBQ_DIR . 'templates/form.php';
    return ob_get_clean();
});

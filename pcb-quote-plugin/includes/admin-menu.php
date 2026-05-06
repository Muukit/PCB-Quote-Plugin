<?php
if ( ! defined('ABSPATH') ) exit;

add_action('admin_menu', function() {
    add_menu_page('PCB Quotes','PCB Quotes','manage_options','pcbq-quotes','pcbq_page_quotes',
        'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="12" y2="14"/><circle cx="17" cy="17" r="3"/></svg>'),
        30
    );
    add_submenu_page('pcbq-quotes','All Quotes','All Quotes','manage_options','pcbq-quotes','pcbq_page_quotes');
    add_submenu_page('pcbq-quotes','Quote Detail','Quote Detail','manage_options','pcbq-detail','pcbq_page_detail');
    add_submenu_page('pcbq-quotes','Field Manager','Field Manager','manage_options','pcbq-fields','pcbq_page_fields');
    add_submenu_page('pcbq-quotes','Email Templates','Email Templates','manage_options','pcbq-emails','pcbq_page_emails');
    add_submenu_page('pcbq-quotes','Settings','Settings','manage_options','pcbq-settings','pcbq_page_settings');
});

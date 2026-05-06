<?php
if ( ! defined('ABSPATH') ) exit;

/* ═══════════════════════════════════════════════════════
   FRONTEND — Submit quote request
═══════════════════════════════════════════════════════ */
add_action( 'wp_ajax_nopriv_pcbq_submit', 'pcbq_ajax_submit' );
add_action( 'wp_ajax_pcbq_submit',        'pcbq_ajax_submit' );

function pcbq_ajax_submit() {
    check_ajax_referer( 'pcbq_submit', 'pcbq_nonce' );

    $P = $_POST;
    $name  = sanitize_text_field( $P['client_name']  ?? '' );
    $email = sanitize_email(      $P['client_email'] ?? '' );

    if ( ! $name )            wp_send_json_error(['msg' => 'Please enter your full name.']);
    if ( ! is_email($email) ) wp_send_json_error(['msg' => 'Please enter a valid email address.']);

    // ── File upload ───────────────────────────────────
    $gerber = '';
    if ( ! empty( $_FILES['gerber_file']['name'] ) ) {
        $file     = $_FILES['gerber_file'];
        $ext      = strtolower( pathinfo($file['name'], PATHINFO_EXTENSION) );
        $ok_exts  = ['zip','rar','gbr','gtl','gbl','gts','gbs','gko','drl','xln','gbx','ger'];
        if ( ! in_array($ext, $ok_exts) )           wp_send_json_error(['msg' => 'Invalid file type. Please upload ZIP or RAR.']);
        if ( $file['size'] > 100 * 1024 * 1024 )    wp_send_json_error(['msg' => 'File too large. Max 100 MB.']);
        $up   = wp_upload_dir();
        $dest = $up['basedir'] . '/pcb-gerber-files/';
        $fn   = 'gbr_' . time() . '_' . wp_generate_password(8, false) . '.' . $ext;
        if ( move_uploaded_file($file['tmp_name'], $dest . $fn) ) $gerber = $fn;
    }

    // ── Insert ────────────────────────────────────────
    global $wpdb;
    $ok = $wpdb->insert( $wpdb->prefix . 'pcb_quotes', [
        'client_name'    => $name,
        'client_email'   => $email,
        'client_phone'   => sanitize_text_field($P['client_phone']   ?? ''),
        'client_company' => sanitize_text_field($P['client_company'] ?? ''),
        'product_type'   => sanitize_text_field($P['product_type']   ?? 'Standard PCB/PCBA'),
        'base_material'  => sanitize_text_field($P['base_material']  ?? 'FR-4'),
        'layers'         => (int)($P['layers'] ?? 2),
        'dim_width'      => (float)($P['dim_width']  ?? 100),
        'dim_height'     => (float)($P['dim_height'] ?? 100),
        'quantity'       => (int)($P['quantity'] ?? 5),
        'product_use'    => sanitize_text_field($P['product_use'] ?? ''),
        'different_design'=> (int)($P['different_design'] ?? 1),
        'delivery_format' => sanitize_text_field($P['delivery_format'] ?? 'Single PCB'),
        'pcb_thickness'  => sanitize_text_field($P['pcb_thickness']  ?? '1.6mm'),
        'pcb_color'      => sanitize_text_field($P['pcb_color']      ?? 'Green'),
        'silkscreen'     => sanitize_text_field($P['silkscreen']     ?? 'White'),
        'material_type'  => sanitize_text_field($P['material_type']  ?? 'FR4 TG135'),
        'surface_finish' => sanitize_text_field($P['surface_finish'] ?? 'HASL(with lead)'),
        'outer_copper'   => sanitize_text_field($P['outer_copper']   ?? '1 oz'),
        'via_covering'   => sanitize_text_field($P['via_covering']   ?? 'Tented'),
        'via_plating'    => sanitize_text_field($P['via_plating']    ?? 'Not Specified'),
        'min_via_hole'   => sanitize_text_field($P['min_via_hole']   ?? ''),
        'board_tolerance'=> sanitize_text_field($P['board_tolerance']?? '±0.2mm(Regular)'),
        'confirm_file'   => sanitize_text_field($P['confirm_file']   ?? 'No'),
        'mark_on_pcb'    => sanitize_text_field($P['mark_on_pcb']    ?? 'Remove Mark'),
        'electrical_test'=> sanitize_text_field($P['electrical_test']?? 'Flying Probe Fully Test'),
        'gold_fingers'   => sanitize_text_field($P['gold_fingers']   ?? 'No'),
        'castellated'    => sanitize_text_field($P['castellated']    ?? 'No'),
        'edge_plating'   => sanitize_text_field($P['edge_plating']   ?? 'No'),
        'blind_slots'    => sanitize_text_field($P['blind_slots']    ?? 'No'),
        'ul_marking'     => sanitize_text_field($P['ul_marking']     ?? 'No'),
        'humidity_card'  => sanitize_text_field($P['humidity_card']  ?? 'No'),
        'special_notes'  => sanitize_textarea_field($P['special_notes'] ?? ''),
        'gerber_file'    => $gerber,
    ]);

    if ( ! $ok ) {
        error_log( 'PCBQ DB error: ' . $wpdb->last_error );
        wp_send_json_error(['msg' => 'Database error. Please try again.']);
    }

    pcbq_send_admin_notification( $wpdb->insert_id );
    wp_send_json_success(['msg' => 'Your PCB quote request has been submitted! We will review your files and send you a price shortly.']);
}

/* ═══════════════════════════════════════════════════════
   ADMIN — Send price quote
═══════════════════════════════════════════════════════ */
add_action( 'wp_ajax_pcbq_send_quote', function() {
    check_ajax_referer('pcbq_admin','nonce');
    if ( ! current_user_can('manage_options') ) wp_send_json_error();

    $id    = (int)($_POST['id'] ?? 0);
    $price = round((float)($_POST['price'] ?? 0), 2);
    $notes = sanitize_textarea_field($_POST['notes'] ?? '');

    if ( ! $id || $price <= 0 ) wp_send_json_error(['msg' => 'Invalid ID or price.']);

    global $wpdb;
    $q = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}pcb_quotes WHERE id=%d", $id));
    if ( ! $q ) wp_send_json_error(['msg' => 'Quote not found.']);

    $sent = pcbq_send_client_quote($q, $price, $notes);
    if ( $sent ) {
        $wpdb->update($wpdb->prefix.'pcb_quotes',
            ['status'=>'quoted','quoted_price'=>$price,'admin_notes'=>$notes,'quote_sent_at'=>current_time('mysql')],
            ['id'=>$id]
        );
        wp_send_json_success(['msg' => "Quote sent to {$q->client_email}"]);
    } else {
        wp_send_json_error(['msg' => 'Failed to send email. Check WP mail config.']);
    }
});

/* ═══════════════════════════════════════════════════════
   ADMIN — Update status
═══════════════════════════════════════════════════════ */
add_action( 'wp_ajax_pcbq_update_status', function() {
    check_ajax_referer('pcbq_admin','nonce');
    if ( ! current_user_can('manage_options') ) wp_send_json_error();
    global $wpdb;
    $id     = (int)($_POST['id'] ?? 0);
    $status = sanitize_text_field($_POST['status'] ?? '');
    $ok     = ['pending','reviewing','quoted','approved','rejected'];
    if ( $id && in_array($status,$ok) ) {
        $wpdb->update($wpdb->prefix.'pcb_quotes',['status'=>$status],['id'=>$id]);
        wp_send_json_success();
    }
    wp_send_json_error();
});

/* ═══════════════════════════════════════════════════════
   ADMIN — Delete quote
═══════════════════════════════════════════════════════ */
add_action( 'wp_ajax_pcbq_delete', function() {
    check_ajax_referer('pcbq_admin','nonce');
    if ( ! current_user_can('manage_options') ) wp_send_json_error();
    global $wpdb;
    $id = (int)($_POST['id'] ?? 0);
    $q  = $wpdb->get_row($wpdb->prepare("SELECT gerber_file FROM {$wpdb->prefix}pcb_quotes WHERE id=%d",$id));
    if ( $q && $q->gerber_file ) {
        $f = wp_upload_dir()['basedir'].'/pcb-gerber-files/'.$q->gerber_file;
        if ( file_exists($f) ) @unlink($f);
    }
    $wpdb->delete($wpdb->prefix.'pcb_quotes',['id'=>$id]);
    wp_send_json_success();
});

/* ═══════════════════════════════════════════════════════
   ADMIN — Save field config
═══════════════════════════════════════════════════════ */
add_action( 'wp_ajax_pcbq_save_fields', function() {
    check_ajax_referer('pcbq_admin','nonce');
    if ( ! current_user_can('manage_options') ) wp_send_json_error();

    global $wpdb;
    $t      = $wpdb->prefix . 'pcb_field_config';
    $fields = $_POST['fields'] ?? [];

    foreach ( $fields as $key => $data ) {
        $key     = sanitize_key($key);
        $enabled = isset($data['enabled']) ? 1 : 0;
        $label   = sanitize_text_field($data['label'] ?? '');
        // Options: one per line
        $opts_raw = sanitize_textarea_field($data['options'] ?? '');
        $opts     = array_filter(array_map('trim', explode("\n", $opts_raw)));
        $opts_j   = ! empty($opts) ? json_encode(array_values($opts)) : null;

        $wpdb->update( $t,
            ['enabled'=>$enabled, 'label'=>$label, 'options_json'=>$opts_j],
            ['field_key'=>$key]
        );
    }
    wp_send_json_success(['msg' => 'Field settings saved.']);
});

/* ═══════════════════════════════════════════════════════
   ADMIN — Download gerber file
═══════════════════════════════════════════════════════ */
add_action( 'admin_init', function() {
    if ( ! isset($_GET['pcbq_download']) ) return;
    if ( ! current_user_can('manage_options') ) wp_die('Forbidden');
    $id = (int)$_GET['pcbq_download'];
    global $wpdb;
    $q = $wpdb->get_row($wpdb->prepare("SELECT gerber_file FROM {$wpdb->prefix}pcb_quotes WHERE id=%d",$id));
    if ( ! $q || ! $q->gerber_file ) wp_die('File not found.');
    $path = wp_upload_dir()['basedir'].'/pcb-gerber-files/'.$q->gerber_file;
    if ( ! file_exists($path) ) wp_die('File missing from server.');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($q->gerber_file).'"');
    header('Content-Length: '.filesize($path));
    readfile($path); exit;
});

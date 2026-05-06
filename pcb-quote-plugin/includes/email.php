<?php
if ( ! defined('ABSPATH') ) exit;

function pcbq_parse_template( $tpl, $vars ) {
    foreach ( $vars as $k => $v ) {
        $tpl = str_replace( '{{' . $k . '}}', $v, $tpl );
    }
    return $tpl;
}

function pcbq_send_admin_notification( $quote_id ) {
    global $wpdb;
    $q   = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}pcb_quotes WHERE id=%d", $quote_id));
    $tpl = get_option('pcbq_email_admin_tpl', '');
    if ( ! $tpl ) return;

    $vars = [
        'quote_id'       => $q->id,
        'submitted_date' => date_i18n('F j, Y \a\t H:i', strtotime($q->submitted_at)),
        'client_name'    => esc_html($q->client_name),
        'client_email'   => esc_html($q->client_email),
        'client_phone'   => esc_html($q->client_phone  ?: '—'),
        'client_company' => esc_html($q->client_company ?: '—'),
        'admin_url'      => admin_url("admin.php?page=pcbq-detail&id={$q->id}"),
        'specs_table'    => pcbq_build_specs_table($q),
        'site_name'      => get_bloginfo('name'),
        'site_url'       => get_bloginfo('url'),
    ];

    $body    = pcbq_parse_template($tpl, $vars);
    $subject = '[' . get_bloginfo('name') . '] New PCB Quote Request #' . $q->id . ' — ' . $q->client_name;
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    wp_mail( get_option('admin_email'), $subject, $body, $headers );
}

function pcbq_send_client_quote( $q, $price, $admin_notes ) {
    $tpl = get_option('pcbq_email_client_tpl', '');
    if ( ! $tpl ) return false;

    $currency = get_option('pcbq_currency', 'USD');

    $msg_block = '';
    if ( $admin_notes ) {
        $msg_block = '<div style="margin:24px 0;padding:20px;background:#f8fafc;border-left:4px solid #0f4c81;border-radius:0 8px 8px 0;">'
            . '<p style="margin:0 0 6px;color:#1a2e4a;font-weight:700;font-size:14px;">Message from our team:</p>'
            . '<p style="margin:0;color:#475569;font-size:14px;line-height:1.7;">' . nl2br(esc_html($admin_notes)) . '</p>'
            . '</div>';
    }

    $vars = [
        'quote_id'            => $q->id,
        'client_name'         => esc_html($q->client_name),
        'quoted_price'        => number_format($price, 2),
        'currency'            => $currency,
        'quote_date'          => date_i18n('F j, Y'),
        'specs_table'         => pcbq_build_specs_table($q),
        'admin_message_block' => $msg_block,
        'site_name'           => get_bloginfo('name'),
        'site_url'            => get_bloginfo('url'),
    ];

    $body    = pcbq_parse_template($tpl, $vars);
    $subject = 'Your PCB Quote is Ready — ' . get_bloginfo('name') . ' (#' . $q->id . ')';
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    return wp_mail($q->client_email, $subject, $body, $headers);
}

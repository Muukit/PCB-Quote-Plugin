<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * Return all field configs, keyed by field_key.
 * Only enabled ones unless $all = true.
 */
function pcbq_get_fields( $all = false ) {
    global $wpdb;
    $t   = $wpdb->prefix . 'pcb_field_config';
    $sql = $all
        ? "SELECT * FROM {$t} ORDER BY sort_order ASC"
        : "SELECT * FROM {$t} WHERE enabled=1 ORDER BY sort_order ASC";
    $rows = $wpdb->get_results( $sql );
    $out  = [];
    foreach ( $rows as $r ) {
        $r->options = $r->options_json ? json_decode( $r->options_json, true ) : [];
        $out[ $r->field_key ] = $r;
    }
    return $out;
}

function pcbq_field_enabled( $key ) {
    $fields = pcbq_get_fields();
    return isset( $fields[ $key ] );
}

function pcbq_field_options( $key ) {
    $fields = pcbq_get_fields();
    return $fields[ $key ]->options ?? [];
}

function pcbq_field_label( $key ) {
    $fields = pcbq_get_fields( true );
    return $fields[ $key ]->label ?? $key;
}

/** Build an HTML specs table for emails */
function pcbq_build_specs_table( $quote ) {
    $rows = [
        'Product Type'    => $quote->product_type,
        'Base Material'   => $quote->base_material,
        'Layers'          => $quote->layers,
        'Dimensions'      => $quote->dim_width . ' × ' . $quote->dim_height . ' mm',
        'Quantity'        => number_format( $quote->quantity ) . ' pcs',
        'PCB Thickness'   => $quote->pcb_thickness,
        'PCB Color'       => $quote->pcb_color,
        'Surface Finish'  => $quote->surface_finish,
        'Material Type'   => $quote->material_type,
        'Outer Copper'    => $quote->outer_copper,
        'Via Covering'    => $quote->via_covering,
        'Electrical Test' => $quote->electrical_test,
        'Gold Fingers'    => $quote->gold_fingers,
        'Gerber File'     => $quote->gerber_file ?: 'Not uploaded',
    ];

    $html  = '<table width="100%" cellpadding="8" cellspacing="0" style="border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;">';
    $i = 0;
    foreach ( $rows as $k => $v ) {
        $bg = $i % 2 === 0 ? '#f8fafc' : '#ffffff';
        $html .= "<tr style=\"background:{$bg};\"><td style=\"color:#64748b;font-size:13px;font-weight:600;width:42%;padding:9px 14px;\">{$k}</td><td style=\"color:#1e293b;font-size:13px;padding:9px 14px;\">" . esc_html($v) . "</td></tr>";
        $i++;
    }
    $html .= '</table>';
    return $html;
}

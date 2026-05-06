<?php
if ( ! defined('ABSPATH') ) exit;

function pcbq_create_tables() {
    global $wpdb;
    $c = $wpdb->get_charset_collate();

    $wpdb->query( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}pcb_quotes (
        id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        submitted_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        status           VARCHAR(30) NOT NULL DEFAULT 'pending',
        client_name      VARCHAR(150) NOT NULL DEFAULT '',
        client_email     VARCHAR(150) NOT NULL DEFAULT '',
        client_phone     VARCHAR(60) NOT NULL DEFAULT '',
        client_company   VARCHAR(150) NOT NULL DEFAULT '',
        product_type     VARCHAR(60) NOT NULL DEFAULT 'Standard PCB/PCBA',
        base_material    VARCHAR(60) NOT NULL DEFAULT 'FR-4',
        layers           TINYINT NOT NULL DEFAULT 2,
        dim_width        DECIMAL(10,2) NOT NULL DEFAULT 100,
        dim_height       DECIMAL(10,2) NOT NULL DEFAULT 100,
        quantity         INT NOT NULL DEFAULT 5,
        product_use      VARCHAR(60) NOT NULL DEFAULT '',
        different_design TINYINT NOT NULL DEFAULT 1,
        delivery_format  VARCHAR(40) NOT NULL DEFAULT 'Single PCB',
        pcb_thickness    VARCHAR(10) NOT NULL DEFAULT '1.6mm',
        pcb_color        VARCHAR(30) NOT NULL DEFAULT 'Green',
        silkscreen       VARCHAR(30) NOT NULL DEFAULT 'White',
        material_type    VARCHAR(40) NOT NULL DEFAULT '',
        surface_finish   VARCHAR(40) NOT NULL DEFAULT '',
        outer_copper     VARCHAR(10) NOT NULL DEFAULT '',
        via_covering     VARCHAR(40) NOT NULL DEFAULT '',
        via_plating      VARCHAR(60) NOT NULL DEFAULT '',
        min_via_hole     VARCHAR(40) NOT NULL DEFAULT '',
        board_tolerance  VARCHAR(30) NOT NULL DEFAULT '',
        confirm_file     VARCHAR(5)  NOT NULL DEFAULT 'No',
        mark_on_pcb      VARCHAR(60) NOT NULL DEFAULT '',
        electrical_test  VARCHAR(40) NOT NULL DEFAULT '',
        gold_fingers     VARCHAR(5)  NOT NULL DEFAULT 'No',
        castellated      VARCHAR(5)  NOT NULL DEFAULT 'No',
        edge_plating     VARCHAR(5)  NOT NULL DEFAULT 'No',
        blind_slots      VARCHAR(5)  NOT NULL DEFAULT 'No',
        ul_marking       VARCHAR(30) NOT NULL DEFAULT 'No',
        humidity_card    VARCHAR(5)  NOT NULL DEFAULT 'No',
        gerber_file      VARCHAR(500) NOT NULL DEFAULT '',
        special_notes    TEXT,
        quoted_price     DECIMAL(12,2) DEFAULT NULL,
        admin_notes      TEXT,
        quote_sent_at    DATETIME DEFAULT NULL,
        PRIMARY KEY (id)
    ) {$c}" );

    $wpdb->query( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}pcb_field_config (
        id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        field_key    VARCHAR(80) NOT NULL DEFAULT '',
        label        VARCHAR(150) NOT NULL DEFAULT '',
        enabled      TINYINT(1) NOT NULL DEFAULT 1,
        options_json TEXT,
        sort_order   SMALLINT NOT NULL DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE KEY field_key (field_key)
    ) {$c}" );
}

/* ─── Seed default field configuration ────────────────── */
function pcbq_seed_defaults() {
    global $wpdb;
    $t = $wpdb->prefix . 'pcb_field_config';
    if ( $wpdb->get_var("SELECT COUNT(*) FROM {$t}") > 0 ) return;

    $fields = [
        ['base_material',   'Base Material',         1, json_encode(['FR-4','Flex','Aluminum','Copper Core','Rogers']),                                   10],
        ['layers',          'Layers',                1, json_encode(['1','2','4','6','8','10','12','14','16']),                                            20],
        ['dim',             'Dimensions',            1, null,                                                                                              30],
        ['quantity',        'PCB Quantity',          1, json_encode(['5','10','15','20','25','30','40','50','75','100','125','150','200','250','300','400','450','500','600','700','800','900','1000']), 40],
        ['product_use',     'Product Type',          1, json_encode(['Industrial/Consumer','Aerospace','Medical']),                                        50],
        ['different_design','Different Design',      1, json_encode(['1','2','3','4']),                                                                    60],
        ['delivery_format', 'Delivery Format',       1, json_encode(['Single PCB','Panel by Customer','Panel by Manufacturer']),                           70],
        ['pcb_thickness',   'PCB Thickness',         1, json_encode(['0.4mm','0.6mm','0.8mm','1.0mm','1.2mm','1.6mm','2.0mm']),                           80],
        ['pcb_color',       'PCB Color',             1, json_encode(['Green','Purple','Red','Yellow','Blue','White','Black']),                             90],
        ['silkscreen',      'Silkscreen',            1, json_encode(['White','Black','None']),                                                            100],
        ['material_type',   'Material Type',         1, json_encode(['FR4 TG135','KB6164 - TG135','Nan Ya NP-140F','S1141 TG140','S1000H TG155']),       110],
        ['surface_finish',  'Surface Finish',        1, json_encode(['HASL(with lead)','LeadFree HASL','ENIG']),                                         120],
        ['outer_copper',    'Outer Copper Weight',   1, json_encode(['1 oz','2 oz','2.5 oz','3.5 oz','4.5 oz']),                                         130],
        ['via_covering',    'Via Covering',          1, json_encode(['Tented','Untented','Plugged','Epoxy Filled & Capped']),                             140],
        ['via_plating',     'Via Plating Method',    1, json_encode(['Not Specified','Conductive Adhesive','Horizontal Electroless Copper Plating']),     150],
        ['min_via_hole',    'Min Via Hole Size',     1, json_encode(['0.3mm/(0.4/0.45mm)','0.25mm/(0.35/0.4mm)','0.2mm/(0.3/0.35mm)','0.15mm/(0.25/0.3mm)']), 160],
        ['board_tolerance', 'Board Outline Tolerance',1,json_encode(['±0.2mm(Regular)','±0.1mm(Precision)']),                                            170],
        ['confirm_file',    'Confirm Production File',1,json_encode(['No','Yes']),                                                                        180],
        ['mark_on_pcb',     'Mark on PCB',           1, json_encode(['Remove Mark','Order Number(Specify Position)','2D barcode (Serial Number)','Order Number']), 190],
        ['electrical_test', 'Electrical Test',       1, json_encode(['Flying Probe Fully Test']),                                                        200],
        ['gold_fingers',    'Gold Fingers',          1, json_encode(['No','Yes']),                                                                        210],
        ['castellated',     'Castellated Holes',     1, json_encode(['No','Yes']),                                                                        220],
        ['edge_plating',    'Edge Plating',          1, json_encode(['No','Yes']),                                                                        230],
        ['blind_slots',     'Blind Slots',           1, json_encode(['No','Yes']),                                                                        240],
        ['ul_marking',      'UL Marking',            1, json_encode(['No','Yes (Any Position)','Yes (Specify Position)']),                               250],
        ['humidity_card',   'Humidity Indicator Card',1,json_encode(['No','Yes']),                                                                       260],
        ['special_notes',   'Special Notes',         1, null,                                                                                            270],
        ['gerber_file',     'Gerber File Upload',    1, null,                                                                                            280],
    ];

    foreach ( $fields as $f ) {
        $wpdb->insert( $t, [
            'field_key'    => $f[0],
            'label'        => $f[1],
            'enabled'      => $f[2],
            'options_json' => $f[3],
            'sort_order'   => $f[4],
        ]);
    }

    // Default email templates
    pcbq_seed_email_templates();
}

function pcbq_seed_email_templates() {
    // Admin notification template
    $admin_tpl = '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>New PCB Quote Request</title></head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:\'Segoe UI\',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f8;padding:30px 0;">
<tr><td align="center">
<table width="620" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.10);">
  <!-- Header -->
  <tr><td style="background:linear-gradient(135deg,#1a2e4a 0%,#0f4c81 100%);padding:36px 40px;text-align:center;">
    <div style="font-size:36px;margin-bottom:8px;">🖥️</div>
    <h1 style="color:#ffffff;margin:0;font-size:26px;font-weight:700;letter-spacing:-0.5px;">New PCB Quote Request</h1>
    <p style="color:rgba(255,255,255,0.7);margin:8px 0 0;font-size:14px;">Request #{{quote_id}} received on {{submitted_date}}</p>
  </td></tr>
  <!-- Body -->
  <tr><td style="padding:36px 40px;">
    <h3 style="color:#1a2e4a;margin:0 0 16px;font-size:16px;border-left:4px solid #0f4c81;padding-left:12px;">👤 Client Information</h3>
    <table width="100%" cellpadding="8" cellspacing="0" style="background:#f8fafc;border-radius:8px;margin-bottom:28px;">
      <tr><td style="color:#64748b;font-size:13px;width:40%;">Name</td><td style="color:#1e293b;font-weight:600;font-size:13px;">{{client_name}}</td></tr>
      <tr style="background:#f1f5f9;"><td style="color:#64748b;font-size:13px;">Email</td><td style="color:#1e293b;font-weight:600;font-size:13px;">{{client_email}}</td></tr>
      <tr><td style="color:#64748b;font-size:13px;">Phone</td><td style="color:#1e293b;font-weight:600;font-size:13px;">{{client_phone}}</td></tr>
      <tr style="background:#f1f5f9;"><td style="color:#64748b;font-size:13px;">Company</td><td style="color:#1e293b;font-weight:600;font-size:13px;">{{client_company}}</td></tr>
    </table>
    <h3 style="color:#1a2e4a;margin:0 0 16px;font-size:16px;border-left:4px solid #0f4c81;padding-left:12px;">📋 Board Specifications</h3>
    {{specs_table}}
    <div style="margin-top:28px;text-align:center;">
      <a href="{{admin_url}}" style="display:inline-block;background:linear-gradient(135deg,#0f4c81,#1a2e4a);color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:8px;font-weight:700;font-size:15px;">Review &amp; Send Quote →</a>
    </div>
  </td></tr>
  <tr><td style="background:#f8fafc;padding:20px 40px;text-align:center;border-top:1px solid #e2e8f0;">
    <p style="color:#94a3b8;font-size:12px;margin:0;">This is an automated notification from your PCB Quote Manager plugin.</p>
  </td></tr>
</table>
</td></tr>
</table>
</body>
</html>';

    // Client price quote template
    $client_tpl = '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Your PCB Quote is Ready</title></head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:\'Segoe UI\',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f8;padding:30px 0;">
<tr><td align="center">
<table width="620" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.10);">
  <!-- Header -->
  <tr><td style="background:linear-gradient(135deg,#1a2e4a 0%,#0f4c81 100%);padding:40px;text-align:center;">
    <div style="font-size:48px;margin-bottom:10px;">📦</div>
    <h1 style="color:#ffffff;margin:0;font-size:28px;font-weight:800;">Your PCB Quote is Ready!</h1>
    <p style="color:rgba(255,255,255,0.8);margin:10px 0 0;font-size:15px;">Quote #{{quote_id}} — Prepared for {{client_name}}</p>
  </td></tr>
  <!-- Price Banner -->
  <tr><td style="background:linear-gradient(135deg,#00b5a3,#007a6e);padding:28px 40px;text-align:center;">
    <p style="color:rgba(255,255,255,0.85);margin:0 0 6px;font-size:13px;text-transform:uppercase;letter-spacing:1px;">Your Quoted Price</p>
    <div style="color:#ffffff;font-size:48px;font-weight:800;line-height:1;">{{currency}} {{quoted_price}}</div>
    <p style="color:rgba(255,255,255,0.75);margin:8px 0 0;font-size:13px;">Valid for 7 days from {{quote_date}}</p>
  </td></tr>
  <!-- Body -->
  <tr><td style="padding:36px 40px;">
    <p style="color:#475569;font-size:15px;line-height:1.7;margin:0 0 28px;">Dear <strong>{{client_name}}</strong>,<br>Thank you for your interest. We have reviewed your Gerber files and specifications. Here is your personalised PCB quotation:</p>
    <h3 style="color:#1a2e4a;margin:0 0 16px;font-size:16px;border-left:4px solid #00b5a3;padding-left:12px;">📋 Your Board Specifications</h3>
    {{specs_table}}
    {{admin_message_block}}
    <div style="margin:32px 0 0;padding:20px;background:#f0fdfb;border:1px solid #a7f3d0;border-radius:10px;">
      <p style="margin:0;color:#065f46;font-size:14px;font-weight:600;">✅ To proceed with this order, simply reply to this email or contact us directly. We\'ll guide you through payment and production scheduling.</p>
    </div>
  </td></tr>
  <!-- Footer -->
  <tr><td style="background:#1a2e4a;padding:28px 40px;text-align:center;">
    <p style="color:rgba(255,255,255,0.6);font-size:12px;margin:0 0 6px;">{{site_name}} · {{site_url}}</p>
    <p style="color:rgba(255,255,255,0.4);font-size:11px;margin:0;">This quote was generated in response to your request. Prices are subject to final review.</p>
  </td></tr>
</table>
</td></tr>
</table>
</body>
</html>';

    update_option( 'pcbq_email_admin_tpl',  $admin_tpl );
    update_option( 'pcbq_email_client_tpl', $client_tpl );
}

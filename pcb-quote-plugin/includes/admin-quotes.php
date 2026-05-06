<?php
if ( ! defined('ABSPATH') ) exit;

/* ═══════════════════════════════════════════════════════
   LIST PAGE
═══════════════════════════════════════════════════════ */
function pcbq_page_quotes() {
    global $wpdb;
    $t  = $wpdb->prefix . 'pcb_quotes';
    $sf = sanitize_text_field($_GET['status'] ?? '');
    $ss = sanitize_text_field($_GET['s']      ?? '');

    $where = 'WHERE 1=1';
    if ($sf) $where .= $wpdb->prepare(' AND status=%s', $sf);
    if ($ss) $where .= $wpdb->prepare(' AND (client_name LIKE %s OR client_email LIKE %s)', "%$ss%", "%$ss%");

    $quotes = $wpdb->get_results("SELECT * FROM {$t} {$where} ORDER BY submitted_at DESC");
    $total  = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$t}");
    $counts = [];
    foreach ( ['pending','reviewing','quoted','approved','rejected'] as $s ) {
        $counts[$s] = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$t} WHERE status=%s",$s));
    }
    $STATUS = [
        'pending'   => ['Pending',   '#f59e0b','#fef3c7'],
        'reviewing' => ['Reviewing', '#3b82f6','#dbeafe'],
        'quoted'    => ['Quoted',    '#8b5cf6','#ede9fe'],
        'approved'  => ['Approved',  '#10b981','#d1fae5'],
        'rejected'  => ['Rejected',  '#ef4444','#fee2e2'],
    ];
    ?>
    <div class="wrap pcbq-wrap">
        <div class="pcbq-header">
            <div>
                <h1 class="pcbq-title">🖥 PCB Quote Manager</h1>
                <p class="pcbq-subtitle">Review incoming quote requests and send pricing to clients.</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="pcbq-stats">
            <div class="pcbq-stat-card"><span class="pcbq-stat-n"><?php echo $total; ?></span><span class="pcbq-stat-l">Total</span></div>
            <?php foreach($STATUS as $k=>[$lbl,$col,$bg]): ?>
            <div class="pcbq-stat-card"><span class="pcbq-stat-n" style="color:<?php echo $col; ?>"><?php echo $counts[$k]; ?></span><span class="pcbq-stat-l"><?php echo $lbl; ?></span></div>
            <?php endforeach; ?>
        </div>

        <!-- Filters -->
        <div class="pcbq-filters">
            <form method="get">
                <input type="hidden" name="page" value="pcbq-quotes">
                <div class="pcbq-filter-row">
                    <input type="text" name="s" value="<?php echo esc_attr($ss); ?>" placeholder="Search name or email…" class="pcbq-search">
                    <select name="status" class="pcbq-select">
                        <option value="">All Statuses</option>
                        <?php foreach($STATUS as $k=>[$lbl,$col,$bg]): ?>
                        <option value="<?php echo $k; ?>" <?php selected($sf,$k); ?>><?php echo $lbl; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="pcbq-btn pcbq-btn-teal">Filter</button>
                    <a href="?page=pcbq-quotes" class="pcbq-btn pcbq-btn-ghost">Reset</a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="pcbq-card pcbq-table-card">
            <table class="pcbq-table">
                <thead>
                    <tr>
                        <th>#</th><th>Client</th><th>Board</th><th>Qty</th><th>Gerber</th><th>Price</th><th>Status</th><th>Date</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($quotes)): ?>
                    <tr><td colspan="9" class="pcbq-empty">No quote requests found.</td></tr>
                    <?php else: foreach($quotes as $q):
                        [$lbl,$col,$bg] = $STATUS[$q->status] ?? ['Unknown','#888','#eee'];
                        $detail = admin_url("admin.php?page=pcbq-detail&id={$q->id}");
                    ?>
                    <tr data-id="<?php echo $q->id; ?>">
                        <td><strong class="pcbq-id">#<?php echo $q->id; ?></strong></td>
                        <td>
                            <div class="pcbq-cname"><?php echo esc_html($q->client_name); ?></div>
                            <div class="pcbq-cemail"><?php echo esc_html($q->client_email); ?></div>
                            <?php if($q->client_company): ?><div class="pcbq-cco"><?php echo esc_html($q->client_company); ?></div><?php endif; ?>
                        </td>
                        <td>
                            <span class="pcbq-chip"><?php echo esc_html($q->base_material); ?></span>
                            <span class="pcbq-chip"><?php echo $q->layers; ?>L</span>
                            <span class="pcbq-chip"><?php echo esc_html($q->pcb_color); ?></span>
                            <br><small><?php echo $q->dim_width; ?>×<?php echo $q->dim_height; ?>mm</small>
                        </td>
                        <td><strong><?php echo number_format($q->quantity); ?> pcs</strong></td>
                        <td><?php if($q->gerber_file): ?>
                            <a href="<?php echo admin_url("admin.php?pcbq_download={$q->id}"); ?>" class="pcbq-file-link">📁 Download</a>
                            <?php else: ?><span class="pcbq-na">—</span><?php endif; ?></td>
                        <td><?php echo $q->quoted_price ? '<strong>'.get_option('pcbq_currency','USD').' '.number_format($q->quoted_price,2).'</strong>' : '<span class="pcbq-na">—</span>'; ?></td>
                        <td>
                            <select class="pcbq-status-sel" data-id="<?php echo $q->id; ?>" style="border-left:3px solid <?php echo $col; ?>;">
                                <?php foreach($STATUS as $k=>[$sl]): ?>
                                <option value="<?php echo $k; ?>" <?php selected($q->status,$k); ?>><?php echo $sl; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><?php echo date_i18n('M j,Y', strtotime($q->submitted_at)); ?></td>
                        <td class="pcbq-actions">
                            <a href="<?php echo esc_url($detail); ?>" class="pcbq-btn pcbq-btn-teal pcbq-btn-sm">View</a>
                            <button class="pcbq-btn pcbq-btn-danger pcbq-btn-sm pcbq-del" data-id="<?php echo $q->id; ?>">Del</button>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

/* ═══════════════════════════════════════════════════════
   DETAIL PAGE
═══════════════════════════════════════════════════════ */
function pcbq_page_detail() {
    global $wpdb;
    $id = (int)($_GET['id'] ?? 0);
    if(!$id){echo '<div class="wrap"><p>Invalid ID.</p></div>';return;}
    $q = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}pcb_quotes WHERE id=%d",$id));
    if(!$q){echo '<div class="wrap"><p>Not found.</p></div>';return;}
    $currency = get_option('pcbq_currency','USD');
    $STATUS = ['pending'=>'Pending','reviewing'=>'Reviewing','quoted'=>'Quoted','approved'=>'Approved','rejected'=>'Rejected'];

    $all_specs = [
        'Product Type' => $q->product_type, 'Base Material' => $q->base_material,
        'Layers' => $q->layers, 'Dimensions' => $q->dim_width.'×'.$q->dim_height.' mm',
        'Quantity' => number_format($q->quantity).' pcs', 'Product Use' => $q->product_use,
        'Different Design' => $q->different_design, 'Delivery Format' => $q->delivery_format,
        'PCB Thickness' => $q->pcb_thickness, 'PCB Color' => $q->pcb_color,
        'Silkscreen' => $q->silkscreen, 'Material Type' => $q->material_type,
        'Surface Finish' => $q->surface_finish, 'Outer Copper' => $q->outer_copper,
        'Via Covering' => $q->via_covering, 'Via Plating' => $q->via_plating,
        'Min Via Hole' => $q->min_via_hole, 'Board Tolerance' => $q->board_tolerance,
        'Confirm File' => $q->confirm_file, 'Mark on PCB' => $q->mark_on_pcb,
        'Electrical Test' => $q->electrical_test, 'Gold Fingers' => $q->gold_fingers,
        'Castellated' => $q->castellated, 'Edge Plating' => $q->edge_plating,
        'Blind Slots' => $q->blind_slots, 'UL Marking' => $q->ul_marking,
        'Humidity Card' => $q->humidity_card,
    ];
    ?>
    <div class="wrap pcbq-wrap">
        <div class="pcbq-header">
            <div>
                <h1 class="pcbq-title">Quote Request #<?php echo $q->id; ?></h1>
                <p class="pcbq-subtitle">Submitted <?php echo date_i18n('F j, Y \a\t H:i', strtotime($q->submitted_at)); ?></p>
            </div>
            <a href="?page=pcbq-quotes" class="pcbq-btn pcbq-btn-ghost">← All Quotes</a>
        </div>

        <div class="pcbq-detail-grid">
            <div class="pcbq-detail-left">
                <!-- Client -->
                <div class="pcbq-card">
                    <div class="pcbq-card-head">👤 Client Information</div>
                    <table class="pcbq-info-tbl">
                        <tr><th>Name</th><td><?php echo esc_html($q->client_name); ?></td></tr>
                        <tr><th>Email</th><td><a href="mailto:<?php echo esc_attr($q->client_email); ?>"><?php echo esc_html($q->client_email); ?></a></td></tr>
                        <tr><th>Phone</th><td><?php echo esc_html($q->client_phone ?: '—'); ?></td></tr>
                        <tr><th>Company</th><td><?php echo esc_html($q->client_company ?: '—'); ?></td></tr>
                        <tr><th>Status</th><td>
                            <select class="pcbq-status-sel" data-id="<?php echo $q->id; ?>">
                                <?php foreach($STATUS as $k=>$l): ?>
                                <option value="<?php echo $k; ?>" <?php selected($q->status,$k); ?>><?php echo $l; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td></tr>
                    </table>
                </div>

                <!-- All Specs -->
                <div class="pcbq-card">
                    <div class="pcbq-card-head">⚙️ Board Specifications</div>
                    <table class="pcbq-info-tbl">
                        <?php foreach($all_specs as $k=>$v): ?>
                        <tr><th><?php echo esc_html($k); ?></th><td><?php echo esc_html($v); ?></td></tr>
                        <?php endforeach; ?>
                    </table>
                </div>

                <?php if($q->special_notes): ?>
                <div class="pcbq-card">
                    <div class="pcbq-card-head">📝 Client Notes</div>
                    <p style="color:#475569;line-height:1.7;margin:0;font-size:14px;"><?php echo nl2br(esc_html($q->special_notes)); ?></p>
                </div>
                <?php endif; ?>

                <!-- Gerber -->
                <div class="pcbq-card">
                    <div class="pcbq-card-head">📁 Gerber File</div>
                    <?php if($q->gerber_file): ?>
                    <p style="font-size:13px;color:#475569;margin:0 0 12px;"><?php echo esc_html($q->gerber_file); ?></p>
                    <a href="<?php echo admin_url("admin.php?pcbq_download={$q->id}"); ?>" class="pcbq-btn pcbq-btn-teal">⬇ Download Gerber File</a>
                    <?php else: ?><p style="color:#94a3b8;margin:0;">No file uploaded.</p><?php endif; ?>
                </div>
            </div>

            <div class="pcbq-detail-right">
                <!-- Quote Panel -->
                <div class="pcbq-card pcbq-quote-panel">
                    <div class="pcbq-card-head" style="color:#00b5a3;">💰 Send Price Quotation</div>
                    <?php if($q->quote_sent_at): ?>
                    <div class="pcbq-alert pcbq-alert-ok">✅ Quote of <strong><?php echo $currency.' '.number_format($q->quoted_price,2); ?></strong> sent on <?php echo date_i18n('M j, Y',strtotime($q->quote_sent_at)); ?></div>
                    <?php endif; ?>
                    <label class="pcbq-lbl">Quoted Price (<?php echo esc_html($currency); ?>)</label>
                    <input type="number" id="pcbq-price" class="pcbq-input" step="0.01" min="0" value="<?php echo esc_attr($q->quoted_price ?? ''); ?>" placeholder="e.g. 45.00">
                    <label class="pcbq-lbl" style="margin-top:14px;">Message to Client</label>
                    <textarea id="pcbq-notes" class="pcbq-textarea" rows="6" placeholder="Delivery time, payment info, any notes…"><?php echo esc_textarea($q->admin_notes ?? ''); ?></textarea>
                    <button id="pcbq-send-btn" class="pcbq-btn pcbq-btn-teal pcbq-btn-full pcbq-btn-lg" data-id="<?php echo $q->id; ?>">📧 Send Price Quote</button>
                    <div id="pcbq-send-msg" style="margin-top:10px;font-size:13px;font-weight:600;"></div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

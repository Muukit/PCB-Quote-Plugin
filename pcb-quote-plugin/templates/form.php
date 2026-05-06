<?php
if ( ! defined('ABSPATH') ) exit;
$fields = pcbq_get_fields(); // only enabled
?>
<div class="pcbq-app" id="pcbq-app">

<!-- ══════════════════════════════════════════════════
     PRODUCT TYPE TABS
══════════════════════════════════════════════════ -->
<div class="pcbq-product-tabs">
    <?php
    $products = [
        'Standard PCB/PCBA' => ['icon'=>'🖥', 'sub'=>'Standard PCB'],
        'Advanced PCB/PCBA' => ['icon'=>'⚡', 'sub'=>'Advanced PCB'],
        'SMT Stencil'       => ['icon'=>'🔲', 'sub'=>'Stencil'],
        'Flex Heater'       => ['icon'=>'🌡', 'sub'=>'Heater'],
        'Mechatronic Parts' => ['icon'=>'⚙', 'sub'=>'Mechanical'],
        '3D Printing'       => ['icon'=>'🖨', 'sub'=>'3D Print'],
        'CNC Machining'     => ['icon'=>'🔩', 'sub'=>'CNC'],
    ];
    foreach($products as $name => $info): ?>
    <button type="button" class="pcbq-prod-tab <?php echo $name==='Standard PCB/PCBA'?'active':''; ?>" data-product="<?php echo esc_attr($name); ?>">
        <span class="pcbq-prod-icon"><?php echo $info['icon']; ?></span>
        <span class="pcbq-prod-name"><?php echo esc_html($name); ?></span>
    </button>
    <?php endforeach; ?>
</div>

<!-- ══════════════════════════════════════════════════
     MAIN LAYOUT: Form + Sidebar
══════════════════════════════════════════════════ -->
<div class="pcbq-main-layout">

    <div class="pcbq-form-col">
        <form id="pcbq-form" enctype="multipart/form-data" novalidate>
            <?php wp_nonce_field('pcbq_submit','pcbq_nonce'); ?>
            <input type="hidden" name="product_type" id="pcbq-product-type" value="Standard PCB/PCBA">

            <!-- ── Section: Online PCB Quote ──────────────── -->
            <div class="pcbq-section">
                <div class="pcbq-section-head">
                    <span>Online PCB Quote</span>
                </div>

                <?php if(isset($fields['gerber_file'])): ?>
                <!-- Gerber Upload -->
                <div class="pcbq-upload-zone" id="pcbq-dropzone">
                    <input type="file" name="gerber_file" id="pcbq-file-input" accept=".zip,.rar,.gbr,.gtl,.gbl,.gts,.gbs,.gko,.drl,.xln,.gbx,.ger">
                    <div class="pcbq-upload-body">
                        <div class="pcbq-upload-icon-wrap">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#0f4c81" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </div>
                        <div>
                            <div class="pcbq-upload-title">Add Gerber File</div>
                            <div class="pcbq-upload-hint">Only accept ZIP or RAR, Max 100 MB · <a href="#" style="color:#0f4c81">View example</a></div>
                            <div class="pcbq-upload-secure">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                All uploads are secure and confidential.
                            </div>
                        </div>
                    </div>
                    <div class="pcbq-file-name" id="pcbq-file-name" style="display:none;"></div>
                </div>
                <?php endif; ?>

                <!-- Base Material -->
                <?php if(isset($fields['base_material'])): ?>
                <div class="pcbq-field-row-form">
                    <div class="pcbq-field-lbl">
                        <?php echo esc_html($fields['base_material']->label); ?>
                        
                    </div>
                    <div class="pcbq-opts-wrap" data-field="base_material">
                        <?php foreach($fields['base_material']->options as $i=>$opt): ?>
                        <button type="button" class="pcbq-opt <?php echo $i===0?'active':''; ?>" data-val="<?php echo esc_attr($opt); ?>">
                            <span class="pcbq-mat-icon"><?php
                                $icons=['FR-4'=>'🟩','Flex'=>'🟡','Aluminum'=>'⬜','Copper Core'=>'🟫','Rogers'=>'🔵'];
                                echo $icons[$opt] ?? '🔲';
                            ?></span>
                            <?php echo esc_html($opt); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="base_material" id="f_base_material" value="<?php echo esc_attr($fields['base_material']->options[0] ?? 'FR-4'); ?>">
                </div>
                <?php endif; ?>

                <!-- Layers -->
                <?php if(isset($fields['layers'])): ?>
                <div class="pcbq-field-row-form">
                    <div class="pcbq-field-lbl"><?php echo esc_html($fields['layers']->label); ?> </div>
                    <div class="pcbq-opts-row" data-field="layers">
                        <?php foreach($fields['layers']->options as $i=>$opt): ?>
                        <button type="button" class="pcbq-opt <?php echo $opt==='2'?'active':''; ?>" data-val="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="layers" id="f_layers" value="2">
                </div>
                <?php endif; ?>

                <!-- Dimensions -->
                <?php if(isset($fields['dim'])): ?>
                <div class="pcbq-field-row-form">
                    <div class="pcbq-field-lbl"><?php echo esc_html($fields['dim']->label); ?> </div>
                    <div class="pcbq-dim-row">
                        <input type="number" name="dim_width"  id="f_dim_width"  class="pcbq-dim-in" value="100" min="5" max="600" step="0.1">
                        <span class="pcbq-dim-x">*</span>
                        <input type="number" name="dim_height" id="f_dim_height" class="pcbq-dim-in" value="100" min="5" max="600" step="0.1">
                        <select class="pcbq-dim-unit"><option>mm</option><option>inch</option></select>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quantity -->
                <?php if(isset($fields['quantity'])): ?>
                <div class="pcbq-field-row-form">
                    <div class="pcbq-field-lbl"><?php echo esc_html($fields['quantity']->label); ?> </div>
                    <div class="pcbq-qty-wrap">
                        <select name="quantity" id="f_quantity" class="pcbq-qty-sel">
                            <?php foreach($fields['quantity']->options as $opt): ?>
                            <option value="<?php echo esc_attr($opt); ?>" <?php selected($opt,'5'); ?>><?php echo esc_html($opt); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="pcbq-qty-hint">pcs</span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Product Use -->
                <?php if(isset($fields['product_use'])): ?>
                <div class="pcbq-field-row-form">
                    <div class="pcbq-field-lbl"><?php echo esc_html($fields['product_use']->label); ?> </div>
                    <div class="pcbq-opts-row" data-field="product_use">
                        <?php foreach($fields['product_use']->options as $i=>$opt): ?>
                        <button type="button" class="pcbq-opt <?php echo $i===0?'active':''; ?>" data-val="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="product_use" id="f_product_use" value="<?php echo esc_attr($fields['product_use']->options[0] ?? ''); ?>">
                </div>
                <?php endif; ?>
            </div>

            <!-- ── Section: PCB Specifications ────────────── -->
            <?php
            $spec_fields = ['different_design','delivery_format','pcb_thickness','pcb_color','silkscreen','material_type','surface_finish'];
            $has_specs = array_intersect($spec_fields, array_keys($fields));
            if($has_specs):
            ?>
            <div class="pcbq-section pcbq-collapsible">
                <div class="pcbq-section-head pcbq-toggle-section">
                    <span>PCB Specifications</span>
                    <svg class="pcbq-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                </div>
                <div class="pcbq-section-body">

                    <?php if(isset($fields['different_design'])): ?>
                    <div class="pcbq-field-row-form">
                        <div class="pcbq-field-lbl"><?php echo esc_html($fields['different_design']->label); ?> </div>
                        <div class="pcbq-opts-row" data-field="different_design">
                            <?php foreach($fields['different_design']->options as $i=>$opt): ?>
                            <button type="button" class="pcbq-opt <?php echo $i===0?'active':''; ?>" data-val="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="different_design" id="f_different_design" value="<?php echo esc_attr($fields['different_design']->options[0] ?? '1'); ?>">
                    </div>
                    <?php endif; ?>

                    <?php if(isset($fields['delivery_format'])): ?>
                    <div class="pcbq-field-row-form">
                        <div class="pcbq-field-lbl"><?php echo esc_html($fields['delivery_format']->label); ?> </div>
                        <div class="pcbq-opts-row" data-field="delivery_format">
                            <?php foreach($fields['delivery_format']->options as $i=>$opt): ?>
                            <button type="button" class="pcbq-opt <?php echo $i===0?'active':''; ?>" data-val="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="delivery_format" id="f_delivery_format" value="<?php echo esc_attr($fields['delivery_format']->options[0] ?? 'Single PCB'); ?>">
                    </div>
                    <?php endif; ?>

                    <?php if(isset($fields['pcb_thickness'])): ?>
                    <div class="pcbq-field-row-form">
                        <div class="pcbq-field-lbl"><?php echo esc_html($fields['pcb_thickness']->label); ?> </div>
                        <div class="pcbq-opts-row" data-field="pcb_thickness">
                            <?php foreach($fields['pcb_thickness']->options as $opt): ?>
                            <button type="button" class="pcbq-opt <?php echo $opt==='1.6mm'?'active':''; ?>" data-val="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="pcb_thickness" id="f_pcb_thickness" value="1.6mm">
                    </div>
                    <?php endif; ?>

                    <!-- PCB Color -->
                    <?php if(isset($fields['pcb_color'])): ?>
                    <div class="pcbq-field-row-form">
                        <div class="pcbq-field-lbl"><?php echo esc_html($fields['pcb_color']->label); ?> </div>
                        <div class="pcbq-color-row" data-field="pcb_color">
                            <?php
                            $color_map = ['Green'=>'#2d7a2d','Purple'=>'#7c3aed','Red'=>'#dc2626','Yellow'=>'#eab308','Blue'=>'#1d4ed8','White'=>'#e5e7eb','Black'=>'#1a1a1a'];
                            foreach($fields['pcb_color']->options as $i=>$opt):
                                $bg = $color_map[$opt] ?? '#888';
                            ?>
                            <button type="button" class="pcbq-color-btn <?php echo $i===0?'active':''; ?>" data-val="<?php echo esc_attr($opt); ?>">
                                <span class="pcbq-color-swatch" style="background:<?php echo $bg; ?>;<?php echo $opt==='White'?'border:1px solid #d1d5db;':''; ?>"></span>
                                <span><?php echo esc_html($opt); ?></span>
                            </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="pcb_color" id="f_pcb_color" value="<?php echo esc_attr($fields['pcb_color']->options[0] ?? 'Green'); ?>">
                    </div>
                    <?php endif; ?>

                    <!-- Silkscreen -->
                    <?php if(isset($fields['silkscreen'])): ?>
                    <div class="pcbq-field-row-form">
                        <div class="pcbq-field-lbl"><?php echo esc_html($fields['silkscreen']->label); ?></div>
                        <div class="pcbq-opts-row" data-field="silkscreen">
                            <?php foreach($fields['silkscreen']->options as $i=>$opt): ?>
                            <button type="button" class="pcbq-opt pcbq-opt-outline <?php echo $i===0?'active':''; ?>" data-val="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="silkscreen" id="f_silkscreen" value="<?php echo esc_attr($fields['silkscreen']->options[0] ?? 'White'); ?>">
                    </div>
                    <?php endif; ?>

                    <?php if(isset($fields['material_type'])): ?>
                    <div class="pcbq-field-row-form">
                        <div class="pcbq-field-lbl"><?php echo esc_html($fields['material_type']->label); ?> </div>
                        <div class="pcbq-opts-row pcbq-opts-wrap" data-field="material_type">
                            <?php foreach($fields['material_type']->options as $i=>$opt): ?>
                            <button type="button" class="pcbq-opt <?php echo $i===0?'active':''; ?>" data-val="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="material_type" id="f_material_type" value="<?php echo esc_attr($fields['material_type']->options[0] ?? ''); ?>">
                    </div>
                    <?php endif; ?>

                    <?php if(isset($fields['surface_finish'])): ?>
                    <div class="pcbq-field-row-form">
                        <div class="pcbq-field-lbl"><?php echo esc_html($fields['surface_finish']->label); ?> </div>
                        <div class="pcbq-opts-row" data-field="surface_finish">
                            <?php foreach($fields['surface_finish']->options as $i=>$opt): ?>
                            <button type="button" class="pcbq-opt <?php echo $i===0?'active':''; ?>" data-val="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="surface_finish" id="f_surface_finish" value="<?php echo esc_attr($fields['surface_finish']->options[0] ?? ''); ?>">
                    </div>
                    <?php endif; ?>

                </div>
            </div>
            <?php endif; ?>

            <!-- ── Section: High-spec Options ─────────────── -->
            <?php
            $highspec = ['outer_copper','via_covering','via_plating','min_via_hole','board_tolerance','confirm_file','mark_on_pcb','electrical_test','gold_fingers','castellated','edge_plating','blind_slots','ul_marking','humidity_card'];
            $has_high = array_intersect($highspec, array_keys($fields));
            if($has_high):
            ?>
            <div class="pcbq-section pcbq-collapsible pcbq-collapsed">
                <div class="pcbq-section-head pcbq-toggle-section">
                    <span>High-spec Options</span>
                    <svg class="pcbq-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
                <div class="pcbq-section-body" style="display:none;">

                    <?php
                    $yesno_fields = ['confirm_file','gold_fingers','castellated','edge_plating','blind_slots','humidity_card'];
                    $highspec_all = [
                        'outer_copper','via_covering','via_plating','min_via_hole','board_tolerance',
                        'confirm_file','mark_on_pcb','electrical_test','gold_fingers','castellated',
                        'edge_plating','blind_slots','ul_marking','humidity_card'
                    ];
                    foreach($highspec_all as $fk):
                        if(!isset($fields[$fk])) continue;
                        $f = $fields[$fk];
                        $default = $f->options[0] ?? '';
                        $is_yn = in_array($fk, $yesno_fields);
                    ?>
                    <div class="pcbq-field-row-form">
                        <div class="pcbq-field-lbl"><?php echo esc_html($f->label); ?> </div>
                        <div class="pcbq-opts-row <?php echo $is_yn?'pcbq-yn-row':''; ?>" data-field="<?php echo esc_attr($fk); ?>">
                            <?php foreach($f->options as $i=>$opt): ?>
                            <button type="button" class="pcbq-opt <?php echo $i===0?'active':''; ?>" data-val="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="<?php echo esc_attr($fk); ?>" id="f_<?php echo esc_attr($fk); ?>" value="<?php echo esc_attr($default); ?>">
                    </div>
                    <?php endforeach; ?>

                </div>
            </div>
            <?php endif; ?>

            <!-- ── Section: Special Notes ──────────────────── -->
            <?php if(isset($fields['special_notes'])): ?>
            <div class="pcbq-section">
                <div class="pcbq-section-head"><span><?php echo esc_html($fields['special_notes']->label); ?></span></div>
                <div class="pcbq-section-body">
                    <textarea name="special_notes" class="pcbq-textarea" rows="4" placeholder="Any special requirements, stackup details, impedance notes, delivery instructions…"></textarea>
                </div>
            </div>
            <?php endif; ?>

            <!-- ── Section: Contact & Submit ──────────────── -->
            <div class="pcbq-section pcbq-contact-section">
                <div class="pcbq-section-head"><span>Your Contact Details</span></div>
                <div class="pcbq-section-body">
                    <div class="pcbq-contact-grid">
                        <div class="pcbq-contact-field">
                            <label class="pcbq-clabel">Full Name <span class="pcbq-req">*</span></label>
                            <input type="text" name="client_name" class="pcbq-cinput" placeholder="Jane Smith" required>
                        </div>
                        <div class="pcbq-contact-field">
                            <label class="pcbq-clabel">Email Address <span class="pcbq-req">*</span></label>
                            <input type="email" name="client_email" class="pcbq-cinput" placeholder="jane@company.com" required>
                        </div>
                        <div class="pcbq-contact-field">
                            <label class="pcbq-clabel">Phone Number</label>
                            <input type="tel" name="client_phone" class="pcbq-cinput" placeholder="+1 555 000 0000">
                        </div>
                        <div class="pcbq-contact-field">
                            <label class="pcbq-clabel">Company</label>
                            <input type="text" name="client_company" class="pcbq-cinput" placeholder="Your Company Ltd.">
                        </div>
                    </div>

                    <button type="submit" class="pcbq-submit-btn" id="pcbq-submit-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Submit Quote Request
                    </button>
                    <p class="pcbq-privacy">🔒 Your information is private and used only for quotation purposes.</p>
                </div>
            </div>

        </form>
    </div><!-- /form-col -->

    <!-- ══════════════════════════════════════════════
         RIGHT SIDEBAR
    ══════════════════════════════════════════════ -->
    <div class="pcbq-sidebar-col">
        <div class="pcbq-sidebar-card">
            <div class="pcbq-sidebar-head">Charge Details</div>
            <div class="pcbq-charge-rows">
                <div class="pcbq-charge-row"><span>Gerber File Review</span><span class="pcbq-charge-val">Free</span></div>
                <div class="pcbq-charge-row"><span>Technical Support</span><span class="pcbq-charge-val">Free</span></div>
                <div class="pcbq-charge-row"><span>DFM Check</span><span class="pcbq-charge-val">Free</span></div>
            </div>
            <div class="pcbq-sidebar-divider"></div>
            <div class="pcbq-build-time-head">Build Time</div>
            <div class="pcbq-build-options">
                <label class="pcbq-build-opt"><input type="radio" name="build_time" value="standard" checked><span><strong>Standard</strong> <span class="pcbq-bt-days">5–7 days</span></span><span class="pcbq-bt-price">Quote</span></label>
                <label class="pcbq-build-opt"><input type="radio" name="build_time" value="express"><span><strong>Express</strong> <span class="pcbq-bt-days">2–3 days</span></span><span class="pcbq-bt-price">Quote</span></label>
            </div>

            <div class="pcbq-sidebar-divider"></div>
            <div class="pcbq-price-note">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0f4c81" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Price will be calculated by our team after reviewing your Gerber file and specifications.
            </div>

            <!-- Summary Preview -->
            <div class="pcbq-summary-preview" id="pcbq-summary">
                <div class="pcbq-sum-head">Order Summary</div>
                <div class="pcbq-sum-rows" id="pcbq-sum-rows">
                    <div class="pcbq-sum-row"><span>Material</span><span id="sum-material">FR-4</span></div>
                    <div class="pcbq-sum-row"><span>Layers</span><span id="sum-layers">2</span></div>
                    <div class="pcbq-sum-row"><span>Qty</span><span id="sum-qty">5 pcs</span></div>
                    <div class="pcbq-sum-row"><span>Color</span><span id="sum-color">Green</span></div>
                    <div class="pcbq-sum-row"><span>Finish</span><span id="sum-finish">HASL(with lead)</span></div>
                </div>
            </div>
        </div>
    </div>

</div><!-- /main-layout -->

<!-- Success -->
<div class="pcbq-success" id="pcbq-success" style="display:none;">
    <div class="pcbq-success-icon">✅</div>
    <h2>Quote Request Submitted!</h2>
    <p id="pcbq-success-msg">Thank you! Our team will review your files and get back to you with pricing shortly.</p>
</div>

<!-- Error Toast -->
<div class="pcbq-toast" id="pcbq-toast" style="display:none;">
    <span id="pcbq-toast-msg"></span>
    <button id="pcbq-toast-close">×</button>
</div>

</div><!-- /pcbq-app -->

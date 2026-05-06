<?php
if ( ! defined('ABSPATH') ) exit;

/* ═══════════════════════════════════════════════════════
   EMAIL TEMPLATE EDITOR
═══════════════════════════════════════════════════════ */
function pcbq_page_emails() {
    // Save
    if ( isset($_POST['pcbq_save_email']) && check_admin_referer('pcbq_email_save') ) {
        $which = sanitize_text_field($_POST['which'] ?? 'client');
        $tpl   = wp_kses_post( wp_unslash($_POST['email_body'] ?? '') );
        $opt   = $which === 'admin' ? 'pcbq_email_admin_tpl' : 'pcbq_email_client_tpl';
        update_option($opt, $tpl);
        echo '<div class="notice notice-success is-dismissible"><p>✅ Email template saved.</p></div>';
    }

    $which   = sanitize_text_field($_GET['which'] ?? 'client');
    $opt     = $which === 'admin' ? 'pcbq_email_admin_tpl' : 'pcbq_email_client_tpl';
    $current = get_option($opt, '');

    $tabs = ['client' => '📧 Client Price Quote', 'admin' => '🔔 Admin Notification'];
    $placeholders = [
        '{{quote_id}}'            => 'Quote request ID number',
        '{{client_name}}'         => 'Client\'s full name',
        '{{client_email}}'        => 'Client\'s email address',
        '{{client_phone}}'        => 'Client\'s phone number',
        '{{client_company}}'      => 'Client\'s company name',
        '{{submitted_date}}'      => 'Date the request was submitted',
        '{{specs_table}}'         => 'Full HTML specs table',
        '{{quoted_price}}'        => 'The quoted price (client email only)',
        '{{currency}}'            => 'Currency code (USD, EUR, etc.)',
        '{{quote_date}}'          => 'Date the quote was sent',
        '{{admin_message_block}}' => 'Admin notes block (client email only)',
        '{{admin_url}}'           => 'Link to admin detail page (admin email only)',
        '{{site_name}}'           => 'Your WordPress site name',
        '{{site_url}}'            => 'Your website URL',
    ];
    ?>
    <div class="wrap pcbq-wrap">
        <div class="pcbq-header">
            <div>
                <h1 class="pcbq-title">✉️ Email Template Editor</h1>
                <p class="pcbq-subtitle">Edit the HTML emails sent to clients and to yourself. Use the placeholders below.</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="pcbq-email-tabs">
            <?php foreach($tabs as $k=>$label): ?>
            <a href="?page=pcbq-emails&which=<?php echo $k; ?>" class="pcbq-email-tab <?php echo $which===$k?'active':''; ?>"><?php echo $label; ?></a>
            <?php endforeach; ?>
        </div>

        <div class="pcbq-email-layout">
            <!-- Editor -->
            <div class="pcbq-email-editor-wrap">
                <div class="pcbq-card">
                    <div class="pcbq-card-head">HTML Source Editor — <?php echo $tabs[$which]; ?></div>
                    <form method="post" id="pcbq-email-form">
                        <?php wp_nonce_field('pcbq_email_save'); ?>
                        <input type="hidden" name="pcbq_save_email" value="1">
                        <input type="hidden" name="which" value="<?php echo esc_attr($which); ?>">
                        <div style="position:relative;">
                            <textarea name="email_body" id="pcbq-email-body" style="width:100%;min-height:520px;font-family:monospace;font-size:13px;border:1px solid #e2e8f0;border-radius:6px;padding:14px;"><?php echo esc_textarea($current); ?></textarea>
                        </div>
                        <div class="pcbq-email-actions">
                            <button type="submit" class="pcbq-btn pcbq-btn-teal pcbq-btn-lg">💾 Save Template</button>
                            <button type="button" id="pcbq-preview-btn" class="pcbq-btn pcbq-btn-ghost pcbq-btn-lg">👁 Preview</button>
                            <button type="button" id="pcbq-reset-btn" class="pcbq-btn pcbq-btn-danger pcbq-btn-lg" data-which="<?php echo esc_attr($which); ?>">↩ Reset to Default</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar: Placeholders -->
            <div class="pcbq-email-sidebar">
                <div class="pcbq-card">
                    <div class="pcbq-card-head">📌 Available Placeholders</div>
                    <p style="font-size:12px;color:#64748b;margin:0 0 14px;">Click any placeholder to copy it.</p>
                    <div class="pcbq-placeholders">
                        <?php foreach($placeholders as $ph=>$desc): ?>
                        <div class="pcbq-placeholder" data-copy="<?php echo esc_attr($ph); ?>">
                            <code class="pcbq-ph-code"><?php echo esc_html($ph); ?></code>
                            <span class="pcbq-ph-desc"><?php echo esc_html($desc); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="pcbq-card" style="margin-top:16px;">
                    <div class="pcbq-card-head">💡 Tips</div>
                    <ul style="font-size:13px;color:#475569;padding-left:18px;line-height:1.9;margin:0;">
                        <li>Use full HTML — inline CSS is best for email clients.</li>
                        <li>The <code>{{specs_table}}</code> placeholder inserts a pre-built HTML table.</li>
                        <li>Always test sending before going live.</li>
                        <li>Keep images hosted externally (no base64).</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div id="pcbq-preview-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:99999;overflow-y:auto;">
            <div style="background:#fff;max-width:680px;margin:40px auto;border-radius:12px;overflow:hidden;">
                <div style="background:#1a2e4a;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;">
                    <span style="color:#fff;font-weight:700;">Email Preview (sample data)</span>
                    <button id="pcbq-close-preview" style="background:none;border:none;color:#fff;font-size:22px;cursor:pointer;">×</button>
                </div>
                <div id="pcbq-preview-content" style="padding:20px;"></div>
            </div>
        </div>
    </div>

    <script>
    jQuery(function($){
        // Copy placeholder
        $('.pcbq-placeholder').on('click', function(){
            var txt = $(this).data('copy');
            var $ta = $('#pcbq-email-body');
            var pos = $ta[0].selectionStart;
            var val = $ta.val();
            $ta.val(val.slice(0,pos)+txt+val.slice(pos));
            $ta[0].setSelectionRange(pos+txt.length, pos+txt.length);
            $ta.focus();
            var $code = $(this).find('.pcbq-ph-code');
            $code.css('background','#00b5a3').css('color','#fff');
            setTimeout(()=>$code.css('background','').css('color',''), 800);
        });

        // Preview
        $('#pcbq-preview-btn').on('click', function(){
            var html = $('#pcbq-email-body').val();
            // Replace placeholders with sample data
            var samples = {
                '{{quote_id}}':'42','{{client_name}}':'Jane Smith','{{client_email}}':'jane@example.com',
                '{{client_phone}}':'+1 555 0100','{{client_company}}':'TechCorp Ltd',
                '{{submitted_date}}':'June 15, 2025 at 14:30','{{specs_table}}':'<table style="width:100%;border:1px solid #e2e8f0;border-radius:6px;"><tr style="background:#f8fafc"><td style="padding:8px 14px;font-size:13px;color:#64748b;width:42%">Base Material</td><td style="padding:8px 14px;font-size:13px;font-weight:600;">FR-4</td></tr><tr><td style="padding:8px 14px;font-size:13px;color:#64748b">Layers</td><td style="padding:8px 14px;font-size:13px;font-weight:600;">2</td></tr><tr style="background:#f8fafc"><td style="padding:8px 14px;font-size:13px;color:#64748b">Quantity</td><td style="padding:8px 14px;font-size:13px;font-weight:600;">100 pcs</td></tr></table>',
                '{{quoted_price}}':'85.00','{{currency}}':'USD','{{quote_date}}':'June 16, 2025',
                '{{admin_message_block}}':'<div style="margin:24px 0;padding:20px;background:#f8fafc;border-left:4px solid #0f4c81;border-radius:0 8px 8px 0;"><p style="margin:0;color:#475569;font-size:14px;">Lead time is 5–7 business days. Payment via bank transfer or PayPal.</p></div>',
                '{{admin_url}}':'#','{{site_name}}':'PCB Factory','{{site_url}}':'https://example.com'
            };
            for(var k in samples) html = html.split(k).join(samples[k]);
            $('#pcbq-preview-content').html(html);
            $('#pcbq-preview-modal').show();
        });
        $('#pcbq-close-preview').on('click',function(){ $('#pcbq-preview-modal').hide(); });
        $('#pcbq-preview-modal').on('click',function(e){ if(e.target===this) $(this).hide(); });

        // Reset to default
        $('#pcbq-reset-btn').on('click', function(){
            if(!confirm('Reset this template to the built-in default? Your edits will be lost.')) return;
            var $btn = $(this).prop('disabled',true).text('Resetting…');
            var which = $(this).data('which');
            $.post(PCBQ_Admin.ajax,{action:'pcbq_reset_email_tpl',nonce:PCBQ_Admin.nonce,which:which},function(res){
                if(res.success){ $('#pcbq-email-body').val(res.data.tpl); }
                $btn.prop('disabled',false).text('↩ Reset to Default');
            });
        });
    });
    </script>
    <?php
}

// AJAX reset template
add_action('wp_ajax_pcbq_reset_email_tpl', function(){
    check_ajax_referer('pcbq_admin','nonce');
    if(!current_user_can('manage_options')) wp_send_json_error();
    $which = sanitize_text_field($_POST['which'] ?? 'client');
    pcbq_seed_email_templates(); // re-seeds both
    $opt = $which==='admin' ? 'pcbq_email_admin_tpl' : 'pcbq_email_client_tpl';
    wp_send_json_success(['tpl' => get_option($opt,'')]);
});

/* ═══════════════════════════════════════════════════════
   SETTINGS PAGE
═══════════════════════════════════════════════════════ */
function pcbq_page_settings() {
    if( isset($_POST['pcbq_save_settings']) && check_admin_referer('pcbq_settings') ){
        update_option('pcbq_currency',    sanitize_text_field($_POST['pcbq_currency'] ?? 'USD'));
        update_option('pcbq_company_name',sanitize_text_field($_POST['pcbq_company_name'] ?? ''));
        echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
    }
    $currencies = ['USD'=>'USD — US Dollar','EUR'=>'EUR — Euro','GBP'=>'GBP — British Pound','JPY'=>'JPY — Japanese Yen','CNY'=>'CNY — Chinese Yuan','BDT'=>'BDT — Bangladeshi Taka','AUD'=>'AUD — Australian Dollar','CAD'=>'CAD — Canadian Dollar'];
    ?>
    <div class="wrap pcbq-wrap">
        <div class="pcbq-header"><div>
            <h1 class="pcbq-title">⚙️ Settings</h1>
            <p class="pcbq-subtitle">General plugin configuration.</p>
        </div></div>
        <div class="pcbq-card" style="max-width:560px;">
            <form method="post">
                <?php wp_nonce_field('pcbq_settings'); ?>
                <div class="pcbq-settings-grid">
                    <div class="pcbq-field-group">
                        <label class="pcbq-lbl">Currency</label>
                        <select name="pcbq_currency" class="pcbq-input">
                            <?php foreach($currencies as $code=>$label): ?>
                            <option value="<?php echo $code; ?>" <?php selected(get_option('pcbq_currency','USD'),$code); ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pcbq-field-group">
                        <label class="pcbq-lbl">Company / Site Name (used in emails)</label>
                        <input type="text" name="pcbq_company_name" class="pcbq-input" value="<?php echo esc_attr(get_option('pcbq_company_name',get_bloginfo('name'))); ?>">
                    </div>
                    <div class="pcbq-field-group">
                        <label class="pcbq-lbl">Shortcode</label>
                        <code style="display:block;padding:10px 14px;background:#f1f5f9;border-radius:6px;font-size:15px;">[pcb_quote_form]</code>
                        <p style="font-size:12px;color:#64748b;margin:6px 0 0;">Paste this on any page to display the PCB quote form.</p>
                    </div>
                </div>
                <button type="submit" name="pcbq_save_settings" class="pcbq-btn pcbq-btn-teal pcbq-btn-lg">Save Settings</button>
            </form>
        </div>
    </div>
    <?php
}

<?php
if ( ! defined('ABSPATH') ) exit;

function pcbq_page_fields() {
    $fields = pcbq_get_fields( true ); // get ALL including disabled
    ?>
    <div class="wrap pcbq-wrap">
        <div class="pcbq-header">
            <div>
                <h1 class="pcbq-title">🔧 Field Manager</h1>
                <p class="pcbq-subtitle">Control which fields appear on the frontend quote form and edit their labels and available options.</p>
            </div>
            <button id="pcbq-save-fields-btn" class="pcbq-btn pcbq-btn-teal pcbq-btn-lg">💾 Save All Changes</button>
        </div>
        <div id="pcbq-fields-msg" style="margin-bottom:16px;"></div>

        <div class="pcbq-card">
            <div class="pcbq-fields-legend">
                <span>🟢 Toggle to show/hide a field on the frontend form</span>
                <span>✏️ Edit the label shown to clients</span>
                <span>📋 One option per line in the options box</span>
            </div>

            <form id="pcbq-fields-form">
                <div class="pcbq-fields-grid">
                    <?php foreach($fields as $key => $f):
                        $has_opts = ! empty($f->options);
                        $no_opts_fields = ['dim','special_notes','gerber_file'];
                        $is_no_opts = in_array($key, $no_opts_fields);
                    ?>
                    <div class="pcbq-field-row <?php echo $f->enabled ? 'pcbq-enabled' : 'pcbq-disabled'; ?>" data-key="<?php echo esc_attr($key); ?>">
                        <div class="pcbq-field-row-top">
                            <div class="pcbq-toggle-wrap">
                                <label class="pcbq-toggle">
                                    <input type="checkbox" name="fields[<?php echo $key; ?>][enabled]" value="1" <?php checked($f->enabled, 1); ?> class="pcbq-toggle-cb" data-key="<?php echo $key; ?>">
                                    <span class="pcbq-toggle-slider"></span>
                                </label>
                            </div>
                            <div class="pcbq-field-info">
                                <span class="pcbq-field-key"><?php echo esc_html($key); ?></span>
                                <input type="text" name="fields[<?php echo $key; ?>][label]" value="<?php echo esc_attr($f->label); ?>" class="pcbq-label-input" placeholder="Field label">
                            </div>
                        </div>
                        <?php if(!$is_no_opts): ?>
                        <div class="pcbq-field-opts">
                            <label class="pcbq-opts-label">Options (one per line):</label>
                            <textarea name="fields[<?php echo $key; ?>][options]" class="pcbq-opts-textarea" rows="<?php echo min(8, max(3, count($f->options))); ?>"><?php echo esc_textarea(implode("\n", $f->options)); ?></textarea>
                        </div>
                        <?php else: ?>
                        <div class="pcbq-field-opts">
                            <p class="pcbq-no-opts">This field has no configurable options.</p>
                            <input type="hidden" name="fields[<?php echo $key; ?>][options]" value="">
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>
    </div>

    <script>
    jQuery(function($){
        // Toggle row style
        $('.pcbq-toggle-cb').on('change', function(){
            var $row = $(this).closest('.pcbq-field-row');
            $row.toggleClass('pcbq-enabled', this.checked).toggleClass('pcbq-disabled', !this.checked);
        });

        // Save
        $('#pcbq-save-fields-btn').on('click', function(){
            var $btn = $(this).prop('disabled',true).text('Saving…');
            var data = {action:'pcbq_save_fields', nonce:PCBQ_Admin.nonce};
            $('#pcbq-fields-form').find('[name]').each(function(){
                data[$(this).attr('name')] = this.type==='checkbox' ? (this.checked?'1':'0') : $(this).val();
            });
            // Rebuild fields object properly
            var fields = {};
            $('#pcbq-fields-form').find('.pcbq-field-row').each(function(){
                var key = $(this).data('key');
                fields[key] = {
                    enabled: $(this).find('.pcbq-toggle-cb').is(':checked') ? '1' : '0',
                    label:   $(this).find('.pcbq-label-input').val(),
                    options: $(this).find('.pcbq-opts-textarea').val() || ''
                };
            });
            $.post(PCBQ_Admin.ajax, {action:'pcbq_save_fields',nonce:PCBQ_Admin.nonce,fields:fields}, function(res){
                $btn.prop('disabled',false).text('💾 Save All Changes');
                var cls = res.success ? 'pcbq-alert-ok' : 'pcbq-alert-err';
                var msg = res.success ? '✅ '+res.data.msg : '❌ '+(res.data?.msg||'Error');
                $('#pcbq-fields-msg').html('<div class="pcbq-alert '+cls+'">'+msg+'</div>');
                setTimeout(()=>$('#pcbq-fields-msg').html(''), 4000);
            });
        });
    });
    </script>
    <?php
}

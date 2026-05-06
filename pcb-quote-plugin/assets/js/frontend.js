/* PCB Quote Manager Pro — Frontend JS */
(function($){
'use strict';

$(function(){

  /* ── Product tabs ─────────────────────────────────── */
  $('.pcbq-prod-tab').on('click', function(){
    $('.pcbq-prod-tab').removeClass('active');
    $(this).addClass('active');
    $('#pcbq-product-type').val($(this).data('product'));
  });

  /* ── Option buttons (toggle) ─────────────────────── */
  $(document).on('click', '.pcbq-opt', function(){
    var $g = $(this).closest('[data-field]');
    $g.find('.pcbq-opt').removeClass('active');
    $(this).addClass('active');
    var fld = $g.data('field');
    if(fld) $('#f_'+fld).val($(this).data('val'));
    updateSummary();
  });

  /* ── Color buttons ──────────────────────────────── */
  $(document).on('click', '.pcbq-color-btn', function(){
    var $g = $(this).closest('[data-field]');
    $g.find('.pcbq-color-btn').removeClass('active');
    $(this).addClass('active');
    $('#f_'+$g.data('field')).val($(this).data('val'));
    updateSummary();
  });

  /* ── Collapsible sections ────────────────────────── */
  $('.pcbq-toggle-section').on('click', function(){
    var $sec = $(this).closest('.pcbq-collapsible');
    var $body = $sec.find('.pcbq-section-body').first();
    var isCollapsed = $sec.hasClass('pcbq-collapsed');
    if(isCollapsed){
      $body.slideDown(220);
      $sec.removeClass('pcbq-collapsed');
    } else {
      $body.slideUp(220);
      $sec.addClass('pcbq-collapsed');
    }
  });

  /* ── Drag & drop upload ──────────────────────────── */
  var $zone  = $('#pcbq-dropzone');
  var $fi    = $('#pcbq-file-input');
  var $fname = $('#pcbq-file-name');

  $fi.on('change', function(){ if(this.files[0]) showFile(this.files[0]); });
  $zone.on('dragover dragenter', function(e){ e.preventDefault(); $(this).addClass('drag-over'); })
       .on('dragleave drop', function(){ $(this).removeClass('drag-over'); })
       .on('drop', function(e){
         e.preventDefault();
         var f = e.originalEvent.dataTransfer.files[0];
         if(f){ showFile(f); try{ $fi[0].files = e.originalEvent.dataTransfer.files; }catch(e){} }
       });

  function showFile(f){
    var max = 100 * 1024 * 1024;
    if(f.size > max){ showToast('File too large. Max 100 MB.'); return; }
    $fname.text('📁 ' + f.name + ' (' + (f.size/1024/1024).toFixed(2) + ' MB)').show();
  }

  /* ── Summary updater ─────────────────────────────── */
  function updateSummary(){
    var mat = $('#f_base_material').val() || 'FR-4';
    var lay = $('#f_layers').val() || '2';
    var qty = $('#f_quantity').val() || '5';
    var col = $('#f_pcb_color').val() || '—';
    var fin = $('#f_surface_finish').val() || '—';
    $('#sum-material').text(mat);
    $('#sum-layers').text(lay + ' layers');
    $('#sum-qty').text(qty + ' pcs');
    $('#sum-color').text(col);
    $('#sum-finish').text(fin);
  }
  updateSummary();
  $('#f_quantity').on('change', updateSummary);

  /* ── Form submit ─────────────────────────────────── */
  $('#pcbq-form').on('submit', function(e){
    e.preventDefault();

    var name  = $.trim($('[name=client_name]').val());
    var email = $.trim($('[name=client_email]').val());

    if(!name){  showToast('Please enter your full name.'); $('[name=client_name]').addClass('err').focus(); return; }
    if(!isEmail(email)){ showToast('Please enter a valid email address.'); $('[name=client_email]').addClass('err').focus(); return; }

    var $btn = $('#pcbq-submit-btn').prop('disabled',true).html('<span>⏳ Submitting…</span>');

    var fd = new FormData(this);
    fd.append('action', 'pcbq_submit');

    $.ajax({
      url: PCBQ.ajax, type:'POST', data:fd, processData:false, contentType:false,
      success: function(r){
        if(r.success){
          $('#pcbq-form').closest('.pcbq-main-layout').hide();
          $('.pcbq-product-tabs').hide();
          $('#pcbq-success').show();
          if(r.data && r.data.msg) $('#pcbq-success-msg').text(r.data.msg);
        } else {
          showToast((r.data && r.data.msg) || 'Submission failed. Please try again.');
          $btn.prop('disabled',false).html('<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Submit Quote Request');
        }
      },
      error: function(){
        showToast('Network error. Please check your connection.');
        $btn.prop('disabled',false).html('<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Submit Quote Request');
      }
    });
  });

  $('[name=client_name],[name=client_email]').on('input', function(){ $(this).removeClass('err'); });

  /* ── Toast ───────────────────────────────────────── */
  function showToast(msg){
    $('#pcbq-toast-msg').text(msg);
    $('#pcbq-toast').show();
    clearTimeout(window._pcbqToast);
    window._pcbqToast = setTimeout(function(){ $('#pcbq-toast').fadeOut(); }, 5000);
  }
  $('#pcbq-toast-close').on('click',function(){ $('#pcbq-toast').hide(); });

  function isEmail(e){ return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e); }

});
})(jQuery);

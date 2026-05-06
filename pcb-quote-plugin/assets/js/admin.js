/* PCB Quote Manager Pro — Admin JS */
(function($){
'use strict';
$(function(){

  /* ── Status inline change ──────────────────────── */
  $(document).on('change','.pcbq-status-sel', function(){
    var $s=$(this), id=$s.data('id');
    $.post(PCBQ_Admin.ajax,{action:'pcbq_update_status',nonce:PCBQ_Admin.nonce,id:id,status:$s.val()},function(r){
      if(r.success){ $s.css({background:'#d1fae5'}); setTimeout(()=>$s.css({background:''}),1200); }
    });
  });

  /* ── Delete row ────────────────────────────────── */
  $(document).on('click','.pcbq-del',function(){
    var $b=$(this), id=$b.data('id');
    if(!confirm('Delete quote #'+id+'? The Gerber file will also be removed.')) return;
    $.post(PCBQ_Admin.ajax,{action:'pcbq_delete',nonce:PCBQ_Admin.nonce,id:id},function(r){
      if(r.success) $b.closest('tr').fadeOut(300,function(){$(this).remove();});
      else alert('Delete failed.');
    });
  });

  /* ── Send price quote ──────────────────────────── */
  $('#pcbq-send-btn').on('click',function(){
    var $b=$(this), id=$b.data('id');
    var price=parseFloat($('#pcbq-price').val());
    var notes=$('#pcbq-notes').val();
    if(!price||price<=0){ showMsg('error','Please enter a valid price above 0.'); return; }
    if(!confirm('Send a price quote to the client?')) return;
    $b.prop('disabled',true).text('Sending…');
    $.post(PCBQ_Admin.ajax,{action:'pcbq_send_quote',nonce:PCBQ_Admin.nonce,id:id,price:price,notes:notes},function(r){
      $b.prop('disabled',false).html('📧 Send Price Quote');
      if(r.success) showMsg('ok', r.data.msg || 'Quote sent!');
      else showMsg('error', r.data?.msg || 'Send failed.');
    }).fail(function(){ $b.prop('disabled',false).html('📧 Send Price Quote'); showMsg('error','Network error.'); });
  });

  function showMsg(type,msg){
    var cls=type==='ok'?'color:#10b981':'color:#ef4444';
    $('#pcbq-send-msg').html('<span style="'+cls+';">'+(type==='ok'?'✅ ':'❌ ')+msg+'</span>');
    setTimeout(()=>$('#pcbq-send-msg').html(''),6000);
  }

});
})(jQuery);

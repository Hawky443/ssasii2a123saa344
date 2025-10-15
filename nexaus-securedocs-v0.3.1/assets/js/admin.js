(function($){
    function out(msg, ok){ $('#nsd-admin-output').html('<pre style="white-space:pre-wrap;padding:8px;border-left:3px solid '+(ok?'#46b450':'#dc3232')+'">'+msg+'</pre>'); }
    $('#nsd-fetch-models').on('click', function(){
        out('Fetching models…'); $.post(NSDAdmin.ajax,{action:'nsd_fetch_models',nonce:NSDAdmin.nonce},function(res){
            if(!res.success){ out('Error: '+(res.data&&res.data.message?res.data.message:'Unknown'),false); return; }
            var $sel=$('#nsd_model'); $sel.empty(); res.data.models.forEach(function(m){ $sel.append('<option value="'+m+'">'+m+'</option>'); });
            out('Fetched '+res.data.models.length+' models. Choose one and Save.',true);
        });
    });
    $('#nsd-test-connection').on('click', function(){
        out('Testing connection…'); $.post(NSDAdmin.ajax,{action:'nsd_test_connection',nonce:NSDAdmin.nonce,provider:$('#nsd_ai_provider').val(),model:$('#nsd_model').val()},function(res){
            if(!res.success){ out('Fail: '+(res.data&&res.data.message?res.data.message:'Unknown')+(res.data&&res.data.raw?'\n'+JSON.stringify(res.data.raw,null,2):''),false); return; }
            out('OK: '+res.data.reply,true);
        });
    });
})(jQuery);
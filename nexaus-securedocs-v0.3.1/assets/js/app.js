(function($){
    $(document).on('submit', '.nsd-generate', async function(e){
        e.preventDefault();
        const $form=$(this); const type=$form.attr('data-type'); const template=$form.attr('data-template');
        const brand=$form.find('[name="brand"]').val(); const industry=$form.find('[name="industry"]').val(); const scope=$form.find('[name="scope"]').val();
        const $out=$form.find('.nsd-output'); $out.html('<p>Generating… please wait.</p>');
        try{ const res=await fetch(NSD.rest+'/generate',{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':NSD.nonce},body:JSON.stringify({type,template,brand,industry,scope})});
            const data=await res.json(); if(!res.ok||data.code){ throw new Error(data.message||'Failed to generate'); }
            $out.html('<p>Done. <a target="_blank" href="'+data.edit_link+'">Open document</a></p>');
        }catch(err){ $out.html('<p style="color:red">Error: '+err.message+'</p>'); }
    });
})(jQuery);
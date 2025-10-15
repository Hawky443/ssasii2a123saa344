<?php
namespace NexausSecureDocs;
if (!defined('ABSPATH')) { exit; }
class AdminAjax {
    public static function init(){ add_action('wp_ajax_nsd_fetch_models',[__CLASS__,'fetch_models']); add_action('wp_ajax_nsd_test_connection',[__CLASS__,'test_connection']); }
    private static function verify(){ if(!current_user_can('manage_options')) wp_send_json_error(['message'=>'Forbidden'],403); check_ajax_referer('nsd_admin','nonce'); }
    public static function fetch_models(){ self::verify(); $provider = sanitize_text_field($_POST['provider'] ?? get_option('nsd_ai_provider','groq')); $models=[];
        if($provider==='groq'){ $key=get_option('nsd_groq_api_key',''); if(empty($key)) wp_send_json_error(['message'=>'Groq API key missing']); $resp=wp_remote_get('https://api.groq.com/openai/v1/models',['headers'=>['Authorization'=>'Bearer '.$key]]);
        } else { $key=get_option('nsd_openai_api_key',''); if(empty($key)) wp_send_json_error(['message'=>'OpenAI API key missing']); $resp=wp_remote_get('https://api.openai.com/v1/models',['headers'=>['Authorization'=>'Bearer '.$key]]); }
        if(is_wp_error($resp)) wp_send_json_error(['message'=>$resp->get_error_message()]); $data=json_decode(wp_remote_retrieve_body($resp),true);
        if(!empty($data['data'])){ foreach($data['data'] as $m){ if(!empty($m['id'])) $models[]=$m['id']; } }
        if(!$models) wp_send_json_error(['message'=>'No models returned by provider']); set_transient('nsd_model_list',$models,HOUR_IN_SECONDS); wp_send_json_success(['models'=>$models]); }
    public static function test_connection(){ self::verify(); $provider=sanitize_text_field($_POST['provider'] ?? get_option('nsd_ai_provider','groq')); $model=sanitize_text_field($_POST['model'] ?? get_option('nsd_model',''));
        $body=['model'=>$model,'messages'=>[['role'=>'system','content'=>'You are a ping tester. Reply with exactly: PONG'],['role'=>'user','content'=>'ping']],'temperature'=>0];
        if($provider==='groq'){ $key=get_option('nsd_groq_api_key',''); if(empty($key)) wp_send_json_error(['message'=>'Groq API key missing']); $url='https://api.groq.com/openai/v1/chat/completions'; $headers=['Authorization'=>'Bearer '.$key,'Content-Type'=>'application/json']; }
        else { $key=get_option('nsd_openai_api_key',''); if(empty($key)) wp_send_json_error(['message'=>'OpenAI API key missing']); $url='https://api.openai.com/v1/chat/completions'; $headers=['Authorization'=>'Bearer '.$key,'Content-Type'=>'application/json']; }
        $resp=wp_remote_post($url,['headers'=>$headers,'body'=>wp_json_encode($body),'timeout'=>45]); if(is_wp_error($resp)) wp_send_json_error(['message'=>$resp->get_error_message()]);
        $code=wp_remote_retrieve_response_code($resp); $data=json_decode(wp_remote_retrieve_body($resp),true);
        if($code!==200){ $msg=!empty($data['error']['message'])?$data['error']['message']:'HTTP '.$code; wp_send_json_error(['message'=>$msg,'raw'=>$data]); }
        $content=$data['choices'][0]['message']['content'] ?? ''; wp_send_json_success(['message'=>'OK','reply'=>$content,'raw'=>$data]); }
}
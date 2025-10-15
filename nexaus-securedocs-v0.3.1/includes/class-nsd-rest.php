<?php
namespace NexausSecureDocs;
if (!defined('ABSPATH')) { exit; }
class REST {
    public static function init(){ add_action('rest_api_init',[__CLASS__,'routes']); }
    public static function routes(){ register_rest_route('nexaus/v1','/generate',['methods'=>'POST','permission_callback'=>function(){ return is_user_logged_in(); },'callback'=>[__CLASS__,'handle_generate']]); }
    public static function handle_generate($request){
        $p=$request->get_json_params(); $type=sanitize_text_field($p['type'] ?? 'policy'); $template=sanitize_text_field($p['template'] ?? 'iso27001-information-security-policy');
        $brand=sanitize_text_field($p['brand'] ?? get_option('nsd_brand_name', get_bloginfo('name'))); $industry=sanitize_text_field($p['industry'] ?? ''); $scope=wp_kses_post($p['scope'] ?? '');
        $content=Generator::generate($type,$template,$brand,$industry,$scope); if(is_wp_error($content)) return $content;
        $post_type = $type==='risk'?'nsd_risk':($type==='pack'?'nsd_cepack':'nsd_policy');
        $post_id = wp_insert_post(['post_type'=>$post_type,'post_title'=>sanitize_text_field("$brand - ".ucwords(str_replace('-',' ',$template))),'post_content'=>wp_kses_post($content),'post_status'=>'publish','post_author'=>get_current_user_id()],true);
        if(is_wp_error($post_id)) return new \WP_Error('nsd_insert_failed','Failed to save generated document.');
        return ['post_id'=>$post_id,'edit_link'=>get_edit_post_link($post_id,'')];
    }
}
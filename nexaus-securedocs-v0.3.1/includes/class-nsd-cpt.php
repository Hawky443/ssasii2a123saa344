<?php
namespace NexausSecureDocs;
if (!defined('ABSPATH')) { exit; }
class CPT {
    public static function init(){ add_action('init',[__CLASS__,'register']); }
    public static function register(){
        register_post_type('nsd_policy',['labels'=>['name'=>'Policies','singular_name'=>'Policy'],'public'=>false,'show_ui'=>true,'show_in_menu'=>'nsd-root','supports'=>['title','editor','author','revisions'],'capability_type'=>'post','map_meta_cap'=>true]);
        register_post_type('nsd_risk',['labels'=>['name'=>'Risk Register','singular_name'=>'Risk Entry'],'public'=>false,'show_ui'=>true,'show_in_menu'=>'nsd-root','supports'=>['title','editor','author','revisions'],'capability_type'=>'post','map_meta_cap'=>true]);
        register_post_type('nsd_cepack',['labels'=>['name'=>'CE Packs','singular_name'=>'CE Pack'],'public'=>false,'show_ui'=>true,'show_in_menu'=>'nsd-root','supports'=>['title','editor','author','revisions'],'capability_type'=>'post','map_meta_cap'=>true]);
    }
}
<?php
namespace NexausSecureDocs;
if (!defined('ABSPATH')) { exit; }
class Plugin {
    public static function init() {
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('admin_menu', [__CLASS__, 'add_menu'], 9);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        add_shortcode('nsd_dashboard', [__CLASS__, 'dashboard_shortcode']);
        add_shortcode('nsd_generate', [__CLASS__, 'generate_shortcode']);
    }
    public static function register_settings() {
        register_setting('nsd_settings_group', 'nsd_ai_provider', ['type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>'groq']);
        register_setting('nsd_settings_group', 'nsd_groq_api_key', ['type'=>'string','sanitize_callback'=>'sanitize_text_field']);
        register_setting('nsd_settings_group', 'nsd_openai_api_key', ['type'=>'string','sanitize_callback'=>'sanitize_text_field']);
        register_setting('nsd_settings_group', 'nsd_model', ['type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>'']);
        register_setting('nsd_settings_group', 'nsd_brand_name', ['type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>get_bloginfo('name')]);
    }
    public static function add_menu() {
        add_menu_page(__('SecureDocs','nexaus-securedocs'), __('SecureDocs','nexaus-securedocs'),'edit_posts','nsd-root',[__CLASS__,'root_page'],'dashicons-shield-alt',58);
        add_submenu_page('nsd-root', __('Dashboard','nexaus-securedocs'), __('Dashboard','nexaus-securedocs'),'edit_posts','nsd-dashboard',[__CLASS__,'submenu_dashboard']);
        add_submenu_page('nsd-root', __('Policies','nexaus-securedocs'), __('Policies','nexaus-securedocs'),'edit_posts','edit.php?post_type=nsd_policy');
        add_submenu_page('nsd-root', __('Risk Register','nexaus-securedocs'), __('Risk Register','nexaus-securedocs'),'edit_posts','edit.php?post_type=nsd_risk');
        add_submenu_page('nsd-root', __('CE Packs','nexaus-securedocs'), __('CE Packs','nexaus-securedocs'),'edit_posts','edit.php?post_type=nsd_cepack');
        add_submenu_page('nsd-root', __('Settings','nexaus-securedocs'), __('Settings','nexaus-securedocs'),'manage_options','nsd-settings',[__CLASS__,'settings_page']);
    }
    public static function root_page(){ echo '<div class="wrap"><h1>SecureDocs</h1><p>Use the submenu to navigate.</p></div>'; }
    public static function submenu_dashboard(){ echo do_shortcode('[nsd_dashboard]'); }
    public static function enqueue_admin_assets($hook){ if (strpos($hook,'nsd')===false) return; wp_enqueue_script('nsd-admin', NSD_URL.'assets/js/admin.js',['jquery'],NSD_VERSION,true); wp_localize_script('nsd-admin','NSDAdmin',['ajax'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('nsd_admin')]); }
    public static function enqueue_assets(){ wp_enqueue_script('nsd-app', NSD_URL.'assets/js/app.js',['jquery'],NSD_VERSION,true); wp_localize_script('nsd-app','NSD',['rest'=>esc_url_raw(rest_url('nexaus/v1')),'nonce'=>wp_create_nonce('wp_rest')]); }
    public static function settings_page(){
        $prov = esc_attr(get_option('nsd_ai_provider','groq')); $model = esc_attr(get_option('nsd_model','')); ?>
        <div class="wrap"><h1><?php echo esc_html__('Nexaus SecureDocs Settings','nexaus-securedocs'); ?></h1>
        <form method="post" action="options.php"><?php settings_fields('nsd_settings_group'); do_settings_sections('nsd_settings_group'); ?>
        <table class="form-table" role="presentation">
            <tr><th><label for="nsd_ai_provider">AI Provider</label></th><td>
                <select name="nsd_ai_provider" id="nsd_ai_provider">
                    <option value="groq" <?php selected($prov,'groq'); ?>>Groq</option>
                    <option value="openai" <?php selected($prov,'openai'); ?>>OpenAI</option>
                </select></td></tr>
            <tr><th><label for="nsd_groq_api_key">Groq API Key</label></th><td><input type="password" name="nsd_groq_api_key" id="nsd_groq_api_key" value="<?php echo esc_attr(get_option('nsd_groq_api_key','')); ?>" class="regular-text" /><p class="description">console.groq.com/keys</p></td></tr>
            <tr><th><label for="nsd_openai_api_key">OpenAI API Key</label></th><td><input type="password" name="nsd_openai_api_key" id="nsd_openai_api_key" value="<?php echo esc_attr(get_option('nsd_openai_api_key','')); ?>" class="regular-text" /></td></tr>
            <tr><th><label for="nsd_model">Model</label></th><td>
                <select name="nsd_model" id="nsd_model">
                <?php $models = get_transient('nsd_model_list'); if(!is_array($models)) $models=[]; foreach($models as $m){ echo '<option value="'.esc_attr($m).'" '.selected($model,$m,false).'>'.esc_html($m).'</option>'; } ?>
                </select>
                <p class="description">Click "Fetch Models" to refresh this list. If empty, Save once and fetch.</p>
                <p><button type="button" class="button" id="nsd-fetch-models">Fetch Models</button> <button type="button" class="button" id="nsd-test-connection">Test Connection</button></p>
                <div id="nsd-admin-output" style="margin-top:8px;"></div>
            </td></tr>
            <tr><th><label for="nsd_brand_name">Default Brand/Company Name</label></th><td><input type="text" name="nsd_brand_name" id="nsd_brand_name" value="<?php echo esc_attr(get_option('nsd_brand_name', get_bloginfo('name'))); ?>" class="regular-text" /></td></tr>
        </table><?php submit_button(); ?></form>
        <p>Shortcodes: <code>[nsd_dashboard]</code>, <code>[nsd_generate type="policy" template="iso27001-information-security-policy"]</code></p></div><?php
    }
    public static function dashboard_shortcode($atts=[]){
        if (!is_user_logged_in()) return '<p>Please <a href="'.esc_url(wp_login_url(get_permalink())).'">log in</a>.</p>';
        ob_start(); ?>
        <div class="nsd-dashboard"><h2>SecureDocs Dashboard</h2><p>Create policies, risk registers, and CE packs.</p>
        <div class="nsd-actions"><a class="button button-primary" href="<?php echo esc_url(add_query_arg('nsd','new-policy')); ?>">New Policy</a>
        <a class="button" href="<?php echo esc_url(add_query_arg('nsd','new-risk')); ?>">New Risk Entry</a>
        <a class="button" href="<?php echo esc_url(add_query_arg('nsd','new-pack')); ?>">New CE Pack</a></div></div>
        <?php return ob_get_clean();
    }
    public static function generate_shortcode($atts=[]){
        if (!is_user_logged_in()) return '<p>You must be logged in.</p>';
        $atts = shortcode_atts(['type'=>'policy','template'=>'iso27001-information-security-policy'],$atts);
        ob_start(); ?>
        <form class="nsd-generate" data-type="<?php echo esc_attr($atts['type']); ?>" data-template="<?php echo esc_attr($atts['template']); ?>">
        <h3>Generate <?php echo esc_html(ucfirst($atts['type'])); ?></h3>
        <p><label>Company/Brand Name <input type="text" name="brand" value="<?php echo esc_attr(get_option('nsd_brand_name', get_bloginfo('name'))); ?>" required></label></p>
        <p><label>Industry <input type="text" name="industry" placeholder="e.g., MSP, SaaS, Construction" required></label></p>
        <p><label>Scope / Notes <textarea name="scope" rows="6" placeholder="Describe the organisation, assets, scope, controls, or any specifics to include."></textarea></label></p>
        <p><button type="submit" class="button button-primary">Generate</button></p>
        <div class="nsd-output"></div></form>
        <?php return ob_get_clean();
    }
}
<?php
/**
 * Plugin Name: Nexaus SecureDocs
 * Description: AI-powered generator for cybersecurity policies, risk reports, and CE packs. Clean HTML output only. Unified admin menu. Groq + OpenAI with live model picker.
 * Version: 0.3.1
 * Author: Nexaus
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Text Domain: nexaus-securedocs
 */
if (!defined('ABSPATH')) { exit; }
define('NSD_VERSION', '0.3.1');
define('NSD_PATH', plugin_dir_path(__FILE__));
define('NSD_URL', plugin_dir_url(__FILE__));
require_once NSD_PATH . 'includes/class-nsd-plugin.php';
require_once NSD_PATH . 'includes/class-nsd-cpt.php';
require_once NSD_PATH . 'includes/class-nsd-rest.php';
require_once NSD_PATH . 'includes/class-nsd-generator.php';
require_once NSD_PATH . 'includes/class-nsd-adminajax.php';
add_action('plugins_loaded', function() {
    \NexausSecureDocs\Plugin::init();
    \NexausSecureDocs\CPT::init();
    \NexausSecureDocs\REST::init();
    \NexausSecureDocs\AdminAjax::init();
});
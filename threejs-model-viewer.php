<?php

/**
 * Plugin Name: ThreeJS Model Viewer
 * Plugin URI: FILL_IN_LATER
 * Plugin Description: Plugin to add ThreeJS Based Model Loading on WordPress Websites
 * Version: Alpha 0.0.26
 * Author: Ryan Chippendale
 * Author URI: FILL_IN_LATER
 * License: GPL2
 */

define('version_number', '0.0.26');

if (!defined('WPINC')) {
    die;
}

/* Main class */
class ThreeJS_Model_Viewer {
    protected $loader;
    protected $admin;
    protected $plugin_slug;
    protected $version;

    public function __construct() {
        $this->plugin_slug = 'threejs-model-viewer';
        $this->version = version_number;

        $this->load_dependencies(); 
        $this->define_admin_hooks();
        $this->define_shortcodes();
    }
    
    private function define_shortcodes() {
        add_shortcode('threejs_model', array($this, 'insert_model'));
    }

    public function insert_model($atts) {
        $atts = shortcode_atts(array(
            'id' => null
        ), $atts, 'threejs_model');
        
        if (empty($atts['id'])) {
            return '';
        }
        
        $model_id = $atts['id'];
        
        // Load the 3D model with the given ID
        $model = $this->load_model($model_id);
        
        if (!$model) {
            return '';
        }
        
        // Render the HTML representation of the model
        $html = '<div class="threejs-model">' . $model->render() . '</div>';
        
        return $html;
    
    }
    private function load_dependencies() {
        require_once (plugin_dir_path(__FILE__) . 'includes/class-threejs-model-viewer-loader.php');
        require_once (plugin_dir_path(__FILE__) . 'admin/class-threejs-model-viewer-admin.php');

        $this->loader = new ThreeJS_Model_Viewer_Loader(); 
        $this->admin = new ThreeJS_Model_Viewer_Admin($this->get_plugin_slug(), version_number);
    }

    private function define_admin_hooks() {
        $this->loader->add_action('admin_enqueue_scripts', $this->admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $this->admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $this->admin, 'add_admin_menu');
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_slug() {
        return $this->plugin_slug;
    }
}
 
function run_threejs_model_viewer() {
    $tjs = new ThreeJS_Model_Viewer();
    $tjs->run(); 
}

run_threejs_model_viewer();

?>

<?php

/**
 * Plugin Name: ThreeJS Model Viewer
 * Plugin URI: FILL_IN_LATER
 * Plugin Description: Plugin to add ThreeJS Based Model Loading on WordPress Websites
 * Version: Alpha 0.0.4
 * Author: Ryan Chippendale
 * Author URI: FILL_IN_LATER
 * License: GPL2
 */

 if (!defined('WPINC')) {
    die;
 }

 class ThreeJS_Model_Viewer {
    protected $loader;
    protected $plugin_slug;
    protected $version;

    public function __construct() {
        $this->plugin_slug = 'threejs-model-viewer';
        $this->version = 'Alpha 0.0.4';

        $this->load_dependencies(); 
        $this->define_admin_hooks();
    }
    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-threejs-model-viewer-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-threejs-model-viewer-admin.php';
        $this->loader = new ThreeJS_Model_Viewer_Loader(); 
    }
    private function define_admin_hooks() {
        $admin = new ThreeJS_Model_Viewer_Admin($this->get_version());
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_scripts');
        $this->loader->add_action('add_meta_boxes', $admin, 'add_meta-boxes');
        $this->loader->add_action('save_post', $admin, 'save_post');
        $this->loader->add_action('admin_menu', $admin, 'add_admin_menu');
    }
    public function run() {
        $this->loader->run();
    }
    public function get_version() {
        return $this->version;
    }
 }
 
 function run_threejs_model_viewer() {
    $tjs = new ThreeJS_Model_Viewer();
    $tjs->run(); 
 }
 run_threejs_model_viewer();
 ?>
<?php
class ThreeJS_Model_Viewer_Admin {
    private $version;
    private $plugin_slug;

    public function __construct($plugin_slug, $version) {
        $this->plugin_slug = $plugin_slug;
        $this->version = $version;
        
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function register_settings() {
        // Initialize the 'threejs_models' option if it doesn't exist
        if (false === get_option('threejs_models')) {
            add_option('threejs_models', array());
        }

        register_setting('threejs-model-viewer-settings', 'threejs_models'); 
    
        add_settings_section(
            'threejs_model_manager_section',
            'ThreeJS Model Manager',
            array($this, 'model_manager_section_callback'),
            'threejs-model-viewer-settings'
        );
    
        add_settings_field(
            'threejs_model',
            '3D Model',
            array($this, 'threejs_model_render'),
            'threejs-model-viewer-settings',
            'threejs_model_manager_section'
        );
    }

    public function model_manager_section_callback() {
        echo '<p>Add and manage your 3D Models here</p>';
    }

    public function threejs_model_render() {
        $options = get_option('threejs_models');
        $model = isset($options['threejs_model']) ? $options['threejs_model'] : '';
        ?>
            <input type='text' name='threejs_models[threejs_model]' value='<?php echo $model; ?>'>
        <?php
    }


    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_slug, plugin_dir_url(__FILE__) . 'css/admin.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_slug, plugin_dir_url(__FILE__) . 'js/admin.js', array('jquery'), $this->version, false);
        wp_enqueue_script('three-js', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/110/three.min.js', array(), null, true);
    }

    public function add_admin_menu() {
        add_menu_page(
            'ThreeJS Model Manager',
            'ThreeJS Models',
            'manage_options',
            'threejs-model-manager',
            array($this, 'display_model_manager_page'),
            'dashicons-admin-media',
            20
        );
    }

    public function display_model_manager_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
    
        echo '<div class="wrap">';
        echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';
    
        echo '<form method="post" action="options.php" enctype="multipart/form-data">';
        settings_fields('threejs-model-viewer-settings');
        do_settings_sections('threejs-model-viewer-settings');
        submit_button();
        echo '</form>';
    
        echo '</div>';
    }
    
    
    
    

    public function run()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
}
?>

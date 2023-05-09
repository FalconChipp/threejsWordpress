<php
class ThreeJS_Model_Viewer_Admin {
    private $version;
    private $plugin_slug;

    public function __construct($plugin_slug, $version) {
        $this->plugin_slug = $plugin_slug;
        $this->version = $version;
    }
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_slug, plugin_dir-url(__FILE__) . 'css/admin.css', array(), $this->version, 'all');
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
        );''
    }
    public function display_model_manager_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div class="wrap">'
        echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';
        echo '<form method="post" action="options.php">';
            <!-- // Display form fields here -->
        echo '</form>'
        echo '</div>'
    }
    public function add_meta_boxes() {
        add_meta_box(
            'threejs_model_viewer_meta_box',
            __('ThreeJS Model Viewer', 'threejs-model-viewer'),
            array($this, 'render_meta_box'),
            'post',
            'normal',
            'high'
        )
    }
    public function render_meta_box($post) {
        wp_nonce_field('threejs_model_viewer_nonce_action', 'threejs_model_viewer_nonce'); 

        $threejs_model = get_post_meta($post->ID, '_threejs_model', true);
        $threejs_position = get_post_meta($post->ID, '_threejs_position', true);
        $threejs_lighting = get_post_meta($post->ID, '_threejs_lighting', true);
        $threejs_lighting_position = get_post_meta($post->ID, '_threejs_lighting_position', true);

        echo '<label for="threejs_model">3D Model</label>';
        echo '<input type="text" id="threejs_model" name="threejs_model" value="' . esc_attr($threejs_model) . '" size="25" />';
        echo '<label for="threejs_position">Model Position</label>';
        echo '<input type="text" id="threejs_position" name="threejs_position" value="' . esc_attr($threejs_position) . '" size="25" />';
        echo '<label for="threejs_lighting">Lighting</label>';
        echo '<select id="threejs_lighting" name="threejs_lighting">';
        echo '<option value="Ambient" ' . selected($threejs_lighting, 'Ambient', false) . '>Ambient</option>';
        echo '<option value="Point" ' . selected($threejs_lighting, 'Point', false) . '>Point</option>';
        echo '</select>';
        echo '<label for="threejs_lighting_position">Lighting Position</label>';
        echo '<input type="text" id="threejs_lighting_position" name="threejs_lighting_position" value="' . esc_attr($threejs_lighting_position) . '" size="25" />';
    }
    public function save_post($post_id) {
        if (!isset($_POST['threejs_model_viewer_nonce']) || !wp_verify_nonce($_POST['threejs_model_viewer_nonce'], 'threejs_model_viewer_nonce_action')) {
            return $post_id;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
        if (isset($_POST['threejs_model'])) {
            update_post_meta($post_id, '_threejs_model', sanitize_text_field($_POST['threejs_model']));
        }
        if (isset($_POST['threejs_position'])) {
            update_post_meta($post_id, '_threejs_position', sanitize_text_field($_POST['threejs_position']));
        }
        if (isset($_POST['threejs_lighting'])) {
            update_post_meta($post_id, '_threejs_lighting', sanitize_text_field($_POST['threejs_lighting']));
        }
        if (isset($_POST['threejs_lighting_position'])) {
            update_post_meta($post_id, '_threejs_lighting_position', sanitize_text_field($_POST['threejs_lighting_position']));
        }
    }
    public function run()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
}
?>
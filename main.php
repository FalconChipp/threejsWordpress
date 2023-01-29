<?php
/*
Plugin Name: 3D Model Customiser
Description: Allows users to add and customise 3D models using three.js
Version: Beta 0.0.6
Author: Ryan Chippendale
*/

function enqueue_plugin_styles() {
    wp_enqueue_style( 'plugin-styles', plugin_dir_url( __FILE__ ) . 'style.css' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_plugin_styles' );

function register_3d_model_customizer() {
    $labels = array(
        'name' => '3D Models',
        'singular_name' => '3D Model',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New 3D Model',
        'edit_item' => 'Edit 3D Model',
        'new_item' => 'New 3D Model',
        'view_item' => 'View 3D Model',
        'search_items' => 'Search 3D Models',
        'not_found' =>  'No 3D models found',
        'not_found_in_trash' => 'No 3D models found in Trash',
        'parent_item_colon' => '',
        'menu_name' => '3D Models'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => '3d-model' ),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
    );

    register_post_type( '3d-model', $args );
}
add_action( 'init', 'register_3d_model_customizer' );

function enqueue_3d_model_customizer_scripts() {
    wp_enqueue_script( 'threejs', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r121/three.min.js', array(), 'r121', true );
    // wp_enqueue_script( '3d-model-customizer', plugin_dir_url( __FILE__ ) . '3d-model-customizer.js', array( 'threejs' ), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_3d_model_customizer_scripts' );

function add_3d_model_customizer_meta_box() {
    add_meta_box( '3d-model-customizer-meta-box', '3D Model Customizer', 'render_3d_model_customizer_meta_box', '3d-model', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'add_3d_model_customizer_meta_box' );

function render_3d_model_customizer_meta_box( $post ) {
    // $model_url = get_post_meta( $post->ID, 'model_url', true );

    wp_nonce_field('my_meta_box_nonce', 'meta_nonce_box');
    $models = get_posts(
        array(
            'post_type' => 'attachment',
            'post_mime_type' => 'model/*',
            'post_status' => 'inherit',
            'posts_per_page' => -1
        )
    );

    $model_options = array();
    foreach ($models as $model) {
        $model_options[$model->guid] = $model->post_title;
    }

    $current_model = get_post_meta($post->ID, '3d model', true);
    echo '<label for="3d_model">Model:</label>';
    echo '<select name="3d_model" id="3d_model">';
    echo '<option value=""> Select a model</option>';
    foreach ($model_options as $value => $label) {
        echo '<option value="' . value . '" ' . selected($current_model, $value, false) . '>' . $label . '</option>';
    }
    echo '</select>';

    $lights = get_post_meta( $post->ID, 'lights', true );
    ?>

    <h2>Lights</h2>
    <div id="lights-container">
        <?php
        if ( $lights ) {
            foreach ( $lights as $light ) {
                ?>
                <div class="light">
                    <p>
                        <label for="type">Type:</label><br>
                        <select name="lights[<?php echo $light; ?>][type]" id="type">
                            <option value="ambient" <?php selected( $light['type'], 'ambient' ); ?>>Ambient</option>
                            <option value="directional" <?php selected( $light['type'], 'directional' ); ?>>Directional</option>
                            <option value="point" <?php selected( $light['type'], 'point' ); ?>>Point</option>
                            <option value="spot" <?php selected( $light['type'], 'spot' ); ?>>Spot</option>
                        </select>
                    </p>
                    <p>
                        <label for="position_x">Position X:</label><br>
                        <input type="number" name="lights[<?php echo $light; ?>][position_x]" id="position_x" value="<?php echo esc_attr( $light['position_x'] ); ?>">
                    </p>
                    <p>
                        <label for="position_y">Position Y:</label><br>
                        <input type="number" name="lights[<?php echo $light; ?>][position_y]" id="position_y" value="<?php echo esc_attr( $light['position_y'] ); ?>">
                    </p>
                    <p>
                        <label for="position_z">Position Z:</label><br>
                        <input type="number" name="lights[<?php echo $light; ?>][position_z]" id="position_z" value="<?php echo esc_attr( $light['position_z'] ); ?>">
                    </p>
                </div>
                <?php
            }
        }
        ?>
    </div>
    <button type="button" id="add-light-button">Add Light</button>
    <script>
    // Add light fields when "Add Light" button is clicked
    var lightCounter = 0;
    jQuery('#add-light-button').on('click', function() {
        lightCounter++;
        var lightHtml = '<div class="light"><p><label for="type">Type:</label><br><select name="lights[' + lightCounter + '][type]" id="type"><option value="ambient">Ambient</option><option value="directional">Directional</option><option value="point">Point</option><option value="spot">Spot</option></select></p><p><label for="position_x">Position X:</label><br><input type="number" name="lights[' + lightCounter + '][position_x]" id="position_x"></p><p><label for="position_y">Position Y:</label><br><input type="number" name="lights[' + lightCounter + '][position_y]" id="position_y"></p><p><label for="position_z">Position Z:</label><br><input type="number" name="lights[' + lightCounter + '][position_z]" id="position_z"></p></div>';
        jQuery('#lights-container').append(lightHtml);
    });
</script>
<?php
}

function save_3d_model_customizer_meta_box( $post_id ) {
    if(!isset($_POST['meta_box_nonce']) || !wp_verify_nonce($_POST['meta_box_nonce'], 'my_meta_box_nonce')) {
        return; 
    }
    if (isset($_POST['3d_model'])) {
        update_post_meta($post_id, '3d_model', esc_url_raw($_POST['3d_model']));
    }
    if ( isset( $_POST['lights'] ) ) {
        $lights = array_map( function( $light ) {
            return array(
                'type' => sanitize_text_field( $light['type'] ),
                'position_x' => floatval( $light['position_x'] ),
                'position_y' => floatval( $light['position_y'] ),
                'position_z' => floatval( $light['position_z'] ),
            );
        }, $_POST['lights'] );
        update_post_meta( $post_id, 'lights', $lights );
    }
}

function display_3d_model( $atts ) {
    $atts = shortcode_atts( array(
        'model_url' => '',
        'lights' => '',
    ), $atts, '3d-model' );

    ob_start();
    ?>
    <div id="3d-model-container"></div>
    <script>
        var modelUrl = document.getElementById('3d_model').value; 
        var lights = <?php echo json_encode( $atts['lights'] ); ?>;
        var scene = new THREE.Scene();
var camera = new THREE.PerspectiveCamera( 75, window.innerWidth / window.innerHeight, 0.1, 1000 );
var renderer = new THREE.WebGLRenderer();
renderer.setSize( window.innerWidth, window.innerHeight );
document.getElementById('3d-model-container').appendChild( renderer.domElement );

// Load 3D model
var loader = new THREE.GLTFLoader();
loader.load( modelUrl, function ( gltf ) {
    scene.add( gltf.scene );
}, undefined, function ( error ) {
    console.error( error );
} );

// Add lights
lights.forEach( function ( light ) {
    var threeLight;
    switch ( light.type ) {
        case 'ambient':
            threeLight = new THREE.AmbientLight( light.color );
            break;
        case 'directional':
            threeLight = new THREE.DirectionalLight( light.color, light.intensity );
            threeLight.position.set( light.position_x, light.position_y, light.position_z );
            break;
        case 'point':
            threeLight = new THREE.PointLight( light.color, light.intensity, light.distance );
            threeLight.position.set( light.position_x, light.position_y, light.position_z );
            break;
        case 'spot':
            threeLight = new THREE.SpotLight( light.color, light.intensity, light.distance, light.angle, light.penumbra );
            threeLight.position.set( light.position_x, light.position_y, light.position_z );
            break;
    }
    scene.add( threeLight );
} );

// Render loop
var animate = function () {
    requestAnimationFrame( animate );
    renderer.render( scene, camera );
};
animate();
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( '3d-model', 'display_3d_model' );
<?php
/**
 * Plugin Name: Custom Comment System
 * Description: A custom AJAX-based comment system plugin for WordPress featuring a beautifully customized form and nested admin reply layouts.
 * Version: 1.0.3
 * Author: AlirezaKMaxim
 * Author URI: https://github.com/AlirezaKmaxim
 * Text Domain: custom-comments-system
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define Plugin Constants
define( 'CCS_PATH', plugin_dir_path( __FILE__ ) );
define( 'CCS_URL', plugin_dir_url( __FILE__ ) );
define( 'CCS_VERSION', '1.0.3' );

// Include Logic Files
require_once CCS_PATH . 'includes/class-form.php';
require_once CCS_PATH . 'includes/class-display.php';

class Custom_Comment_System {

    private static $instance = null;
    private $form_handler;
    private $display_handler;

    /**
     * Singleton Instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Initialize Handlers
        $this->form_handler    = new Custom_Comment_Form();
        $this->display_handler = new Custom_Comment_Display();

        // Hook to register assets
        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );

        // Register Shortcode
        add_shortcode( 'custom_comments', array( $this, 'render_comment_system' ) );
    }

    /**
     * Register scripts and styles so they can be enqueued conditionally
     */
    public function register_assets() {
        // Enqueue Vazirmatn Font Face CDN
        wp_register_style(
            'ccs-vazirmatn-font',
            'https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css',
            array(),
            '33.003'
        );

        // Register Custom Comment Styling
        wp_register_style(
            'ccs-custom-style',
            CCS_URL . 'assets/css/style.css',
            array( 'ccs-vazirmatn-font' ),
            CCS_VERSION
        );

        // Register AJAX Submission Script
        wp_register_script(
            'ccs-custom-script',
            CCS_URL . 'assets/js/script.js',
            array(),
            CCS_VERSION,
            true
        );

        // Localize AJAX parameters
        wp_localize_script(
            'ccs-custom-script',
            'custom_comment_ajax_obj',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' )
            )
        );
    }

    /**
     * Shortcode handler [custom_comments]
     *
     * @return string
     */
    public function render_comment_system() {
        // Only load assets when the shortcode is actually rendered
        wp_enqueue_style( 'ccs-vazirmatn-font' );
        wp_enqueue_style( 'ccs-custom-style' );
        wp_enqueue_script( 'ccs-custom-script' );

        // Start Output Buffering
        ob_start();
        ?>
        <div id="custom-comments-system-container">
            <?php 
            // Render Comment Input Form
            echo $this->form_handler->render_form(); 
            ?>
            <h2 class="comments-title">نظرات کاربران</h2>
            <?php
            // Render Comment List
            echo $this->display_handler->render_comments_list();
            ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Bootstrap the plugin
add_action( 'plugins_loaded', array( 'Custom_Comment_System', 'get_instance' ) );

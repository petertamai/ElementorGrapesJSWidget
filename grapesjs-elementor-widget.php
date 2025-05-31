<?php
/**
 * Plugin Name: GrapesJS Elementor Widget
 * Description: Adds GrapesJS visual editor as an Elementor widget for drag-and-drop HTML editing
 * Version: 1.0.0
 * Author: Piotr Tamulewicz
 * Text Domain: grapesjs-elementor-widget
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Elementor tested up to: 3.20.0
 * Elementor Pro tested up to: 3.20.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Main GrapesJS Elementor Widget Class
 */
final class GrapesJS_Elementor_Widget {

    /**
     * Plugin Version
     */
    const VERSION = '1.0.0';

    /**
     * Minimum Elementor Version
     */
    const MINIMUM_ELEMENTOR_VERSION = '3.0.0';

    /**
     * Minimum PHP Version
     */
    const MINIMUM_PHP_VERSION = '7.4';

    /**
     * Instance
     */
    private static $_instance = null;

    /**
     * Singleton Instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        if ( $this->is_compatible() ) {
            add_action( 'elementor/init', [ $this, 'init' ] );
        }
    }

    /**
     * Compatibility Checks
     */
    public function is_compatible() {
        // Check if Elementor installed and activated
        if ( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor' ] );
            return false;
        }

        // Check for required Elementor version
        if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
            return false;
        }

        // Check for required PHP version
        if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
            return false;
        }

        return true;
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Add Plugin actions - use late priority to avoid conflicts
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ], 999 );
        
        // Register AJAX handlers
        add_action( 'wp_ajax_grapesjs_save_content', [ $this, 'ajax_save_content' ] );
        add_action( 'wp_ajax_nopriv_grapesjs_save_content', [ $this, 'ajax_save_content' ] );
    }

    /**
     * Admin notice - Missing Elementor
     */
    public function admin_notice_missing_elementor() {
        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor */
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'grapesjs-elementor-widget' ),
            '<strong>' . esc_html__( 'GrapesJS Elementor Widget', 'grapesjs-elementor-widget' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor', 'grapesjs-elementor-widget' ) . '</strong>'
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
    }

    /**
     * Admin notice - Minimum Elementor version
     */
    public function admin_notice_minimum_elementor_version() {
        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'grapesjs-elementor-widget' ),
            '<strong>' . esc_html__( 'GrapesJS Elementor Widget', 'grapesjs-elementor-widget' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor', 'grapesjs-elementor-widget' ) . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
    }

    /**
     * Admin notice - Minimum PHP version
     */
    public function admin_notice_minimum_php_version() {
        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'grapesjs-elementor-widget' ),
            '<strong>' . esc_html__( 'GrapesJS Elementor Widget', 'grapesjs-elementor-widget' ) . '</strong>',
            '<strong>' . esc_html__( 'PHP', 'grapesjs-elementor-widget' ) . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
    }

    /**
     * Register Widgets
     */
    public function register_widgets( $widgets_manager ) {
        require_once( plugin_dir_path( __FILE__ ) . 'includes/widgets/html-editor-widget.php' );
        $widgets_manager->register( new \GrapesJS_HTML_Editor_Widget() );
    }

    /**
     * AJAX handler for saving content
     */
    public function ajax_save_content() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'grapesjs_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Get and sanitize data
        $widget_id = sanitize_text_field( $_POST['widget_id'] );
        $html = wp_kses_post( $_POST['html'] );
        $css = sanitize_textarea_field( $_POST['css'] );
        $components = sanitize_textarea_field( $_POST['components'] );
        $styles = sanitize_textarea_field( $_POST['styles'] );

        // Save as transient (you might want to use a custom table for production)
        set_transient( 'grapesjs_content_' . $widget_id, [
            'html' => $html,
            'css' => $css,
            'components' => $components,
            'styles' => $styles
        ], YEAR_IN_SECONDS );

        wp_send_json_success( [ 'message' => 'Content saved successfully' ] );
    }
}

// Initialize the plugin
GrapesJS_Elementor_Widget::instance();
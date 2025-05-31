<?php
/**
 * GrapesJS HTML Editor Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Elementor HTML Editor Widget using GrapesJS
 */
class GrapesJS_HTML_Editor_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'grapesjs_html_editor';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return esc_html__( 'HTML Editor', 'grapesjs-elementor-widget' );
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-code';
    }

    /**
     * Get widget categories
     */
    public function get_categories() {
        return [ 'general' ];
    }

    /**
     * Get widget keywords
     */
    public function get_keywords() {
        return [ 'html', 'editor', 'grapesjs', 'visual', 'builder', 'code' ];
    }

    /**
     * Register widget controls
     */
    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'HTML Editor', 'grapesjs-elementor-widget' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'editor_height',
            [
                'label' => esc_html__( 'Editor Height', 'grapesjs-elementor-widget' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range' => [
                    'px' => [
                        'min' => 300,
                        'max' => 1200,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 500,
                ],
            ]
        );

        $this->add_control(
            'initial_html',
            [
                'label' => esc_html__( 'Initial HTML', 'grapesjs-elementor-widget' ),
                'type' => \Elementor\Controls_Manager::CODE,
                'language' => 'html',
                'rows' => 10,
                'default' => '<div class="my-content">\n  <h1>Welcome to GrapesJS</h1>\n  <p>Start editing or drag blocks from the right panel</p>\n</div>',
                'description' => esc_html__( 'Default HTML content for the editor', 'grapesjs-elementor-widget' ),
            ]
        );

        $this->add_control(
            'initial_css',
            [
                'label' => esc_html__( 'Initial CSS', 'grapesjs-elementor-widget' ),
                'type' => \Elementor\Controls_Manager::CODE,
                'language' => 'css',
                'rows' => 10,
                'default' => '.my-content {\n  padding: 20px;\n  text-align: center;\n}\n\n.my-content h1 {\n  color: #333;\n}\n\n.my-content p {\n  color: #666;\n}',
                'description' => esc_html__( 'Default CSS styles for the editor', 'grapesjs-elementor-widget' ),
            ]
        );

        $this->add_control(
            'widget_unique_id',
            [
                'label' => esc_html__( 'Widget ID', 'grapesjs-elementor-widget' ),
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'default' => '',
            ]
        );

        $this->end_controls_section();

        // Advanced Section
        $this->start_controls_section(
            'advanced_section',
            [
                'label' => esc_html__( 'Advanced', 'grapesjs-elementor-widget' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'enable_storage',
            [
                'label' => esc_html__( 'Enable Local Storage', 'grapesjs-elementor-widget' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'grapesjs-elementor-widget' ),
                'label_off' => esc_html__( 'No', 'grapesjs-elementor-widget' ),
                'return_value' => 'yes',
                'default' => 'no',
                'description' => esc_html__( 'Save work locally in browser', 'grapesjs-elementor-widget' ),
            ]
        );

        $this->add_control(
            'show_borders',
            [
                'label' => esc_html__( 'Show Component Borders', 'grapesjs-elementor-widget' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'grapesjs-elementor-widget' ),
                'label_off' => esc_html__( 'No', 'grapesjs-elementor-widget' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => esc_html__( 'Show borders around components', 'grapesjs-elementor-widget' ),
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $widget_id = $this->get_id();
        $unique_id = ! empty( $settings['widget_unique_id'] ) ? $settings['widget_unique_id'] : $widget_id;
        $height = $settings['editor_height']['size'] . $settings['editor_height']['unit'];

        // Check if we're in the editor
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

        if ( $is_editor ) {
            ?>
            <div class="grapesjs-widget-wrapper" style="min-height: <?php echo esc_attr( $height ); ?>;">
                <div id="gjs-<?php echo esc_attr( $unique_id ); ?>" style="height: <?php echo esc_attr( $height ); ?>; border: 1px solid #ddd; background: #fff; position: relative;">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: #999;">
                        <div class="spinner" style="margin: 0 auto 10px; border: 3px solid #f3f3f3; border-top: 3px solid #0073aa; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite;"></div>
                        Loading GrapesJS Editor...
                    </div>
                </div>
                <button 
                    onclick="saveGJS_<?php echo esc_js( str_replace( '-', '_', $unique_id ) ); ?>()" 
                    style="margin-top: 10px; padding: 10px 20px; background: #0073aa; color: white; border: none; cursor: pointer; border-radius: 4px;">
                    Save Content
                </button>
            </div>

            <style>
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            /* Ensure minimum height for the widget wrapper */
            .elementor-widget-grapesjs_html_editor .elementor-widget-container {
                min-height: <?php echo esc_attr( $height ); ?>;
            }
            </style>

            <!-- Load GrapesJS only for this widget -->
            <?php if ( ! wp_script_is( 'grapesjs', 'enqueued' ) ) : ?>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.7/css/grapes.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/grapesjs-preset-webpage/1.0.2/grapesjs-preset-webpage.min.css">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.7/grapes.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/grapesjs-preset-webpage/1.0.2/grapesjs-preset-webpage.min.js"></script>
            <?php endif; ?>

            <script>
            // Use IIFE to avoid global scope pollution
            (function() {
                'use strict';
                
                // Wait for document ready and GrapesJS to load
                function waitForGrapesJS(callback) {
                    if (typeof grapesjs !== 'undefined') {
                        callback();
                    } else {
                        setTimeout(function() {
                            waitForGrapesJS(callback);
                        }, 100);
                    }
                }
                
                // Initialize editor when ready
                waitForGrapesJS(function() {
                    // Check if container exists and not already initialized
                    var container = document.getElementById('gjs-<?php echo esc_js( $unique_id ); ?>');
                    if (!container || container.hasAttribute('data-gjs-initialized')) {
                        return;
                    }
                    
                    // Mark as initialized
                    container.setAttribute('data-gjs-initialized', 'true');
                    
                    // Clear loading message
                    container.innerHTML = '';
                    
                    // Initialize GrapesJS
                    var editor = grapesjs.init({
                        container: '#gjs-<?php echo esc_js( $unique_id ); ?>',
                        height: '100%',
                        width: 'auto',
                        storageManager: <?php echo $settings['enable_storage'] === 'yes' ? 'true' : 'false'; ?>,
                        showOffsets: <?php echo $settings['show_borders'] === 'yes' ? 'true' : 'false'; ?>,
                        plugins: ['gjs-preset-webpage'],
                        pluginsOpts: {
                            'gjs-preset-webpage': {
                                blocksBasicOpts: {
                                    blocks: ['text', 'link', 'image', 'video', 'map', 'column1', 'column2', 'column3', 'column3-7'],
                                    flexGrid: true,
                                },
                                navbarOpts: false,
                                countdownOpts: false,
                                formsOpts: false,
                            }
                        },
                        canvas: {
                            styles: <?php echo json_encode( array( $settings['initial_css'] ) ); ?>,
                        },
                        components: <?php echo json_encode( $settings['initial_html'] ); ?>,
                    });
                    
                    // Store editor instance
                    window['gjs_editor_<?php echo esc_js( str_replace( '-', '_', $unique_id ) ); ?>'] = editor;
                });
                
                // Define save function in global scope
                window.saveGJS_<?php echo esc_js( str_replace( '-', '_', $unique_id ) ); ?> = function() {
                    var editor = window['gjs_editor_<?php echo esc_js( str_replace( '-', '_', $unique_id ) ); ?>'];
                    if (editor) {
                        var html = editor.getHtml();
                        var css = editor.getCss();
                        
                        // Simple AJAX save
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', '<?php echo admin_url( 'admin-ajax.php' ); ?>', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        
                        var data = 'action=grapesjs_save_content' +
                                   '&nonce=<?php echo wp_create_nonce( 'grapesjs_nonce' ); ?>' +
                                   '&widget_id=<?php echo esc_js( $unique_id ); ?>' +
                                   '&html=' + encodeURIComponent(html) +
                                   '&css=' + encodeURIComponent(css) +
                                   '&components=' + encodeURIComponent(JSON.stringify(editor.getComponents())) +
                                   '&styles=' + encodeURIComponent(JSON.stringify(editor.getStyle()));
                        
                        xhr.onreadystatechange = function() {
                            if (xhr.readyState === 4) {
                                if (xhr.status === 200) {
                                    alert('Content saved successfully!');
                                } else {
                                    alert('Error saving content!');
                                }
                            }
                        };
                        
                        xhr.send(data);
                    } else {
                        alert('Editor not initialized yet!');
                    }
                };
            })();
            </script>
            <?php
        } else {
            // Frontend display - show the saved content
            $saved_content = get_transient( 'grapesjs_content_' . $unique_id );
            
            if ( $saved_content && ! empty( $saved_content['html'] ) ) {
                echo '<div class="grapesjs-content-wrapper">';
                if ( ! empty( $saved_content['css'] ) ) {
                    echo '<style>' . esc_html( $saved_content['css'] ) . '</style>';
                }
                echo wp_kses_post( $saved_content['html'] );
                echo '</div>';
            } else {
                // Show initial content if no saved content
                echo '<div class="grapesjs-content-wrapper">';
                if ( ! empty( $settings['initial_css'] ) ) {
                    echo '<style>' . esc_html( $settings['initial_css'] ) . '</style>';
                }
                if ( ! empty( $settings['initial_html'] ) ) {
                    echo wp_kses_post( $settings['initial_html'] );
                } else {
                    echo '<p>No content yet. Edit with Elementor to add content.</p>';
                }
                echo '</div>';
            }
        }
    }
}
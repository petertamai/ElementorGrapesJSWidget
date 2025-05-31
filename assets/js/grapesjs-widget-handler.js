/**
 * GrapesJS Widget Handler
 * Follows Elementor's best practices for widget JavaScript
 */

class GrapesJSWidgetHandler extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                container: '.grapesjs-editor-container',
                saveButton: '.grapesjs-btn-save',
                wrapper: '.grapesjs-widget-wrapper'
            }
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        return {
            $container: this.$element.find(selectors.container),
            $saveButton: this.$element.find(selectors.saveButton),
            $wrapper: this.$element.find(selectors.wrapper)
        };
    }

    bindEvents() {
        // Only initialize in editor mode
        if (elementorFrontend.isEditMode()) {
            // Delay initialization to ensure DOM is ready
            setTimeout(() => {
                this.initializeGrapesJS();
            }, 100);
        }
    }

    initializeGrapesJS() {
        const $container = this.elements.$container;
        const containerId = $container.attr('id');
        
        if (!containerId || $container.data('grapesjs-initialized')) {
            return;
        }

        // Ensure GrapesJS is loaded
        if (typeof grapesjs === 'undefined') {
            this.loadGrapesJSAssets().then(() => {
                this.createEditor();
            });
        } else {
            this.createEditor();
        }
    }

    loadGrapesJSAssets() {
        return new Promise((resolve) => {
            // Load CSS
            if (!document.querySelector('link[href*="grapesjs"]')) {
                const css = document.createElement('link');
                css.rel = 'stylesheet';
                css.href = 'https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.7/css/grapes.min.css';
                document.head.appendChild(css);

                const presetCss = document.createElement('link');
                presetCss.rel = 'stylesheet';
                presetCss.href = 'https://cdnjs.cloudflare.com/ajax/libs/grapesjs-preset-webpage/1.0.2/grapesjs-preset-webpage.min.css';
                document.head.appendChild(presetCss);

                const fontAwesome = document.createElement('link');
                fontAwesome.rel = 'stylesheet';
                fontAwesome.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css';
                document.head.appendChild(fontAwesome);
            }

            // Load JS
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.7/grapes.min.js';
            script.onload = () => {
                const presetScript = document.createElement('script');
                presetScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/grapesjs-preset-webpage/1.0.2/grapesjs-preset-webpage.min.js';
                presetScript.onload = () => {
                    resolve();
                };
                document.head.appendChild(presetScript);
            };
            document.head.appendChild(script);
        });
    }

    createEditor() {
        const $container = this.elements.$container;
        const containerId = $container.attr('id');
        const initialContent = $container.data('initial-content') || '<div>Start editing...</div>';
        const initialCss = $container.data('initial-css') || '';
        const uniqueId = this.elements.$wrapper.data('unique-id');

        // Mark as initialized
        $container.data('grapesjs-initialized', true);

        // Initialize GrapesJS
        const editor = grapesjs.init({
            container: '#' + containerId,
            height: '100%',
            width: 'auto',
            storageManager: false,
            plugins: ['gjs-preset-webpage'],
            pluginsOpts: {
                'gjs-preset-webpage': {
                    blocksBasicOpts: {
                        blocks: ['column1', 'column2', 'column3', 'column3-7', 'text', 'link', 'image', 'video'],
                        flexGrid: true,
                    },
                    navbarOpts: false,
                    countdownOpts: false,
                    formsOpts: false,
                }
            },
            deviceManager: {
                devices: [{
                    name: 'Desktop',
                    width: ''
                }, {
                    name: 'Tablet',
                    width: '768px',
                    widthMedia: '992px'
                }, {
                    name: 'Mobile',
                    width: '320px',
                    widthMedia: '575px'
                }]
            },
            canvas: {
                styles: [initialCss],
            },
            components: initialContent,
        });

        // Store editor instance
        this.$element.data('grapesjs-editor', editor);

        // Add device buttons
        editor.Panels.addButton('options', [{
            id: 'device-desktop',
            command: 'set-device-desktop',
            className: 'fa fa-desktop',
            attributes: {title: 'Desktop'}
        }, {
            id: 'device-tablet',
            command: 'set-device-tablet',
            className: 'fa fa-tablet',
            attributes: {title: 'Tablet'}
        }, {
            id: 'device-mobile',
            command: 'set-device-mobile',
            className: 'fa fa-mobile',
            attributes: {title: 'Mobile'}
        }]);

        // Add device commands
        editor.Commands.add('set-device-desktop', {
            run: (editor) => editor.setDevice('Desktop')
        });
        editor.Commands.add('set-device-tablet', {
            run: (editor) => editor.setDevice('Tablet')
        });
        editor.Commands.add('set-device-mobile', {
            run: (editor) => editor.setDevice('Mobile')
        });

        // Bind save button
        this.elements.$saveButton.on('click', (e) => {
            e.preventDefault();
            this.saveContent(editor, uniqueId);
        });

        // Listen for Elementor panel changes
        if (window.elementor) {
            elementor.channels.editor.on('change', (e) => {
                if (e && e.elementSettingsModel && e.elementSettingsModel.id === this.$element.data('id')) {
                    // Re-initialize if settings changed
                    setTimeout(() => {
                        if (editor && typeof editor.destroy === 'function') {
                            editor.destroy();
                        }
                        $container.data('grapesjs-initialized', false);
                        this.initializeGrapesJS();
                    }, 100);
                }
            });
        }
    }

    saveContent(editor, uniqueId) {
        const $saveBtn = this.elements.$saveButton;
        
        // Show loading state
        $saveBtn.html('<span class="dashicons dashicons-update spin"></span> Saving...');
        $saveBtn.prop('disabled', true);

        // Prepare data
        const data = {
            action: 'grapesjs_save_content',
            nonce: grapesjs_ajax.nonce,
            widget_id: uniqueId,
            html: editor.getHtml(),
            css: editor.getCss(),
            components: JSON.stringify(editor.getComponents()),
            styles: JSON.stringify(editor.getStyle())
        };

        // Send AJAX request
        jQuery.post(grapesjs_ajax.ajax_url, data)
            .done((response) => {
                if (response.success) {
                    $saveBtn.html('<span class="dashicons dashicons-yes"></span> Saved!');
                    setTimeout(() => {
                        $saveBtn.html('<span class="dashicons dashicons-saved"></span> Save Content');
                        $saveBtn.prop('disabled', false);
                    }, 2000);
                } else {
                    this.showError($saveBtn);
                }
            })
            .fail(() => {
                this.showError($saveBtn);
            });
    }

    showError($saveBtn) {
        $saveBtn.html('<span class="dashicons dashicons-no"></span> Error!');
        setTimeout(() => {
            $saveBtn.html('<span class="dashicons dashicons-saved"></span> Save Content');
            $saveBtn.prop('disabled', false);
        }, 2000);
    }

    onDestroy() {
        const editor = this.$element.data('grapesjs-editor');
        if (editor && typeof editor.destroy === 'function') {
            editor.destroy();
        }
    }
}

// Register the handler when Elementor frontend is ready
jQuery(window).on('elementor/frontend/init', () => {
    const addHandler = ($element) => {
        elementorFrontend.elementsHandler.addHandler(GrapesJSWidgetHandler, {
            $element,
        });
    };

    // Register for both editor and frontend
    elementorFrontend.hooks.addAction('frontend/element_ready/grapesjs_html_editor.default', addHandler);
});
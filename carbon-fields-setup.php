<?php
/**
 * Carbon Fields Setup for Штори ПроФен
 * Free alternative to ACF Pro with enhanced functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load Carbon Fields
add_action('after_setup_theme', function() {
    // Check if Carbon Fields plugin is installed
    if (!class_exists('Carbon_Fields\\Container\\Container')) {
        // Auto-install Carbon Fields (or include instructions)
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>Please install and activate <a href="https://wordpress.org/plugins/carbon-fields/">Carbon Fields</a> plugin.</p></div>';
        });
        return;
    }
    
    // Initialize Carbon Fields containers
    add_action('carbon_fields_register_fields', function() {
        
        // Visualization Fields Container
        \Carbon_Fields\Container\Container::make('post_meta', 'Visualization Details')
            ->where('post_type', '=', 'visualization')
            ->add_fields([
                \Carbon_Fields\Field\Field::make('text', 'photo_id', 'Photo ID')
                    ->set_help_text('Unique identifier from source'),
                
                \Carbon_Fields\Field\Field::make('text', 'photo_url', 'Photo URL')
                    ->set_help_text('Direct URL to the photo'),
                
                \Carbon_Fields\Field\Field::make('text', 'download_url', 'Download URL')
                    ->set_help_text('High-resolution download URL'),
                
                \Carbon_Fields\Field\Field::make('select', 'source', 'Photo Source')
                    ->add_options([
                        'unsplash' => 'Unsplash',
                        'pexels' => 'Pexels',
                        'pixabay' => 'Pixabay',
                        'ai_generated' => 'AI Generated'
                    ]),
                
                \Carbon_Fields\Field\Field::make('text', 'photographer', 'Photographer')
                    ->set_help_text('Name of the photographer'),
                
                \Carbon_Fields\Field\Field::make('text', 'photographer_url', 'Photographer URL')
                    ->set_help_text('Link to photographer profile'),
                
                \Carbon_Fields\Field\Field::make('text', 'width', 'Image Width')
                    ->set_attribute('type', 'number'),
                
                \Carbon_Fields\Field\Field::make('text', 'height', 'Image Height')
                    ->set_attribute('type', 'number'),
                
                \Carbon_Fields\Field\Field::make('checkbox', 'ar_ready', 'AR Ready')
                    ->set_help_text('Check if this visualization supports AR'),
                
                \Carbon_Fields\Field\Field::make('complex', 'color_palette', 'Color Palette')
                    ->add_fields([
                        \Carbon_Fields\Field\Field::make('color', 'color', 'Color'),
                        \Carbon_Fields\Field\Field::make('text', 'name', 'Color Name'),
                        \Carbon_Fields\Field\Field::make('text', 'percentage', 'Percentage (%)')
                            ->set_attribute('type', 'number')
                            ->set_help_text('Percentage of this color in the image')
                    ])
                    ->set_header_template('
                        <% if (color) { %>
                            <span style="display: inline-block; width: 20px; height: 20px; background-color: <%= color %>; border-radius: 3px; margin-right: 10px;"></span>
                        <% } %>
                        <%= name || "Untitled Color" %>
                    ')
                    ->set_collapsed(true),
                
                \Carbon_Fields\Field\Field::make('text', 'dominant_color', 'Dominant Color')
                    ->set_help_text('Main color of the visualization'),
                
                \Carbon_Fields\Field\Field::make('textarea', 'style_notes', 'Style Notes')
                    ->set_help_text('Notes about the interior style and design elements'),
                
                \Carbon_Fields\Field\Field::make('complex', 'product_recommendations', 'Recommended Products')
                    ->add_fields([
                        \Carbon_Fields\Field\Field::make('text', 'product_id', 'Product ID'),
                        \Carbon_Fields\Field\Field::make('text', 'product_name', 'Product Name'),
                        \Carbon_Fields\Field\Field::make('text', 'reason', 'Recommendation Reason')
                    ])
                    ->set_header_template('<%= product_name || "Untitled Product" %>')
                    ->set_collapsed(true)
            ]);
        
        // Partner Fields Container
        \Carbon_Fields\Container\Container::make('post_meta', 'Partner Information')
            ->where('post_type', '=', 'partner')
            ->add_fields([
                \Carbon_Fields\Field\Field::make('text', 'address', 'Address')
                    ->set_help_text('Physical address of the partner'),
                
                \Carbon_Fields\Field\Field::make('text', 'city', 'City')
                    ->set_help_text('City where the partner is located'),
                
                \Carbon_Fields\Field\Field::make('text', 'phone', 'Phone Number')
                    ->set_help_text('Contact phone number'),
                
                \Carbon_Fields\Field\Field::make('text', 'email', 'Email')
                    ->set_help_text('Contact email address'),
                
                \Carbon_Fields\Field\Field::make('text', 'website', 'Website')
                    ->set_help_text('Partner website URL'),
                
                \Carbon_Fields\Field\Field::make('text', 'rating', 'Rating')
                    ->set_attribute('type', 'number')
                    ->set_attribute('min', '0')
                    ->set_attribute('max', '5')
                    ->set_attribute('step', '0.1')
                    ->set_help_text('Partner rating (0-5)'),
                
                \Carbon_Fields\Field\Field::make('complex', 'services', 'Services Offered')
                    ->add_fields([
                        \Carbon_Fields\Field\Field::make('text', 'service_name', 'Service Name'),
                        \Carbon_Fields\Field\Field::make('text', 'price', 'Price')
                            ->set_help_text('Price for the service'),
                        \Carbon_Fields\Field\Field::make('checkbox', 'featured', 'Featured Service')
                    ])
                    ->set_header_template('<%= service_name || "Untitled Service" %>')
                    ->set_collapsed(true),
                
                \Carbon_Fields\Field\Field::make('text', 'latitude', 'Latitude')
                    ->set_attribute('type', 'number')
                    ->set_attribute('step', 'any')
                    ->set_help_text('Latitude for map display'),
                
                \Carbon_Fields\Field\Field::make('text', 'longitude', 'Longitude')
                    ->set_attribute('type', 'number')
                    ->set_attribute('step', 'any')
                    ->set_help_text('Longitude for map display'),
                
                \Carbon_Fields\Field\Field::make('complex', 'working_hours', 'Working Hours')
                    ->add_fields([
                        \Carbon_Fields\Field\Field::make('select', 'day', 'Day')
                            ->add_options([
                                'monday' => 'Monday',
                                'tuesday' => 'Tuesday',
                                'wednesday' => 'Wednesday',
                                'thursday' => 'Thursday',
                                'friday' => 'Friday',
                                'saturday' => 'Saturday',
                                'sunday' => 'Sunday'
                            ]),
                        \Carbon_Fields\Field\Field::make('text', 'open_time', 'Open Time'),
                        \Carbon_Fields\Field\Field::make('text', 'close_time', 'Close Time'),
                        \Carbon_Fields\Field\Field::make('checkbox', 'closed', 'Closed')
                    ])
                    ->set_header_template('<%= day.charAt(0).toUpperCase() + day.slice(1) %>')
                    ->set_collapsed(true),
                
                \Carbon_Fields\Field\Field::make('textarea', 'specializations', 'Specializations')
                    ->set_help_text('Areas of specialization (one per line)'),
                
                \Carbon_Fields\Field\Field::make('complex', 'certifications', 'Certifications')
                    ->add_fields([
                        \Carbon_Fields\Field\Field::make('text', 'cert_name', 'Certification Name'),
                        \Carbon_Fields\Field\Field::make('text', 'cert_number', 'Certification Number'),
                        \Carbon_Fields\Field\Field::make('date', 'expiry_date', 'Expiry Date')
                    ])
                    ->set_header_template('<%= cert_name || "Untitled Certification" %>')
                    ->set_collapsed(true)
            ]);
        
        // Configuration Fields Container
        \Carbon_Fields\Container\Container::make('post_meta', 'Configuration Details')
            ->where('post_type', '=', 'configuration')
            ->add_fields([
                \Carbon_Fields\Field\Field::make('text', 'total_price', 'Total Price')
                    ->set_attribute('type', 'number')
                    ->set_attribute('step', '0.01')
                    ->set_help_text('Total configuration price in UAH'),
                
                \Carbon_Fields\Field\Field::make('text', 'user_ip', 'User IP')
                    ->set_help_text('IP address of the user who created this configuration'),
                
                \Carbon_Fields\Field\Field::make('text', 'share_token', 'Share Token')
                    ->set_help_text('Unique token for sharing configuration'),
                
                \Carbon_Fields\Field\Field::make('complex', 'selections', 'Configuration Selections')
                    ->add_fields([
                        \Carbon_Fields\Field\Field::make('text', 'step', 'Step'),
                        \Carbon_Fields\Field\Field::make('text', 'selection', 'Selection'),
                        \Carbon_Fields\Field\Field::make('text', 'price', 'Price')
                            ->set_attribute('type', 'number')
                            ->set_attribute('step', '0.01')
                    ])
                    ->set_header_template('Step <%= step %>: <%= selection %>')
                    ->set_collapsed(true),
                
                \Carbon_Fields\Field\Field::make('complex', 'installation_details', 'Installation Details')
                    ->add_fields([
                        \Carbon_Fields\Field\Field::make('text', 'complexity', 'Complexity')
                            ->add_options([
                                'simple' => 'Simple',
                                'medium' => 'Medium',
                                'complex' => 'Complex'
                            ]),
                        \Carbon_Fields\Field\Field::make('text', 'base_price', 'Base Price')
                            ->set_attribute('type', 'number')
                            ->set_attribute('step', '0.01'),
                        \Carbon_Fields\Field\Field::make('text', 'location_multiplier', 'Location Multiplier')
                            ->set_attribute('type', 'number')
                            ->set_attribute('step', '0.01'),
                        \Carbon_Fields\Field\Field::make('text', 'final_price', 'Final Price')
                            ->set_attribute('type', 'number')
                            ->set_attribute('step', '0.01')
                    ])
                    ->set_header_template('Installation: <%= complexity %>')
                    ->set_collapsed(true),
                
                \Carbon_Fields\Field\Field::make('textarea', 'customer_notes', 'Customer Notes')
                    ->set_help_text('Additional notes from customer'),
                
                \Carbon_Fields\Field\Field::make('select', 'status', 'Status')
                    ->add_options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'in_production' => 'In Production',
                        'shipped' => 'Shipped',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled'
                    ])
                    ->set_default_value('pending')
            ]);
        
        // Theme Options Container
        \Carbon_Fields\Container\Container::make('theme_options', 'Штори ПроФен Settings')
            ->add_fields([
                \Carbon_Fields\Field\Field::make('header', 'api_settings', 'API Settings')
                    ->set_help_text('Configure API keys for external services'),
                
                \Carbon_Fields\Field\Field::make('text', 'unsplash_api_key', 'Unsplash API Key')
                    ->set_help_text('Access key for Unsplash API'),
                
                \Carbon_Fields\Field\Field::make('text', 'pexels_api_key', 'Pexels API Key')
                    ->set_help_text('API key for Pexels'),
                
                \Carbon_Fields\Field\Field::make('text', 'pixabay_api_key', 'Pixabay API Key')
                    ->set_help_text('API key for Pixabay'),
                
                \Carbon_Fields\Field\Field::make('header', 'ar_settings', 'AR Settings')
                    ->set_help_text('Configure AR functionality'),
                
                \Carbon_Fields\Field\Field::make('checkbox', 'enable_ar', 'Enable AR')
                    ->set_default_value(true)
                    ->set_help_text('Enable AR functionality on the site'),
                
                \Carbon_Fields\Field\Field::make('text', 'ar_model_path', 'AR Model Path')
                    ->set_help_text('Path to AR model files'),
                
                \Carbon_Fields\Field\Field::make('header', 'email_settings', 'Email Settings')
                    ->set_help_text('Configure email functionality'),
                
                \Carbon_Fields\Field\Field::make('text', 'admin_email', 'Admin Email')
                    ->set_help_text('Email for administrative notifications'),
                
                \Carbon_Fields\Field\Field::make('textarea', 'welcome_email_template', 'Welcome Email Template')
                    ->set_help_text('Template for welcome emails'),
                
                \Carbon_Fields\Field\Field::make('textarea', 'ugc_confirmation_template', 'UGC Confirmation Template')
                    ->set_help_text('Template for UGC confirmation emails'),
                
                \Carbon_Fields\Field\Field::make('header', 'pricing_settings', 'Pricing Settings')
                    ->set_help_text('Configure pricing and discounts'),
                
                \Carbon_Fields\Field\Field::make('text', 'base_installation_price', 'Base Installation Price')
                    ->set_attribute('type', 'number')
                    ->set_attribute('step', '0.01')
                    ->set_default_value('1500')
                    ->set_help_text('Base price for installation service'),
                
                \Carbon_Fields\Field\Field::make('text', 'complex_installation_price', 'Complex Installation Price')
                    ->set_attribute('type', 'number')
                    ->set_attribute('step', '0.01')
                    ->set_default_value('2500')
                    ->set_help_text('Price for complex installations'),
                
                \Carbon_Fields\Field\Field::make('text', 'welcome_discount', 'Welcome Discount')
                    ->set_attribute('type', 'number')
                    ->set_attribute('step', '0.01')
                    ->set_default_value('10')
                    ->set_help_text('Discount percentage for new subscribers'),
                
                \Carbon_Fields\Field\Field::make('text', 'ugc_discount', 'UGC Discount')
                    ->set_attribute('type', 'number')
                    ->set_attribute('step', '0.01')
                    ->set_default_value('15')
                    ->set_help_text('Discount percentage for UGC submissions'),
                
                \Carbon_Fields\Field\Field::make('header', 'analytics_settings', 'Analytics Settings')
                    ->set_help_text('Configure analytics and tracking'),
                
                \Carbon_Fields\Field\Field::make('text', 'google_analytics_id', 'Google Analytics ID')
                    ->set_help_text('Google Analytics tracking ID'),
                
                \Carbon_Fields\Field\Field::make('checkbox', 'enable_tracking', 'Enable Tracking')
                    ->set_default_value(true)
                    ->set_help_text('Enable user tracking and analytics'),
                
                \Carbon_Fields\Field\Field::make('header', 'security_settings', 'Security Settings')
                    ->set_help_text('Configure security options'),
                
                \Carbon_Fields\Field\Field::make('checkbox', 'enable_backup', 'Enable Automatic Backups')
                    ->set_default_value(true)
                    ->set_help_text('Enable daily automatic backups'),
                
                \Carbon_Fields\Field\Field::make('text', 'backup_retention_days', 'Backup Retention Days')
                    ->set_attribute('type', 'number')
                    ->set_default_value('30')
                    ->set_help_text('Number of days to retain backups'),
                
                \Carbon_Fields\Field\Field::make('checkbox', 'enable_maintenance_mode', 'Enable Maintenance Mode')
                    ->set_default_value(false)
                    ->set_help_text('Put site in maintenance mode'),
                
                \Carbon_Fields\Field\Field::make('textarea', 'maintenance_message', 'Maintenance Message')
                    ->set_help_text('Message to display during maintenance')
            ]);
    });
});

// Helper functions for Carbon Fields data
function shoriprofen_get_visualization_meta($post_id, $field_name) {
    return carbon_get_post_meta($post_id, $field_name);
}

function shoriprofen_get_partner_meta($post_id, $field_name) {
    return carbon_get_post_meta($post_id, $field_name);
}

function shoriprofen_get_configuration_meta($post_id, $field_name) {
    return carbon_get_post_meta($post_id, $field_name);
}

function shoriprofen_get_theme_option($option_name) {
    return carbon_get_theme_option($option_name);
}

// Custom functions for complex fields
function shoriprofen_get_visualization_colors($post_id) {
    $colors = carbon_get_post_meta($post_id, 'color_palette');
    $result = [];
    
    if (is_array($colors)) {
        foreach ($colors as $color) {
            $result[] = [
                'color' => $color['color'] ?? '',
                'name' => $color['name'] ?? '',
                'percentage' => $color['percentage'] ?? 0
            ];
        }
    }
    
    return $result;
}

function shoriprofen_get_partner_services($post_id) {
    $services = carbon_get_post_meta($post_id, 'services');
    $result = [];
    
    if (is_array($services)) {
        foreach ($services as $service) {
            $result[] = [
                'service_name' => $service['service_name'] ?? '',
                'price' => $service['price'] ?? '',
                'featured' => $service['featured'] ?? false
            ];
        }
    }
    
    return $result;
}

function shoriprofen_get_configuration_selections($post_id) {
    $selections = carbon_get_post_meta($post_id, 'selections');
    $result = [];
    
    if (is_array($selections)) {
        foreach ($selections as $selection) {
            $result[] = [
                'step' => $selection['step'] ?? '',
                'selection' => $selection['selection'] ?? '',
                'price' => $selection['price'] ?? 0
            ];
        }
    }
    
    return $result;
}

// Auto-install Carbon Fields plugin
add_action('admin_init', function() {
    if (!class_exists('Carbon_Fields\\Container\\Container') && current_user_can('install_plugins')) {
        // Include plugin installer
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        
        // Install Carbon Fields
        $api = plugins_api('plugin_information', [
            'slug' => 'carbon-fields',
            'fields' => ['sections' => false]
        ]);
        
        if (!is_wp_error($api)) {
            $upgrader = new Plugin_Upgrader(new Plugin_Installer_Skin());
            $upgrader->install($api->download_link);
            
            // Activate the plugin
            $result = activate_plugin('carbon-fields/carbon-fields-plugin.php');
            
            if (!is_wp_error($result)) {
                // Redirect to refresh the page
                wp_redirect(admin_url());
                exit;
            }
        }
    }
});

// Add custom CSS for Carbon Fields
add_action('admin_head', function() {
    ?>
    <style>
    .carbon-field .complex .complex-item .complex-item-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 10px 15px;
        font-weight: 500;
    }
    
    .carbon-field .complex .complex-item .complex-item-header .complex-remove {
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
    }
    
    .carbon-field .complex .complex-item .complex-item-header .complex-remove:hover {
        background: #c82333;
    }
    
    .carbon-field .color-picker {
        width: 50px;
        height: 50px;
        border: 2px solid #ddd;
        border-radius: 5px;
        cursor: pointer;
    }
    
    .carbon-field .complex .complex-item .complex-item-header .color-preview {
        display: inline-block;
        width: 20px;
        height: 20px;
        border-radius: 3px;
        margin-right: 10px;
        vertical-align: middle;
    }
    </style>
    <?php
});

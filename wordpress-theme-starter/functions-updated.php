<?php
/**
 * Штори ПроФен Theme Functions
 * Core functionality for the WordPress theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include installer
require_once get_template_directory() . '/install.php';
require_once get_template_directory() . '/ajax-handlers.php';

// Theme setup
function shoriprofen_setup() {
    // Add default posts and comments RSS feed links to head.
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title.
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages.
    add_theme_support('post-thumbnails');

    // Register navigation menus
    register_nav_menus([
        'primary' => __('Primary Menu', 'shoriprofen'),
        'footer' => __('Footer Menu', 'shoriprofen'),
        'mobile' => __('Mobile Menu', 'shoriprofen'),
    ]);

    // Switch default core markup for search form, comment form, and comments to output valid HTML5.
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ]);

    // Add theme support for selective refresh for widgets.
    add_theme_support('customize-selective-refresh-widgets');

    // Add support for custom logo
    add_theme_support('custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    // Add support for WooCommerce
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'shoriprofen_setup');

// Enqueue scripts and styles
function shoriprofen_scripts() {
    // Main stylesheet
    wp_enqueue_style('shoriprofen-style', get_stylesheet_uri());
    
    // Google Fonts
    wp_enqueue_style('shoriprofen-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap', [], null);
    
    // Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', [], '6.0.0');
    
    // Main JavaScript
    wp_enqueue_script('shoriprofen-script', get_template_directory_uri() . '/js/main.js', ['jquery'], '1.0.0', true);
    
    // AR functionality
    wp_enqueue_script('shoriprofen-ar', get_template_directory_uri() . '/js/ar-visualizer.js', ['jquery'], '1.0.0', true);
    
    // Configurator
    wp_enqueue_script('shoriprofen-configurator', get_template_directory_uri() . '/js/configurator.js', ['jquery'], '1.0.0', true);
    
    // Localize script
    wp_localize_script('shoriprofen-script', 'shoriprofen_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('shoriprofen_nonce'),
        'site_url' => home_url(),
    ]);
}
add_action('wp_enqueue_scripts', 'shoriprofen_scripts');

// Register widget areas
function shoriprofen_widgets_init() {
    register_sidebar([
        'name'          => __('Primary Sidebar', 'shoriprofen'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here to appear in your sidebar.', 'shoriprofen'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Footer Widgets', 'shoriprofen'),
        'id'            => 'footer-widgets',
        'description'   => __('Add widgets here to appear in your footer.', 'shoriprofen'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ]);
}
add_action('widgets_init', 'shoriprofen_widgets_init');

// Custom excerpt length
function shoriprofen_excerpt_length($length) {
    return 25;
}
add_filter('excerpt_length', 'shoriprofen_excerpt_length');

// Custom excerpt more
function shoriprofen_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'shoriprofen_excerpt_more');

// Breadcrumb function
function shoriprofen_breadcrumbs() {
    if (function_exists('yoast_breadcrumb')) {
        yoast_breadcrumb('<p id="breadcrumbs">', '</p>');
    } else {
        echo '<nav class="breadcrumb">';
        echo '<a href="' . home_url() . '">Главная</a>';
        
        if (is_category() || is_single()) {
            echo ' / ';
            the_category(' / ');
            if (is_single()) {
                echo ' / ';
                the_title();
            }
        } elseif (is_page()) {
            echo ' / ';
            the_title();
        }
        
        echo '</nav>';
    }
}

// Get related visualizations
function shoriprofen_get_related_visualizations($post_id, $limit = 4) {
    $room_types = wp_get_post_terms($post_id, 'room_type');
    $styles = wp_get_post_terms($post_id, 'interior_style');
    
    $args = [
        'post_type' => 'visualization',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'post__not_in' => [$post_id],
        'orderby' => 'rand',
    ];
    
    $tax_query = [];
    
    if (!empty($room_types)) {
        $tax_query[] = [
            'taxonomy' => 'room_type',
            'field' => 'term_id',
            'terms' => wp_list_pluck($room_types, 'term_id'),
        ];
    }
    
    if (!empty($styles)) {
        $tax_query[] = [
            'taxonomy' => 'interior_style',
            'field' => 'term_id',
            'terms' => wp_list_pluck($styles, 'term_id'),
        ];
    }
    
    if (!empty($tax_query)) {
        $tax_query['relation'] = 'OR';
        $args['tax_query'] = $tax_query;
    }
    
    return new WP_Query($args);
}

// Get product price with formatting
function shoriprofen_format_price($price) {
    return number_format($price, 0, '.', ' ') . ' грн';
}

// Get installation price based on complexity
function shoriprofen_get_installation_price($complexity = 'simple') {
    $prices = [
        'simple' => 1500,
        'medium' => 2000,
        'complex' => 2500,
    ];
    
    return $prices[$complexity] ?? $prices['simple'];
}

// Check if AR is supported
function shoriprofen_is_ar_supported() {
    return isset($_SERVER['HTTP_USER_AGENT']) && (
        strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false ||
        strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') !== false ||
        strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
    );
}

// Get user location for pricing
function shoriprofen_get_user_location() {
    // In real implementation, use geolocation API
    return 'kiev';
}

// Get location multiplier for pricing
function shoriprofen_get_location_multiplier($location) {
    $multipliers = [
        'kiev' => 1.0,
        'kharkiv' => 0.9,
        'odesa' => 0.95,
        'dnipro' => 0.9,
        'lviv' => 0.85,
    ];
    
    return $multipliers[$location] ?? 1.0;
}

// Send email notification
function shoriprofen_send_notification($to, $subject, $message, $headers = []) {
    $default_headers = ['Content-Type: text/html; charset=UTF-8'];
    $headers = array_merge($default_headers, $headers);
    
    return wp_mail($to, $subject, $message, $headers);
}

// Create share link for configuration
function shoriprofen_create_share_link($config_id) {
    $token = wp_generate_password(12, false);
    update_post_meta($config_id, 'share_token', $token);
    
    return home_url('/configurator/?share=' . $token);
}

// Get configuration from share token
function shoriprofen_get_configuration_by_token($token) {
    $args = [
        'post_type' => 'configuration',
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => 'share_token',
                'value' => $token,
                'compare' => '=',
            ],
        ],
        'posts_per_page' => 1,
    ];
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        return $query->posts[0];
    }
    
    return null;
}

// Custom login redirect
function shoriprofen_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('administrator', $user->roles)) {
            return admin_url();
        } else {
            return home_url('/my-account/');
        }
    }
    
    return $redirect_to;
}
add_filter('login_redirect', 'shoriprofen_login_redirect', 10, 3);

// Remove WordPress version from head
function shoriprofen_remove_version() {
    return '';
}
add_filter('the_generator', 'shoriprofen_remove_version');

// Add custom body classes
function shoriprofen_body_classes($classes) {
    // Add AR support class
    if (shoriprofen_is_ar_supported()) {
        $classes[] = 'ar-supported';
    } else {
        $classes[] = 'ar-not-supported';
    }
    
    // Add page slug
    if (is_page()) {
        $classes[] = 'page-' . sanitize_title(get_the_title());
    }
    
    return $classes;
}
add_filter('body_class', 'shoriprofen_body_classes');

// Custom admin footer
function shoriprofen_admin_footer() {
    echo 'Разработано для <a href="https://shoriprofen.ua">Штори ПроФен</a>';
}
add_filter('admin_footer_text', 'shoriprofen_admin_footer');

// Add custom image sizes
add_image_size('visualization-thumb', 400, 300, true);
add_image_size('visualization-large', 800, 600, true);
add_image_size('product-thumb', 300, 300, true);
add_image_size('product-large', 600, 600, true);

// Security: Hide admin bar for non-admins
function shoriprofen_hide_admin_bar() {
    if (!current_user_can('administrator')) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'shoriprofen_hide_admin_bar');

// Security: Limit login attempts
function shoriprofen_limit_login_attempts($username) {
    // Implement login attempt limiting
    return $username;
}
add_filter('authenticate', 'shoriprofen_limit_login_attempts', 1, 2);

// Performance: Enable lazy loading for images
function shoriprofen_lazy_loading_images($content) {
    if (!is_admin()) {
        $content = preg_replace('/<img([^>]+)src=([\'"])([^\'">]+)([\'"])([^>]*)>/i', '<img$1data-src=$2$3$4 src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" loading="lazy"$5>', $content);
    }
    return $content;
}
add_filter('the_content', 'shoriprofen_lazy_loading_images');

// Performance: Optimize database queries
function shoriprofen_optimize_queries($query) {
    if (!is_admin() && $query->is_main_query()) {
        // Optimize main queries
    }
}
add_action('pre_get_posts', 'shoriprofen_optimize_queries');

// SEO: Add meta descriptions
function shoriprofen_add_meta_description() {
    if (is_single() || is_page()) {
        $description = get_post_meta(get_the_ID(), '_yoast_wpseo_metadesc', true);
        if (empty($description)) {
            $description = get_the_excerpt();
        }
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    }
}
add_action('wp_head', 'shoriprofen_add_meta_description');

// SEO: Add Open Graph tags
function shoriprofen_add_og_tags() {
    if (is_single() || is_page()) {
        echo '<meta property="og:title" content="' . get_the_title() . '">' . "\n";
        echo '<meta property="og:type" content="article">' . "\n";
        echo '<meta property="og:url" content="' . get_permalink() . '">' . "\n";
        
        if (has_post_thumbnail()) {
            echo '<meta property="og:image" content="' . get_the_post_thumbnail_url(null, 'large') . '">' . "\n";
        }
        
        echo '<meta property="og:site_name" content="' . get_bloginfo('name') . '">' . "\n";
    }
}
add_action('wp_head', 'shoriprofen_add_og_tags');

// Analytics: Add Google Analytics
function shoriprofen_add_google_analytics() {
    // Add Google Analytics code here
    ?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'GA_MEASUREMENT_ID');
    </script>
    <?php
}
add_action('wp_head', 'shoriprofen_add_google_analytics');

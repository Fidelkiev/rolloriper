<?php
/**
 * Functions file for Штори ПроФен theme
 *
 * @package WordPress
 * @subpackage ShoriProfen
 */

// Theme setup
function shoriprofen_setup() {
    // Add default posts and comments RSS feed links to head.
    add_theme_support('automatic-feed-links');
    
    // Title tag support
    add_theme_support('title-tag');
    
    // Post thumbnails
    add_theme_support('post-thumbnails');
    
    // HTML5 semantic markup
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ]);
    
    // WooCommerce support
    add_theme_support('woocommerce');
    
    // Custom logo
    add_theme_support('custom-logo', [
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    
    // Register menus
    register_nav_menus([
        'primary' => 'Primary Menu',
        'footer'  => 'Footer Menu',
    ]);
}
add_action('after_setup_theme', 'shoriprofen_setup');

// Enqueue scripts and styles
function shoriprofen_scripts() {
    // Main stylesheet
    wp_enqueue_style('shoriprofen-style', get_stylesheet_uri());
    
    // Google Fonts
    wp_enqueue_style('shoriprofen-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    // Main script
    wp_enqueue_script('shoriprofen-script', 
        get_template_directory_uri() . '/js/script.js', 
        ['jquery'], 
        '1.0.0', 
        true
    );
    
    // Configurator script
    wp_enqueue_script('shoriprofen-configurator', 
        get_template_directory_uri() . '/js/configurator.js', 
        ['jquery'], 
        '1.0.0', 
        true
    );
    
    // Partner map script
    wp_enqueue_script('shoriprofen-partners', 
        get_template_directory_uri() . '/js/partners.js', 
        ['jquery'], 
        '1.0.0', 
        true
    );
    
    // Localize script for AJAX
    wp_localize_script('shoriprofen-script', 'shoriprofen_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('shoriprofen_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'shoriprofen_scripts');

// Register custom post types
function shoriprofen_post_types() {
    // Visualizations
    register_post_type('visualization', [
        'labels' => [
            'name'          => 'Визуализации',
            'singular_name' => 'Визуализация',
            'add_new'       => 'Добавить визуализацию',
            'add_new_item'  => 'Добавить новую визуализацию',
            'edit_item'     => 'Редактировать визуализацию',
            'new_item'      => 'Новая визуализация',
            'view_item'     => 'Просмотреть визуализацию',
            'search_items'  => 'Найти визуализации',
            'not_found'     => 'Визуализации не найдены',
            'all_items'     => 'Все визуализации',
        ],
        'public'      => true,
        'has_archive' => true,
        'supports'    => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
        'menu_icon'   => 'dashicons-images-alt2',
        'rewrite'     => ['slug' => 'visualizations'],
    ]);
    
    // Partners
    register_post_type('partner', [
        'labels' => [
            'name'          => 'Партнеры',
            'singular_name' => 'Партнер',
            'add_new'       => 'Добавить партнера',
            'add_new_item'  => 'Добавить нового партнера',
            'edit_item'     => 'Редактировать партнера',
            'new_item'      => 'Новый партнер',
            'view_item'     => 'Просмотреть партнера',
            'search_items'  => 'Найти партнеров',
            'not_found'     => 'Партнеры не найдены',
            'all_items'     => 'Все партнеры',
        ],
        'public'      => true,
        'has_archive' => true,
        'supports'    => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
        'menu_icon'   => 'dashicons-store',
        'rewrite'     => ['slug' => 'partners'],
    ]);
    
    // Configurations
    register_post_type('configuration', [
        'labels' => [
            'name'          => 'Конфигурации',
            'singular_name' => 'Конфигурация',
            'add_new'       => 'Добавить конфигурацию',
            'add_new_item'  => 'Добавить новую конфигурацию',
            'edit_item'     => 'Редактировать конфигурацию',
            'new_item'      => 'Новая конфигурация',
            'view_item'     => 'Просмотреть конфигурацию',
            'search_items'  => 'Найти конфигурации',
            'not_found'     => 'Конфигурации не найдены',
            'all_items'     => 'Все конфигурации',
        ],
        'public'       => false,
        'show_ui'      => true,
        'supports'     => ['title', 'custom-fields'],
        'menu_icon'    => 'dashicons-admin-generic',
        'rewrite'      => ['slug' => 'configurations'],
    ]);
}
add_action('init', 'shoriprofen_post_types');

// Register taxonomies
function shoriprofen_taxonomies() {
    // Room types for visualizations
    register_taxonomy('room_type', ['visualization'], [
        'labels' => [
            'name'          => 'Типы помещений',
            'singular_name' => 'Тип помещения',
            'search_items'  => 'Найти тип помещения',
            'all_items'     => 'Все типы помещений',
            'edit_item'     => 'Редактировать тип помещения',
            'update_item'   => 'Обновить тип помещения',
            'add_new_item'  => 'Добавить новый тип помещения',
            'new_item_name' => 'Новый тип помещения',
            'menu_name'     => 'Типы помещений',
        ],
        'hierarchical' => true,
        'public'      => true,
        'rewrite'     => ['slug' => 'room-type'],
    ]);
    
    // Interior styles
    register_taxonomy('interior_style', ['visualization'], [
        'labels' => [
            'name'          => 'Стили интерьера',
            'singular_name' => 'Стиль интерьера',
            'search_items'  => 'Найти стиль',
            'all_items'     => 'Все стили',
            'edit_item'     => 'Редактировать стиль',
            'update_item'   => 'Обновить стиль',
            'add_new_item'  => 'Добавить новый стиль',
            'new_item_name' => 'Новый стиль',
            'menu_name'     => 'Стили интерьера',
        ],
        'hierarchical' => true,
        'public'      => true,
        'rewrite'     => ['slug' => 'style'],
    ]);
}
add_action('init', 'shoriprofen_taxonomies');

// AJAX handlers
function shoriprofen_save_configuration() {
    check_ajax_referer('shoriprofen_nonce', 'nonce');
    
    $config_data = json_decode(stripslashes($_POST['config_data']), true);
    
    $post_id = wp_insert_post([
        'post_type'   => 'configuration',
        'post_title'  => 'Конфигурация от ' . date('d.m.Y H:i'),
        'post_status' => 'publish',
        'meta_input'  => [
            '_configuration_data' => serialize($config_data),
            '_total_price'        => $config_data['total_price'],
            '_user_email'         => $config_data['user_email'],
        ],
    ]);
    
    if ($post_id) {
        wp_send_json_success(['config_id' => $post_id]);
    } else {
        wp_send_json_error('Ошибка сохранения конфигурации');
    }
}
add_action('wp_ajax_save_configuration', 'shoriprofen_save_configuration');
add_action('wp_ajax_nopriv_save_configuration', 'shoriprofen_save_configuration');

// Get partners for map
function shoriprofen_get_partners() {
    check_ajax_referer('shoriprofen_nonce', 'nonce');
    
    $partners = new WP_Query([
        'post_type'      => 'partner',
        'posts_per_page' => -1,
    ]);
    
    $partners_data = [];
    
    if ($partners->have_posts()) {
        while ($partners->have_posts()) {
            $partners->the_post();
            
            $partners_data[] = [
                'id'      => get_the_ID(),
                'title'   => get_the_title(),
                'address' => get_post_meta(get_the_ID(), '_address', true),
                'lat'     => get_post_meta(get_the_ID(), '_latitude', true),
                'lng'     => get_post_meta(get_the_ID(), '_longitude', true),
                'rating'  => get_post_meta(get_the_ID(), '_rating', true),
                'phone'   => get_post_meta(get_the_ID(), '_phone', true),
                'email'   => get_post_meta(get_the_ID(), '_email', true),
            ];
        }
        wp_reset_postdata();
    }
    
    wp_send_json_success($partners_data);
}
add_action('wp_ajax_get_partners', 'shoriprofen_get_partners');
add_action('wp_ajax_nopriv_get_partners', 'shoriprofen_get_partners');

// Customizer options
function shoriprofen_customize_register($wp_customize) {
    // Logo upload
    $wp_customize->add_setting('logo_upload');
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'logo_upload', [
        'label'    => 'Логотип',
        'section'  => 'title_tagline',
        'settings' => 'logo_upload',
    ]));
    
    // Contact information
    $wp_customize->add_section('contact_info', [
        'title'    => 'Контактная информация',
        'priority' => 30,
    ]);
    
    $wp_customize->add_setting('phone');
    $wp_customize->add_control('phone', [
        'label'    => 'Телефон',
        'section'  => 'contact_info',
        'type'     => 'text',
    ]);
    
    $wp_customize->add_setting('email');
    $wp_customize->add_control('email', [
        'label'    => 'Email',
        'section'  => 'contact_info',
        'type'     => 'email',
    ]);
    
    $wp_customize->add_setting('address');
    $wp_customize->add_control('address', [
        'label'    => 'Адрес',
        'section'  => 'contact_info',
        'type'     => 'text',
    ]);
}
add_action('customize_register', 'shoriprofen_customize_register');

// Widget areas
function shoriprofen_widgets_init() {
    register_sidebar([
        'name'          => 'Footer Widget 1',
        'id'            => 'footer-1',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
    
    register_sidebar([
        'name'          => 'Footer Widget 2',
        'id'            => 'footer-2',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
    
    register_sidebar([
        'name'          => 'Footer Widget 3',
        'id'            => 'footer-3',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
}
add_action('widgets_init', 'shoriprofen_widgets_init');

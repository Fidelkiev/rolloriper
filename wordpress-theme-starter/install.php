<?php
/**
 * Installation script for Штори ПроФен theme
 * Sets up initial configuration and sample data
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ShoriProfenInstaller {
    
    public function __construct() {
        add_action('after_setup_theme', [$this, 'setup_theme']);
        add_action('init', [$this, 'register_post_types']);
        add_action('init', [$this, 'register_taxonomies']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_init', [$this, 'create_sample_data']);
    }
    
    /**
     * Theme setup
     */
    public function setup_theme() {
        // Add theme support
        add_theme_support('post-thumbnails');
        add_theme_support('title-tag');
        add_theme_support('html5', [
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
        ]);
        add_theme_support('custom-logo');
        add_theme_support('customize-selective-refresh-widgets');
        
        // Register menus
        register_nav_menus([
            'primary' => __('Primary Menu', 'shoriprofen'),
            'footer' => __('Footer Menu', 'shoriprofen'),
            'mobile' => __('Mobile Menu', 'shoriprofen'),
        ]);
        
        // Set content width
        $GLOBALS['content_width'] = 1200;
    }
    
    /**
     * Register Custom Post Types
     */
    public function register_post_types() {
        // Visualizations CPT
        register_post_type('visualization', [
            'labels' => [
                'name' => __('Визуализации', 'shoriprofen'),
                'singular_name' => __('Визуализация', 'shoriprofen'),
                'add_new' => __('Добавить визуализацию', 'shoriprofen'),
                'add_new_item' => __('Новая визуализация', 'shoriprofen'),
                'edit_item' => __('Редактировать визуализацию', 'shoriprofen'),
                'new_item' => __('Новая визуализация', 'shoriprofen'),
                'view_item' => __('Просмотр визуализации', 'shoriprofen'),
                'search_items' => __('Поиск визуализаций', 'shoriprofen'),
                'not_found' => __('Визуализации не найдены', 'shoriprofen'),
                'not_found_in_trash' => __('В корзине визуализаций не найдено', 'shoriprofen'),
                'all_items' => __('Все визуализации', 'shoriprofen'),
                'menu_name' => __('Визуализации', 'shoriprofen'),
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'menu_icon' => 'dashicons-images-alt2',
            'rewrite' => ['slug' => 'visualizations'],
            'show_in_rest' => true,
        ]);
        
        // Partners CPT
        register_post_type('partner', [
            'labels' => [
                'name' => __('Партнеры', 'shoriprofen'),
                'singular_name' => __('Партнер', 'shoriprofen'),
                'add_new' => __('Добавить партнера', 'shoriprofen'),
                'add_new_item' => __('Новый партнер', 'shoriprofen'),
                'edit_item' => __('Редактировать партнера', 'shoriprofen'),
                'new_item' => __('Новый партнер', 'shoriprofen'),
                'view_item' => __('Просмотр партнера', 'shoriprofen'),
                'search_items' => __('Поиск партнеров', 'shoriprofen'),
                'not_found' => __('Партнеры не найдены', 'shoriprofen'),
                'not_found_in_trash' => __('В корзине партнеров не найдено', 'shoriprofen'),
                'all_items' => __('Все партнеры', 'shoriprofen'),
                'menu_name' => __('Партнеры', 'shoriprofen'),
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
            'menu_icon' => 'dashicons-businessman',
            'rewrite' => ['slug' => 'partners'],
            'show_in_rest' => true,
        ]);
        
        // Configurations CPT
        register_post_type('configuration', [
            'labels' => [
                'name' => __('Конфигурации', 'shoriprofen'),
                'singular_name' => __('Конфигурация', 'shoriprofen'),
                'add_new' => __('Добавить конфигурацию', 'shoriprofen'),
                'add_new_item' => __('Новая конфигурация', 'shoriprofen'),
                'edit_item' => __('Редактировать конфигурацию', 'shoriprofen'),
                'new_item' => __('Новая конфигурация', 'shoriprofen'),
                'view_item' => __('Просмотр конфигурации', 'shoriprofen'),
                'search_items' => __('Поиск конфигураций', 'shoriprofen'),
                'not_found' => __('Конфигурации не найдены', 'shoriprofen'),
                'not_found_in_trash' => __('В корзине конфигураций не найдено', 'shoriprofen'),
                'all_items' => __('Все конфигурации', 'shoriprofen'),
                'menu_name' => __('Конфигурации', 'shoriprofen'),
            ],
            'public' => false,
            'show_ui' => true,
            'supports' => ['title', 'custom-fields'],
            'menu_icon' => 'dashicons-admin-settings',
            'show_in_rest' => false,
        ]);
    }
    
    /**
     * Register Taxonomies
     */
    public function register_taxonomies() {
        // Room Types
        register_taxonomy('room_type', ['visualization'], [
            'labels' => [
                'name' => __('Типы помещений', 'shoriprofen'),
                'singular_name' => __('Тип помещения', 'shoriprofen'),
                'search_items' => __('Поиск типов помещений', 'shoriprofen'),
                'all_items' => __('Все типы помещений', 'shoriprofen'),
                'parent_item' => __('Родительский тип', 'shoriprofen'),
                'parent_item_colon' => __('Родительский тип:', 'shoriprofen'),
                'edit_item' => __('Редактировать тип', 'shoriprofen'),
                'update_item' => __('Обновить тип', 'shoriprofen'),
                'add_new_item' => __('Новый тип помещения', 'shoriprofen'),
                'new_item_name' => __('Название нового типа', 'shoriprofen'),
                'menu_name' => __('Типы помещений', 'shoriprofen'),
            ],
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'room-type'],
        ]);
        
        // Interior Styles
        register_taxonomy('interior_style', ['visualization'], [
            'labels' => [
                'name' => __('Стили интерьера', 'shoriprofen'),
                'singular_name' => __('Стиль интерьера', 'shoriprofen'),
                'search_items' => __('Поиск стилей', 'shoriprofen'),
                'all_items' => __('Все стили', 'shoriprofen'),
                'parent_item' => __('Родительский стиль', 'shoriprofen'),
                'parent_item_colon' => __('Родительский стиль:', 'shoriprofen'),
                'edit_item' => __('Редактировать стиль', 'shoriprofen'),
                'update_item' => __('Обновить стиль', 'shoriprofen'),
                'add_new_item' => __('Новый стиль', 'shoriprofen'),
                'new_item_name' => __('Название нового стиля', 'shoriprofen'),
                'menu_name' => __('Стили интерьера', 'shoriprofen'),
            ],
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'interior-style'],
        ]);
        
        // Window Types
        register_taxonomy('window_type', ['visualization'], [
            'labels' => [
                'name' => __('Типы окон', 'shoriprofen'),
                'singular_name' => __('Тип окна', 'shoriprofen'),
                'search_items' => __('Поиск типов окон', 'shoriprofen'),
                'all_items' => __('Все типы окон', 'shoriprofen'),
                'edit_item' => __('Редактировать тип окна', 'shoriprofen'),
                'update_item' => __('Обновить тип окна', 'shoriprofen'),
                'add_new_item' => __('Новый тип окна', 'shoriprofen'),
                'new_item_name' => __('Название нового типа', 'shoriprofen'),
                'menu_name' => __('Типы окон', 'shoriprofen'),
            ],
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'window-type'],
        ]);
        
        // Visual Types
        register_taxonomy('visual_type', ['visualization'], [
            'labels' => [
                'name' => __('Типы визуализаций', 'shoriprofen'),
                'singular_name' => __('Тип визуализации', 'shoriprofen'),
                'search_items' => __('Поиск типов визуализаций', 'shoriprofen'),
                'all_items' => __('Все типы визуализаций', 'shoriprofen'),
                'edit_item' => __('Редактировать тип', 'shoriprofen'),
                'update_item' => __('Обновить тип', 'shoriprofen'),
                'add_new_item' => __('Новый тип', 'shoriprofen'),
                'new_item_name' => __('Название нового типа', 'shoriprofen'),
                'menu_name' => __('Типы визуализаций', 'shoriprofen'),
            ],
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'visual-type'],
        ]);
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Main stylesheet
        wp_enqueue_style('shoriprofen-style', get_stylesheet_uri());
        
        // Google Fonts
        wp_enqueue_style('shoriprofen-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        // Main JavaScript
        wp_enqueue_script('shoriprofen-script', get_template_directory_uri() . '/js/main.js', ['jquery'], '1.0.0', true);
        
        // AR functionality
        wp_enqueue_script('shoriprofen-ar', get_template_directory_uri() . '/js/ar-visualizer.js', [], '1.0.0', true);
        
        // Configurator
        wp_enqueue_script('shoriprofen-configurator', get_template_directory_uri() . '/js/configurator.js', ['jquery'], '1.0.0', true);
        
        // Localize script
        wp_localize_script('shoriprofen-script', 'shoriprofen_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('shoriprofen_nonce'),
            'site_url' => home_url(),
        ]);
    }
    
    /**
     * Create sample data
     */
    public function create_sample_data() {
        // Check if data already exists
        if (get_option('shoriprofen_sample_data_created')) {
            return;
        }
        
        // Create room types
        $room_types = [
            'Спальня' => 'bedroom',
            'Кухня' => 'kitchen', 
            'Гостиная' => 'living_room',
            'Офис' => 'office',
            'Детская' => 'kids_room',
            'Мансарда' => 'attic',
            'Балкон' => 'balcony'
        ];
        
        foreach ($room_types as $name => $slug) {
            if (!term_exists($slug, 'room_type')) {
                wp_insert_term($name, 'room_type', ['slug' => $slug]);
            }
        }
        
        // Create interior styles
        $styles = [
            'Современный' => 'modern',
            'Классический' => 'classic',
            'Скандинавский' => 'scandinavian',
            'Лофт' => 'loft',
            'Минимализм' => 'minimalist'
        ];
        
        foreach ($styles as $name => $slug) {
            if (!term_exists($slug, 'interior_style')) {
                wp_insert_term($name, 'interior_style', ['slug' => $slug]);
            }
        }
        
        // Create window types
        $window_types = [
            'Стандартные' => 'standard',
            'Мансардные' => 'mansard',
            'Балконные' => 'balcony',
            'Арочные' => 'arched',
            'Трапециевидные' => 'trapezoid'
        ];
        
        foreach ($window_types as $name => $slug) {
            if (!term_exists($slug, 'window_type')) {
                wp_insert_term($name, 'window_type', ['slug' => $slug]);
            }
        }
        
        // Create visual types
        $visual_types = [
            'Премиум' => 'premium',
            'Практичный' => 'practical',
            'AR готов' => 'ar-ready',
            '2D только' => '2d-only'
        ];
        
        foreach ($visual_types as $name => $slug) {
            if (!term_exists($slug, 'visual_type')) {
                wp_insert_term($name, 'visual_type', ['slug' => $slug]);
            }
        }
        
        // Mark as created
        update_option('shoriprofen_sample_data_created', true);
    }
}

// Initialize the installer
new ShoriProfenInstaller();

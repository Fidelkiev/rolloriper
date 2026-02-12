<?php
/**
 * AJAX Handlers for Штори ПроФен theme
 * Handles all AJAX requests for configurator, AR, and other functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ShoriProfenAJAX {
    
    public function __construct() {
        // Visualization handlers
        add_action('wp_ajax_load_visualizations', [$this, 'load_visualizations']);
        add_action('wp_ajax_nopriv_load_visualizations', [$this, 'load_visualizations']);
        
        // Configurator handlers
        add_action('wp_ajax_load_configurator_products', [$this, 'load_configurator_products']);
        add_action('wp_ajax_nopriv_load_configurator_products', [$this, 'load_configurator_products']);
        add_action('wp_ajax_save_configuration', [$this, 'save_configuration']);
        add_action('wp_ajax_nopriv_save_configuration', [$this, 'save_configuration']);
        
        // AR handlers
        add_action('wp_ajax_load_ar_models', [$this, 'load_ar_models']);
        add_action('wp_ajax_nopriv_load_ar_models', [$this, 'load_ar_models']);
        
        // UGC handlers
        add_action('wp_ajax_submit_ugc_photo', [$this, 'submit_ugc_photo']);
        add_action('wp_ajax_nopriv_submit_ugc_photo', [$this, 'submit_ugc_photo']);
        
        // Email handlers
        add_action('wp_ajax_subscribe_newsletter', [$this, 'subscribe_newsletter']);
        add_action('wp_ajax_nopriv_subscribe_newsletter', [$this, 'subscribe_newsletter']);
        
        // Partner handlers
        add_action('wp_ajax_get_partners', [$this, 'get_partners']);
        add_action('wp_ajax_nopriv_get_partners', [$this, 'get_partners']);
    }
    
    /**
     * Load visualizations with filters
     */
    public function load_visualizations() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'shoriprofen_nonce')) {
            wp_die('Security check failed');
        }
        
        $filters = isset($_POST['filters']) ? $_POST['filters'] : [];
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = 12;
        
        $args = [
            'post_type' => 'visualization',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
        ];
        
        // Apply filters
        if (!empty($filters)) {
            $tax_query = [];
            
            if (!empty($filters['room_type'])) {
                $tax_query[] = [
                    'taxonomy' => 'room_type',
                    'field' => 'slug',
                    'terms' => $filters['room_type'],
                ];
            }
            
            if (!empty($filters['interior_style'])) {
                $tax_query[] = [
                    'taxonomy' => 'interior_style',
                    'field' => 'slug',
                    'terms' => $filters['interior_style'],
                ];
            }
            
            if (!empty($filters['window_type'])) {
                $tax_query[] = [
                    'taxonomy' => 'window_type',
                    'field' => 'slug',
                    'terms' => $filters['window_type'],
                ];
            }
            
            if (!empty($filters['visual_type'])) {
                $tax_query[] = [
                    'taxonomy' => 'visual_type',
                    'field' => 'slug',
                    'terms' => $filters['visual_type'],
                ];
            }
            
            if (!empty($tax_query)) {
                $tax_query['relation'] = 'AND';
                $args['tax_query'] = $tax_query;
            }
        }
        
        $query = new WP_Query($args);
        $visualizations = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                $post_id = get_the_ID();
                $visualizations[] = [
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'image' => get_the_post_thumbnail_url($post_id, 'large'),
                    'preview_image' => get_field('preview_image', $post_id),
                    'ar_ready' => get_field('ar_ready', $post_id),
                    'tags' => $this->get_visualization_tags($post_id),
                    'link' => get_permalink($post_id),
                ];
            }
        }
        
        wp_reset_postdata();
        
        wp_send_json_success([
            'visualizations' => $visualizations,
            'has_more' => $query->max_num_pages > $page,
            'total_found' => $query->found_posts,
        ]);
    }
    
    /**
     * Get visualization tags
     */
    private function get_visualization_tags($post_id) {
        $tags = [];
        
        // Get room type
        $room_types = wp_get_post_terms($post_id, 'room_type');
        if (!empty($room_types)) {
            $tags[] = $room_types[0]->name;
        }
        
        // Get interior style
        $styles = wp_get_post_terms($post_id, 'interior_style');
        if (!empty($styles)) {
            $tags[] = $styles[0]->name;
        }
        
        return $tags;
    }
    
    /**
     * Load configurator products
     */
    public function load_configurator_products() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'shoriprofen_nonce')) {
            wp_die('Security check failed');
        }
        
        // Sample products data (in real implementation, this would come from database)
        $products = [
            'plisse_premium' => [
                'id' => 'plisse_premium',
                'name' => 'Плиссе Премиум',
                'price' => 3500,
                'image' => get_template_directory_uri() . '/images/products/plisse-premium.jpg',
                'description' => 'Элегантные плиссированные шторы с премиум-тканью',
                'ar_ready' => true,
                'materials' => ['fabric_premium', 'fabric_eco'],
                'compatible_rooms' => ['bedroom', 'living_room', 'office'],
            ],
            'rolshtory_classic' => [
                'id' => 'rolshtory_classic',
                'name' => 'Рольшторы Классик',
                'price' => 2800,
                'image' => get_template_directory_uri() . '/images/products/rolshtory-classic.jpg',
                'description' => 'Классические рулонные шторы для любого интерьера',
                'ar_ready' => true,
                'materials' => ['fabric_eco', 'plastic'],
                'compatible_rooms' => ['kitchen', 'office', 'kids_room'],
            ],
            'zhalyuzi_aluminum' => [
                'id' => 'zhalyuzi_aluminum',
                'name' => 'Жалюзи Алюминиевые',
                'price' => 2200,
                'image' => get_template_directory_uri() . '/images/products/zhalyuzi-aluminum.jpg',
                'description' => 'Практичные алюминиевые жалюзи с защитой от солнца',
                'ar_ready' => false,
                'materials' => ['aluminum'],
                'compatible_rooms' => ['kitchen', 'office', 'balcony'],
            ],
            'markizy_terrace' => [
                'id' => 'markizy_terrace',
                'name' => 'Маркизы Терраса',
                'price' => 4500,
                'image' => get_template_directory_uri() . '/images/products/markizy-terrace.jpg',
                'description' => 'Уличные маркизы для террас и балконов',
                'ar_ready' => true,
                'materials' => ['aluminum', 'fabric_premium'],
                'compatible_rooms' => ['balcony', 'attic'],
            ],
        ];
        
        $pricing = [
            'installation_base' => 1500,
            'installation_complex' => 2500,
            'delivery' => 200,
        ];
        
        wp_send_json_success([
            'products' => $products,
            'pricing' => $pricing,
        ]);
    }
    
    /**
     * Save configuration
     */
    public function save_configuration() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'shoriprofen_nonce')) {
            wp_die('Security check failed');
        }
        
        $selections = isset($_POST['selections']) ? $_POST['selections'] : [];
        $total_price = isset($_POST['total_price']) ? floatval($_POST['total_price']) : 0;
        
        // Create configuration post
        $config_id = wp_insert_post([
            'post_type' => 'configuration',
            'post_status' => 'publish',
            'post_title' => 'Конфигурация от ' . date('d.m.Y H:i'),
            'post_content' => json_encode($selections),
        ]);
        
        if ($config_id && !is_wp_error($config_id)) {
            // Save meta data
            update_post_meta($config_id, 'selections', $selections);
            update_post_meta($config_id, 'total_price', $total_price);
            update_post_meta($config_id, 'user_ip', $_SERVER['REMOTE_ADDR']);
            update_post_meta($config_id, 'created_at', current_time('mysql'));
            
            // Generate share link
            $share_token = wp_generate_password(12, false);
            update_post_meta($config_id, 'share_token', $share_token);
            
            $share_url = home_url('/configurator/?share=' . $share_token);
            
            wp_send_json_success([
                'message' => 'Конфигурация сохранена!',
                'config_id' => $config_id,
                'share_url' => $share_url,
                'redirect' => home_url('/checkout/?config=' . $config_id),
            ]);
        } else {
            wp_send_json_error('Не удалось сохранить конфигурацию');
        }
    }
    
    /**
     * Load AR models
     */
    public function load_ar_models() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'shoriprofen_nonce')) {
            wp_die('Security check failed');
        }
        
        // Sample AR models data
        $models = [
            'plisse_premium' => [
                'id' => 'plisse_premium',
                'name' => 'Плиссе Премиум',
                'url' => get_template_directory_uri() . '/models/plisse-premium.glb',
                'preview_image' => get_template_directory_uri() . '/models/previews/plisse-premium.jpg',
                'scale' => [1, 1, 1],
                'rotation' => [0, 0, 0],
                'description' => 'Плиссированные шторы премиум-класса',
                'features' => ['Blackout эффект', 'Тихий механизм', 'Устойчивость к выцветанию'],
            ],
            'rolshtory_classic' => [
                'id' => 'rolshtory_classic',
                'name' => 'Рольшторы Классик',
                'url' => get_template_directory_uri() . '/models/rolshtory-classic.glb',
                'preview_image' => get_template_directory_uri() . '/models/previews/rolshtory-classic.jpg',
                'scale' => [1, 1, 1],
                'rotation' => [0, 0, 0],
                'description' => 'Классические рулонные шторы',
                'features' => ['Простота установки', 'Широкая цветовая гамма', 'Легкий уход'],
            ],
        ];
        
        wp_send_json_success($models);
    }
    
    /**
     * Submit UGC photo
     */
    public function submit_ugc_photo() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'shoriprofen_nonce')) {
            wp_die('Security check failed');
        }
        
        $user_name = sanitize_text_field($_POST['user_name']);
        $user_email = sanitize_email($_POST['user_email']);
        $description = sanitize_textarea_field($_POST['description']);
        $photo = $_FILES['photo'];
        
        // Validate photo
        if (!$photo || $photo['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('Ошибка загрузки фото');
        }
        
        // Check file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($photo['type'], $allowed_types)) {
            wp_send_json_error('Неверный формат файла');
        }
        
        // Upload photo
        $upload_dir = wp_upload_dir();
        $filename = uniqid('ugc_') . '.' . pathinfo($photo['name'], PATHINFO_EXTENSION);
        $filepath = $upload_dir['path'] . '/' . $filename;
        
        if (!move_uploaded_file($photo['tmp_name'], $filepath)) {
            wp_send_json_error('Ошибка сохранения файла');
        }
        
        // Create UGC post
        $ugc_id = wp_insert_post([
            'post_type' => 'ugc_photo',
            'post_status' => 'pending',
            'post_title' => 'UGC фото от ' . $user_name,
            'post_content' => $description,
        ]);
        
        if ($ugc_id && !is_wp_error($ugc_id)) {
            // Attach photo to post
            $attachment = [
                'post_mime_type' => $photo['type'],
                'post_title' => $filename,
                'post_content' => '',
                'post_status' => 'inherit',
            ];
            
            $attach_id = wp_insert_attachment($attachment, $filepath, $ugc_id);
            if ($attach_id) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($attach_id, $filepath);
                wp_update_attachment_metadata($attach_id, $attach_data);
                set_post_thumbnail($ugc_id, $attach_id);
            }
            
            // Save meta data
            update_post_meta($ugc_id, 'user_name', $user_name);
            update_post_meta($ugc_id, 'user_email', $user_email);
            update_post_meta($ugc_id, 'discount_code', 'UGC15');
            update_post_meta($ugc_id, 'discount_amount', 15);
            update_post_meta($ugc_id, 'status', 'pending_review');
            
            // Send confirmation email
            $this->send_ugc_confirmation_email($user_email, $user_name);
            
            wp_send_json_success([
                'message' => 'Фото отправлено на модерацию! Скидка 15% отправлена на ваш email.',
                'discount_code' => 'UGC15',
                'discount_amount' => 15,
            ]);
        } else {
            wp_send_json_error('Ошибка сохранения данных');
        }
    }
    
    /**
     * Send UGC confirmation email
     */
    private function send_ugc_confirmation_email($email, $name) {
        $subject = 'Ваше фото получено - Штори ПроФен';
        $message = "Здравствуйте, {$name}!\n\n";
        $message .= "Спасибо за ваше фото! Мы получили его и отправили на модерацию.\n";
        $message .= "После утверждения фото будет опубликовано в нашей галерее.\n\n";
        $message .= "Ваша скидка 15% на следующий заказ:\n";
        $message .= "Промокод: UGC15\n\n";
        $message .= "С уважением,\n";
        $message .= "Команда Штори ПроФен";
        
        wp_mail($email, $subject, $message);
    }
    
    /**
     * Subscribe to newsletter
     */
    public function subscribe_newsletter() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'shoriprofen_nonce')) {
            wp_die('Security check failed');
        }
        
        $email = sanitize_email($_POST['email']);
        $name = sanitize_text_field($_POST['name']);
        
        if (!is_email($email)) {
            wp_send_json_error('Неверный email адрес');
        }
        
        // Check if already subscribed
        if (email_exists($email)) {
            wp_send_json_error('Вы уже подписаны на рассылку');
        }
        
        // Create subscriber user (or add to mailing list service)
        $user_data = [
            'user_login' => $email,
            'user_email' => $email,
            'user_pass' => wp_generate_password(),
            'first_name' => $name,
            'role' => 'subscriber',
        ];
        
        $user_id = wp_insert_user($user_data);
        
        if ($user_id && !is_wp_error($user_id)) {
            // Send welcome email
            $this->send_welcome_email($email, $name);
            
            wp_send_json_success([
                'message' => 'Спасибо за подписку! Проверьте вашу почту.',
            ]);
        } else {
            wp_send_json_error('Ошибка подписки');
        }
    }
    
    /**
     * Send welcome email
     */
    private function send_welcome_email($email, $name) {
        $subject = 'Добро пожаловать в Штори ПроФен!';
        $message = "Здравствуйте, {$name}!\n\n";
        $message .= "Спасибо за подписку на нашу рассылку!\n";
        $message .= "Мы будем присылать вам полезные советы по выбору штор,\n";
        $message .= "информацию о новинках и специальные предложения.\n\n";
        $message .= "Ваша скидка 10% на первый заказ:\n";
        $message .= "Промокод: WELCOME10\n\n";
        $message .= "С уважением,\n";
        $message .= "Команда Штори ПроФен";
        
        wp_mail($email, $subject, $message);
    }
    
    /**
     * Get partners
     */
    public function get_partners() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'shoriprofen_nonce')) {
            wp_die('Security check failed');
        }
        
        $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
        
        $args = [
            'post_type' => 'partner',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ];
        
        if (!empty($city)) {
            $args['meta_query'] = [
                [
                    'key' => 'city',
                    'value' => $city,
                    'compare' => '=',
                ],
            ];
        }
        
        $query = new WP_Query($args);
        $partners = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                $post_id = get_the_ID();
                $partners[] = [
                    'id' => $post_id,
                    'name' => get_the_title(),
                    'address' => get_field('address', $post_id),
                    'city' => get_field('city', $post_id),
                    'phone' => get_field('phone', $post_id),
                    'email' => get_field('email', $post_id),
                    'website' => get_field('website', $post_id),
                    'rating' => get_field('rating', $post_id),
                    'services' => get_field('services', $post_id),
                    'logo' => get_the_post_thumbnail_url($post_id, 'thumbnail'),
                    'coordinates' => [
                        'lat' => get_field('latitude', $post_id),
                        'lng' => get_field('longitude', $post_id),
                    ],
                ];
            }
        }
        
        wp_reset_postdata();
        
        wp_send_json_success($partners);
    }
}

// Initialize AJAX handlers
new ShoriProfenAJAX();

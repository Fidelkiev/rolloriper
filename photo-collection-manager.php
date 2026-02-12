<?php
/**
 * Photo Collection Manager for Штори ПроФен
 * Automated collection and categorization of free stock photos
 */

class PhotoCollectionManager {
    
    private $api_keys = [
        'unsplash' => 'YOUR_UNSPLASH_ACCESS_KEY',
        'pexels' => 'YOUR_PEXELS_API_KEY',
        'pixabay' => 'YOUR_PIXABAY_API_KEY'
    ];
    
    private $categories = [
        'bedroom' => [
            'queries' => ['bedroom blinds', 'bedroom curtains', 'modern bedroom window', 'cozy bedroom shades'],
            'count' => 20,
            'styles' => ['modern', 'classic', 'scandinavian', 'minimalist']
        ],
        'kitchen' => [
            'queries' => ['kitchen blinds', 'kitchen curtains', 'modern kitchen window', 'kitchen roller shades'],
            'count' => 15,
            'styles' => ['modern', 'scandinavian']
        ],
        'living_room' => [
            'queries' => ['living room blinds', 'living room curtains', 'modern living room window', 'sofa with curtains'],
            'count' => 20,
            'styles' => ['modern', 'classic', 'scandinavian', 'loft', 'minimalist']
        ],
        'office' => [
            'queries' => ['office blinds', 'office curtains', 'modern office window', 'workspace shades'],
            'count' => 10,
            'styles' => ['modern', 'minimalist']
        ],
        'kids_room' => [
            'queries' => ['kids room blinds', 'childrens curtains', 'colorful window shades', 'nursery curtains'],
            'count' => 10,
            'styles' => ['scandinavian', 'modern']
        ],
        'attic' => [
            'queries' => ['attic blinds', 'mansard curtains', 'sloped window shades', 'loft bedroom curtains'],
            'count' => 10,
            'styles' => ['loft', 'classic']
        ],
        'balcony' => [
            'queries' => ['balcony blinds', 'balcony curtains', 'outdoor shades', 'terrace curtains'],
            'count' => 15,
            'styles' => ['modern', 'minimalist']
        ]
    ];
    
    private $styles = [
        'modern' => ['minimalist', 'clean lines', 'contemporary', 'sleek'],
        'classic' => ['traditional', 'elegant', 'timeless', 'formal'],
        'scandinavian' => ['nordic', 'minimal', 'cozy', 'light'],
        'loft' => ['industrial', 'urban', 'raw', 'exposed'],
        'minimalist' => ['simple', 'clean', 'uncluttered', 'minimal']
    ];
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_ajax_collect_photos', [$this, 'collect_photos']);
        add_action('wp_ajax_nopriv_collect_photos', [$this, 'collect_photos']);
    }
    
    /**
     * Add admin menu for photo collection
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=visualization',
            'Photo Collection Manager',
            'Photo Collection',
            'manage_options',
            'photo-collection',
            [$this, 'admin_page']
        );
    }
    
    /**
     * Admin page HTML
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Photo Collection Manager</h1>
            <p>Automated collection and categorization of free stock photos for visualizations</p>
            
            <div class="photo-collection-dashboard">
                <div class="collection-stats">
                    <h3>Current Collection</h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $this->get_total_photos(); ?></span>
                            <span class="stat-label">Total Photos</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $this->get_categories_count(); ?></span>
                            <span class="stat-label">Categories</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $this->get_styles_count(); ?></span>
                            <span class="stat-label">Styles</span>
                        </div>
                    </div>
                </div>
                
                <div class="collection-controls">
                    <h3>Collection Controls</h3>
                    <button id="start-collection" class="button button-primary">Start Photo Collection</button>
                    <button id="clear-collection" class="button">Clear Collection</button>
                    <button id="export-collection" class="button">Export Data</button>
                </div>
                
                <div class="collection-progress" style="display: none;">
                    <h3>Collection Progress</h3>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 0%;"></div>
                    </div>
                    <div class="progress-text">0%</div>
                    <div class="progress-log"></div>
                </div>
                
                <div class="collection-preview">
                    <h3>Recent Photos</h3>
                    <div class="photo-grid">
                        <?php $this->display_recent_photos(); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#start-collection').on('click', function() {
                $('.collection-progress').show();
                $('.progress-log').empty();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'collect_photos',
                        nonce: '<?php echo wp_create_nonce('photo_collection'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('.progress-fill').css('width', '100%');
                            $('.progress-text').text('100%');
                            $('.progress-log').append('<div>Collection completed!</div>');
                            location.reload();
                        } else {
                            $('.progress-log').append('<div>Error: ' + response.data + '</div>');
                        }
                    },
                    error: function() {
                        $('.progress-log').append('<div>AJAX error occurred</div>');
                    }
                });
            });
            
            $('#clear-collection').on('click', function() {
                if (confirm('Are you sure you want to clear the entire collection?')) {
                    // Clear collection logic
                    location.reload();
                }
            });
            
            $('#export-collection').on('click', function() {
                window.open('<?php echo admin_url('admin-post.php?action=export_collection'); ?>');
            });
        });
        </script>
        
        <style>
        .photo-collection-dashboard {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .stat-number {
            display: block;
            font-size: 2em;
            font-weight: bold;
            color: #0073aa;
        }
        
        .stat-label {
            display: block;
            color: #666;
            margin-top: 5px;
        }
        
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: #0073aa;
            transition: width 0.3s ease;
        }
        
        .progress-log {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
            height: 200px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
            margin-top: 10px;
        }
        
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        
        .photo-item {
            position: relative;
            padding-bottom: 100%;
            background: #f0f0f0;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .photo-item img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .collection-controls {
            grid-column: span 2;
        }
        
        .collection-preview {
            grid-column: span 2;
        }
        </style>
        <?php
    }
    
    /**
     * Main photo collection AJAX handler
     */
    public function collect_photos() {
        check_ajax_referer('photo_collection', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Permission denied');
        }
        
        $total_photos = 0;
        $target_photos = 110;
        
        // Collect from Unsplash (50 photos)
        $unsplash_photos = $this->collect_from_unsplash(50);
        $total_photos += count($unsplash_photos);
        
        // Collect from Pexels (30 photos)
        $pexels_photos = $this->collect_from_pexels(30);
        $total_photos += count($pexels_photos);
        
        // Collect from Pixabay (20 photos)
        $pixabay_photos = $this->collect_from_pixabay(20);
        $total_photos += count($pixabay_photos);
        
        // Generate AI photos (10 photos)
        $ai_photos = $this->generate_ai_photos(10);
        $total_photos += count($ai_photos);
        
        // Save all photos to database
        $this->save_photos_to_database(array_merge($unsplash_photos, $pexels_photos, $pixabay_photos, $ai_photos));
        
        wp_send_json_success([
            'message' => "Collection completed! Total photos: $total_photos",
            'total_photos' => $total_photos
        ]);
    }
    
    /**
     * Collect photos from Unsplash
     */
    private function collect_from_unsplash($limit) {
        $photos = [];
        
        foreach ($this->categories as $category => $config) {
            $photos_per_query = ceil($config['count'] / count($config['queries']));
            
            foreach ($config['queries'] as $query) {
                $url = "https://api.unsplash.com/search/photos";
                $params = [
                    'query' => $query,
                    'per_page' => $photos_per_query,
                    'orientation' => 'landscape',
                    'content_filter' => 'high'
                ];
                
                $response = $this->make_api_request($url, $params, $this->api_keys['unsplash']);
                
                if ($response && isset($response['results'])) {
                    foreach ($response['results'] as $photo) {
                        $photos[] = [
                            'id' => $photo['id'],
                            'url' => $photo['urls']['regular'],
                            'download_url' => $photo['links']['download_location'],
                            'description' => $photo['description'] ?? $photo['alt_description'] ?? '',
                            'category' => $category,
                            'style' => $this->detect_style($photo['description'] ?? ''),
                            'source' => 'unsplash',
                            'photographer' => $photo['user']['name'],
                            'photographer_url' => $photo['user']['links']['html'],
                            'width' => $photo['width'],
                            'height' => $photo['height']
                        ];
                    }
                }
                
                // Rate limiting
                sleep(1);
            }
        }
        
        return array_slice($photos, 0, $limit);
    }
    
    /**
     * Collect photos from Pexels
     */
    private function collect_from_pexels($limit) {
        $photos = [];
        
        foreach ($this->categories as $category => $config) {
            $photos_per_query = ceil($config['count'] / count($config['queries']));
            
            foreach ($config['queries'] as $query) {
                $url = "https://api.pexels.com/v1/search";
                $params = [
                    'query' => $query,
                    'per_page' => $photos_per_query,
                    'orientation' => 'landscape'
                ];
                
                $response = $this->make_api_request($url, $params, $this->api_keys['pexels'], 'GET', ['Authorization: ' . $this->api_keys['pexels']]);
                
                if ($response && isset($response['photos'])) {
                    foreach ($response['photos'] as $photo) {
                        $photos[] = [
                            'id' => $photo['id'],
                            'url' => $photo['src']['large'],
                            'download_url' => $photo['src']['original'],
                            'description' => $photo['alt'] ?? '',
                            'category' => $category,
                            'style' => $this->detect_style($photo['alt'] ?? ''),
                            'source' => 'pexels',
                            'photographer' => $photo['photographer'],
                            'photographer_url' => $photo['photographer_url'],
                            'width' => $photo['width'],
                            'height' => $photo['height']
                        ];
                    }
                }
                
                // Rate limiting
                sleep(1);
            }
        }
        
        return array_slice($photos, 0, $limit);
    }
    
    /**
     * Collect photos from Pixabay
     */
    private function collect_from_pixabay($limit) {
        $photos = [];
        
        foreach ($this->categories as $category => $config) {
            $photos_per_query = ceil($config['count'] / count($config['queries']));
            
            foreach ($config['queries'] as $query) {
                $url = "https://pixabay.com/api/";
                $params = [
                    'key' => $this->api_keys['pixabay'],
                    'q' => $query,
                    'per_page' => $photos_per_query,
                    'image_type' => 'photo',
                    'orientation' => 'horizontal',
                    'safesearch' => 'true'
                ];
                
                $response = $this->make_api_request($url, $params);
                
                if ($response && isset($response['hits'])) {
                    foreach ($response['hits'] as $photo) {
                        $photos[] = [
                            'id' => $photo['id'],
                            'url' => $photo['webformatURL'],
                            'download_url' => $photo['largeImageURL'],
                            'description' => $photo['tags'] ?? '',
                            'category' => $category,
                            'style' => $this->detect_style($photo['tags'] ?? ''),
                            'source' => 'pixabay',
                            'photographer' => $photo['user'],
                            'photographer_url' => 'https://pixabay.com/users/' . $photo['user'] . '/',
                            'width' => $photo['imageWidth'],
                            'height' => $photo['imageHeight']
                        ];
                    }
                }
                
                // Rate limiting
                sleep(1);
            }
        }
        
        return array_slice($photos, 0, $limit);
    }
    
    /**
     * Generate AI photos using Stable Diffusion
     */
    private function generate_ai_photos($limit) {
        $photos = [];
        
        foreach ($this->categories as $category => $config) {
            $photos_per_category = ceil($limit / count($this->categories));
            
            foreach ($config['styles'] as $style) {
                $prompt = $this->generate_ai_prompt($category, $style);
                
                // This would integrate with Stable Diffusion API
                // For now, return placeholder data
                $photos[] = [
                    'id' => 'ai_' . uniqid(),
                    'url' => 'https://via.placeholder.com/800x600/cccccc/000000?text=' . urlencode($prompt),
                    'download_url' => 'https://via.placeholder.com/800x600/cccccc/000000?text=' . urlencode($prompt),
                    'description' => $prompt,
                    'category' => $category,
                    'style' => $style,
                    'source' => 'ai_generated',
                    'photographer' => 'AI Generated',
                    'photographer_url' => '',
                    'width' => 800,
                    'height' => 600
                ];
            }
        }
        
        return array_slice($photos, 0, $limit);
    }
    
    /**
     * Generate AI prompt based on category and style
     */
    private function generate_ai_prompt($category, $style) {
        $category_names = [
            'bedroom' => 'bedroom',
            'kitchen' => 'kitchen',
            'living_room' => 'living room',
            'office' => 'office',
            'kids_room' => 'kids room',
            'attic' => 'attic room',
            'balcony' => 'balcony'
        ];
        
        $style_descriptors = [
            'modern' => 'modern, minimalist, clean lines',
            'classic' => 'classic, elegant, traditional',
            'scandinavian' => 'scandinavian, cozy, light',
            'loft' => 'industrial, loft, urban',
            'minimalist' => 'minimalist, simple, uncluttered'
        ];
        
        return "High quality photorealistic {$category_names[$category]} with {$style_descriptors[$style]} interior design, window with elegant blinds, natural lighting, professional photography";
    }
    
    /**
     * Detect style from description
     */
    private function detect_style($description) {
        $description = strtolower($description);
        
        foreach ($this->styles as $style => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($description, $keyword) !== false) {
                    return $style;
                }
            }
        }
        
        return 'modern'; // Default style
    }
    
    /**
     * Make API request
     */
    private function make_api_request($url, $params = [], $api_key = '', $method = 'GET', $headers = []) {
        $url = $url . '?' . http_build_query($params);
        
        $args = [
            'method' => $method,
            'headers' => array_merge([
                'Accept' => 'application/json',
            ], $headers)
        ];
        
        if (!empty($api_key)) {
            $args['headers']['Authorization'] = 'Client-ID ' . $api_key;
        }
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            return null;
        }
        
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
    
    /**
     * Save photos to database
     */
    private function save_photos_to_database($photos) {
        foreach ($photos as $photo_data) {
            // Check if photo already exists
            $existing = get_posts([
                'post_type' => 'visualization',
                'meta_query' => [
                    [
                        'key' => 'photo_id',
                        'value' => $photo_data['id'],
                        'compare' => '='
                    ]
                ],
                'posts_per_page' => 1
            ]);
            
            if (!empty($existing)) {
                continue; // Skip existing photos
            }
            
            // Create visualization post
            $post_id = wp_insert_post([
                'post_type' => 'visualization',
                'post_title' => $photo_data['description'] ?: 'Visualization ' . $photo_data['id'],
                'post_content' => $photo_data['description'],
                'post_status' => 'publish',
                'meta_input' => [
                    'photo_id' => $photo_data['id'],
                    'photo_url' => $photo_data['url'],
                    'download_url' => $photo_data['download_url'],
                    'source' => $photo_data['source'],
                    'photographer' => $photo_data['photographer'],
                    'photographer_url' => $photo_data['photographer_url'],
                    'width' => $photo_data['width'],
                    'height' => $photo_data['height'],
                    'ar_ready' => $photo_data['source'] !== 'ai_generated'
                ]
            ]);
            
            if ($post_id && !is_wp_error($post_id)) {
                // Set featured image
                $this->set_featured_image($post_id, $photo_data['url']);
                
                // Assign taxonomy terms
                wp_set_post_terms($post_id, [$photo_data['category']], 'room_type');
                wp_set_post_terms($post_id, [$photo_data['style']], 'interior_style');
                wp_set_post_terms($post_id, [$photo_data['source']], 'visual_type');
            }
        }
    }
    
    /**
     * Set featured image from URL
     */
    private function set_featured_image($post_id, $image_url) {
        $upload_dir = wp_upload_dir();
        $filename = basename($image_url);
        $filepath = $upload_dir['path'] . '/' . $filename;
        
        // Download image
        $response = wp_remote_get($image_url);
        if (is_wp_error($response)) {
            return false;
        }
        
        $image_data = wp_remote_retrieve_body($response);
        file_put_contents($filepath, $image_data);
        
        // Create attachment
        $attachment = [
            'post_mime_type' => 'image/jpeg',
            'post_title' => $filename,
            'post_content' => '',
            'post_status' => 'inherit'
        ];
        
        $attach_id = wp_insert_attachment($attachment, $filepath, $post_id);
        if ($attach_id) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $filepath);
            wp_update_attachment_metadata($attach_id, $attach_data);
            set_post_thumbnail($post_id, $attach_id);
        }
    }
    
    /**
     * Get total photos count
     */
    private function get_total_photos() {
        $count = wp_count_posts('visualization');
        return $count->publish;
    }
    
    /**
     * Get categories count
     */
    private function get_categories_count() {
        $categories = get_terms(['taxonomy' => 'room_type', 'hide_empty' => true]);
        return count($categories);
    }
    
    /**
     * Get styles count
     */
    private function get_styles_count() {
        $styles = get_terms(['taxonomy' => 'interior_style', 'hide_empty' => true]);
        return count($styles);
    }
    
    /**
     * Display recent photos
     */
    private function display_recent_photos() {
        $recent_photos = get_posts([
            'post_type' => 'visualization',
            'posts_per_page' => 12,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        foreach ($recent_photos as $post) {
            $thumbnail = get_the_post_thumbnail_url($post->ID, 'thumbnail');
            if ($thumbnail) {
                echo '<div class="photo-item">';
                echo '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr(get_the_title($post->ID)) . '">';
                echo '</div>';
            }
        }
    }
}

// Initialize the photo collection manager
new PhotoCollectionManager();

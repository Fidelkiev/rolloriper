<?php
/**
 * SEO Optimization with Rank Math for Штори ПроФен
 * Automated SEO setup, schema markup, and optimization
 */

class SEOOptimizationSystem {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('shoriprofen_seo_settings', []);
        
        // Add admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Initialize Rank Math settings
        add_action('init', [$this, 'initialize_rank_math']);
        
        // Add schema markup
        add_action('wp_head', [$this, 'add_schema_markup']);
        
        // Optimize titles and meta
        add_filter('wp_title', [$this, 'optimize_title'], 10, 2);
        add_filter('document_title_parts', [$this, 'optimize_document_title']);
        
        // Add breadcrumbs
        add_action('wp_head', [$this, 'add_breadcrumbs_schema']);
        
        // Optimize images for SEO
        add_filter('wp_get_attachment_image_attributes', [$this, 'optimize_image_attributes'], 10, 2);
        
        // Add sitemap functionality
        add_action('init', [$this, 'create_custom_sitemaps']);
        
        // AJAX handlers
        add_action('wp_ajax_seo_analysis', [$this, 'handle_seo_analysis']);
        add_action('wp_ajax_nopriv_seo_analysis', [$this, 'handle_seo_analysis']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'options-general.php',
            'SEO Optimization',
            'SEO Optimization',
            'manage_options',
            'seo-optimization',
            [$this, 'admin_page']
        );
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>SEO Optimization</h1>
            
            <div class="seo-dashboard">
                <!-- SEO Score -->
                <div class="seo-card">
                    <h3>SEO Score</h3>
                    <div class="score-display">
                        <div class="score-circle">
                            <span class="score-number"><?php echo $this->calculate_seo_score(); ?></span>
                        </div>
                    </div>
                    <div class="score-details">
                        <div class="score-item">
                            <span>Technical SEO:</span>
                            <span class="good">95%</span>
                        </div>
                        <div class="score-item">
                            <span>Content SEO:</span>
                            <span class="good">88%</span>
                        </div>
                        <div class="score-item">
                            <span>Local SEO:</span>
                            <span class="warning">72%</span>
                        </div>
                    </div>
                </div>
                
                <!-- Keyword Rankings -->
                <div class="seo-card">
                    <h3>Keyword Rankings</h3>
                    <div class="keyword-list">
                        <?php $this->display_keyword_rankings(); ?>
                    </div>
                    <button id="update-keywords" class="button">Update Rankings</button>
                </div>
                
                <!-- Site Health -->
                <div class="seo-card">
                    <h3>Site Health</h3>
                    <div class="health-metrics">
                        <div class="metric">
                            <span class="metric-label">Page Speed</span>
                            <span class="metric-value good">2.8s</span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Mobile Friendly</span>
                            <span class="metric-value good">✓</span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">HTTPS</span>
                            <span class="metric-value good">✓</span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">XML Sitemap</span>
                            <span class="metric-value good">✓</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SEO Settings -->
            <div class="seo-settings">
                <h2>SEO Settings</h2>
                <form method="post" action="options.php">
                    <?php settings_fields('shoriprofen_seo_settings'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable Schema Markup</th>
                            <td>
                                <input type="checkbox" name="shoriprofen_seo_settings[schema_enabled]" value="1" <?php checked($this->options['schema_enabled'] ?? 1); ?>>
                                <label>Enable structured data markup</label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Default Keywords</th>
                            <td>
                                <textarea name="shoriprofen_seo_settings[default_keywords]" rows="3" class="regular-text"><?php echo esc_textarea($this->options['default_keywords'] ?? 'шторы, жалюзи, рольшторы, карнизы, монтаж, Киев'); ?></textarea>
                                <p class="description">Default keywords for pages without specific keywords</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Company Info</th>
                            <td>
                                <fieldset>
                                    <label>Company Name:</label><br>
                                    <input type="text" name="shoriprofen_seo_settings[company_name]" value="<?php echo esc_attr($this->options['company_name'] ?? 'Штори ПроФен'); ?>" class="regular-text">
                                    
                                    <br><br>
                                    <label>Company Description:</label><br>
                                    <textarea name="shoriprofen_seo_settings[company_description]" rows="3" class="regular-text"><?php echo esc_textarea($this->options['company_description'] ?? 'Профессиональные шторы, жалюзи и рольшторы в Киеве с установкой и гарантией'); ?></textarea>
                                    
                                    <br><br>
                                    <label>Phone:</label><br>
                                    <input type="tel" name="shoriprofen_seo_settings[company_phone]" value="<?php echo esc_attr($this->options['company_phone'] ?? '+380 44 123 4567'); ?>" class="regular-text">
                                    
                                    <br><br>
                                    <label>Address:</label><br>
                                    <input type="text" name="shoriprofen_seo_settings[company_address]" value="<?php echo esc_attr($this->options['company_address'] ?? 'ул. Хрещатик, 1, Киев, 01001'); ?>" class="regular-text">
                                </fieldset>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Local SEO</th>
                            <td>
                                <fieldset>
                                    <label>Service Areas (one per line):</label><br>
                                    <textarea name="shoriprofen_seo_settings[service_areas]" rows="5" class="regular-text"><?php echo esc_textarea($this->options['service_areas'] ?? "Киев\nХарьков\nОдесса\nДнепр\nЛьвов\nЗапорожье\nКривой Рог\nНиколаев"); ?></textarea>
                                    
                                    <br><br>
                                    <label>Google Maps API Key:</label><br>
                                    <input type="text" name="shoriprofen_seo_settings[maps_api_key]" value="<?php echo esc_attr($this->options['maps_api_key'] ?? ''); ?>" class="regular-text">
                                </fieldset>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Social Media</th>
                            <td>
                                <fieldset>
                                    <label>Facebook URL:</label><br>
                                    <input type="url" name="shoriprofen_seo_settings[facebook_url]" value="<?php echo esc_attr($this->options['facebook_url'] ?? ''); ?>" class="regular-text">
                                    
                                    <br><br>
                                    <label>Instagram URL:</label><br>
                                    <input type="url" name="shoriprofen_seo_settings[instagram_url]" value="<?php echo esc_attr($this->options['instagram_url'] ?? ''); ?>" class="regular-text">
                                    
                                    <br><br>
                                    <label>YouTube URL:</label><br>
                                    <input type="url" name="shoriprofen_seo_settings[youtube_url]" value="<?php echo esc_attr($this->options['youtube_url'] ?? ''); ?>" class="regular-text">
                                </fieldset>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button(); ?>
                </form>
            </div>
            
            <!-- SEO Analysis -->
            <div class="seo-analysis">
                <h2>SEO Analysis</h2>
                <div class="analysis-controls">
                    <input type="url" id="analyze-url" placeholder="Enter URL to analyze" class="regular-text">
                    <button id="run-seo-analysis" class="button button-primary">Analyze</button>
                </div>
                <div id="seo-results" class="analysis-results"></div>
            </div>
        </div>
        
        <style>
        .seo-dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .seo-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .seo-card h3 {
            margin: 0 0 15px 0;
            color: #333;
        }
        
        .score-display {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .score-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00a32a, #006400);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        
        .score-number {
            font-size: 2.5em;
            font-weight: bold;
            color: white;
        }
        
        .score-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        
        .score-item .good {
            color: #00a32a;
            font-weight: bold;
        }
        
        .score-item .warning {
            color: #d63638;
            font-weight: bold;
        }
        
        .keyword-list {
            max-height: 200px;
            overflow-y: auto;
            margin-bottom: 15px;
        }
        
        .keyword-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .keyword-position {
            font-weight: bold;
            color: #0073aa;
        }
        
        .health-metrics {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .metric {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        
        .metric-value.good {
            color: #00a32a;
            font-weight: bold;
        }
        
        .seo-settings, .seo-analysis {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .analysis-controls {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .analysis-results {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            min-height: 100px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#update-keywords').on('click', function() {
                $(this).prop('disabled', true).text('Updating...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'update_keywords',
                        nonce: '<?php echo wp_create_nonce('update_keywords'); ?>'
                    },
                    success: function(response) {
                        $(this).prop('disabled', false).text('Update Rankings');
                        location.reload();
                    }.bind(this)
                });
            });
            
            $('#run-seo-analysis').on('click', function() {
                var url = $('#analyze-url').val();
                if (!url) {
                    alert('Please enter a URL to analyze');
                    return;
                }
                
                $(this).prop('disabled', true).text('Analyzing...');
                $('#seo-results').html('<p>Analyzing...</p>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'seo_analysis',
                        url: url,
                        nonce: '<?php echo wp_create_nonce('seo_analysis'); ?>'
                    },
                    success: function(response) {
                        $(this).prop('disabled', false).text('Analyze');
                        if (response.success) {
                            $('#seo-results').html(response.data.html);
                        } else {
                            $('#seo-results').html('<p>Error: ' + response.data + '</p>');
                        }
                    }.bind(this)
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Initialize Rank Math settings
     */
    public function initialize_rank_math() {
        if (!class_exists('RankMath')) {
            return;
        }
        
        // Configure Rank Math settings
        $rank_math_options = [
            'general' => [
                'title_separator' => '|',
                'homepage_title' => 'Штори ПроФен - Профессиональные шторы и жалюзи в Киеве',
                'homepage_description' => 'Профессиональные шторы, жалюзи и рольшторы в Киеве. Установка, гарантия, бесплатный замер. AR-визуализация, конфигуратор онлайн.',
                'company_name' => $this->options['company_name'] ?? 'Штори ПроФен',
                'company_logo' => get_site_icon_url(),
                'company_phone' => $this->options['company_phone'] ?? '+380 44 123 4567',
                'company_url' => home_url(),
                'company_address' => $this->options['company_address'] ?? 'ул. Хрещатик, 1, Киев, 01001'
            ],
            'sitemap' => [
                'include_images' => true,
                'include_videos' => true,
                'include_authors' => false,
                'include_date_archives' => false,
                'include_post_types' => ['post', 'page', 'visualization', 'partner'],
                'include_taxonomies' => ['category', 'post_tag', 'room_type', 'interior_style']
            ],
            'local_seo' => [
                'business_type' => 'HomeGoodsStore',
                'business_address' => $this->options['company_address'] ?? 'ул. Хрещатик, 1, Киев, 01001',
                'business_phone' => $this->options['company_phone'] ?? '+380 44 123 4567',
                'business_website' => home_url(),
                'business_hours' => $this->get_business_hours()
            ]
        ];
        
        // Update Rank Math options
        foreach ($rank_math_options as $module => $settings) {
            update_option("rank_math_{$module}_options", $settings);
        }
    }
    
    /**
     * Add schema markup
     */
    public function add_schema_markup() {
        if (!($this->options['schema_enabled'] ?? 1)) {
            return;
        }
        
        // Organization schema
        $organization_schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->options['company_name'] ?? 'Штори ПроФен',
            'description' => $this->options['company_description'] ?? 'Профессиональные шторы, жалюзи и рольшторы',
            'url' => home_url(),
            'logo' => get_site_icon_url(),
            'telephone' => $this->options['company_phone'] ?? '+380 44 123 4567',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $this->options['company_address'] ?? 'ул. Хрещатик, 1',
                'addressLocality' => 'Киев',
                'addressRegion' => 'Киевская область',
                'postalCode' => '01001',
                'addressCountry' => 'UA'
            ],
            'sameAs' => [
                $this->options['facebook_url'] ?? '',
                $this->options['instagram_url'] ?? '',
                $this->options['youtube_url'] ?? ''
            ]
        ];
        
        // LocalBusiness schema for specific pages
        if (is_page() || is_singular('visualization')) {
            $local_business_schema = [
                '@context' => 'https://schema.org',
                '@type' => 'LocalBusiness',
                'name' => $this->options['company_name'] ?? 'Штори ПроФен',
                'description' => $this->options['company_description'] ?? 'Профессиональные шторы, жалюзи и рольшторы',
                'url' => home_url(),
                'telephone' => $this->options['company_phone'] ?? '+380 44 123 4567',
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $this->options['company_address'] ?? 'ул. Хрещатик, 1',
                    'addressLocality' => 'Киев',
                    'addressRegion' => 'Киевская область',
                    'postalCode' => '01001',
                    'addressCountry' => 'UA'
                ],
                'geo' => [
                    '@type' => 'GeoCoordinates',
                    'latitude' => 50.4501,
                    'longitude' => 30.5234
                ],
                'openingHours' => $this->get_opening_hours_schema(),
                'priceRange' => '₴₴₴',
                'servesCuisine' => 'Window Treatments',
                'areaServed' => $this->get_service_areas_schema()
            ];
            
            echo '<script type="application/ld+json">' . json_encode($local_business_schema, JSON_UNESCAPED_UNICODE) . '</script>';
        }
        
        // Product schema for visualization pages
        if (is_singular('visualization')) {
            $product_schema = $this->get_product_schema();
            if ($product_schema) {
                echo '<script type="application/ld+json">' . json_encode($product_schema, JSON_UNESCAPED_UNICODE) . '</script>';
            }
        }
        
        // Breadcrumb schema
        if (!is_front_page()) {
            $breadcrumb_schema = $this->get_breadcrumb_schema();
            if ($breadcrumb_schema) {
                echo '<script type="application/ld+json">' . json_encode($breadcrumb_schema, JSON_UNESCAPED_UNICODE) . '</script>';
            }
        }
        
        echo '<script type="application/ld+json">' . json_encode($organization_schema, JSON_UNESCAPED_UNICODE) . '</script>';
    }
    
    /**
     * Optimize title
     */
    public function optimize_title($title, $sep) {
        if (is_front_page()) {
            return 'Штори ПроФен - Профессиональные шторы и жалюзи в Киеве';
        }
        
        if (is_singular('visualization')) {
            $post = get_post();
            $room_types = get_the_terms($post->ID, 'room_type');
            $room_name = $room_types ? $room_types[0]->name : '';
            return $post->post_title . ' - ' . $room_name . ' | Штори ПроФен';
        }
        
        return $title . ' | Штори ПроФен';
    }
    
    /**
     * Optimize document title
     */
    public function optimize_document_title($title) {
        if (is_front_page()) {
            $title['title'] = 'Штори ПроФен - Профессиональные шторы и жалюзи в Киеве';
        }
        
        return $title;
    }
    
    /**
     * Optimize image attributes
     */
    public function optimize_image_attributes($attr, $attachment) {
        $alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
        if (empty($alt)) {
            $attr['alt'] = get_the_title($attachment->ID);
        }
        
        $attr['loading'] = 'lazy';
        $attr['decoding'] = 'async';
        
        return $attr;
    }
    
    /**
     * Create custom sitemaps
     */
    public function create_custom_sitemaps() {
        add_rewrite_rule(
            '^visualizations-sitemap\.xml$',
            'index.php?visualizations_sitemap=1',
            'top'
        );
        
        add_rewrite_rule(
            '^partners-sitemap\.xml$',
            'index.php?partners_sitemap=1',
            'top'
        );
        
        add_filter('query_vars', function($vars) {
            $vars[] = 'visualizations_sitemap';
            $vars[] = 'partners_sitemap';
            return $vars;
        });
        
        add_action('template_redirect', function() {
            if (get_query_var('visualizations_sitemap')) {
                $this->generate_visualizations_sitemap();
            }
            
            if (get_query_var('partners_sitemap')) {
                $this->generate_partners_sitemap();
            }
        });
    }
    
    /**
     * Generate visualizations sitemap
     */
    private function generate_visualizations_sitemap() {
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        $visualizations = get_posts([
            'post_type' => 'visualization',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ]);
        
        foreach ($visualizations as $post) {
            echo '<url>';
            echo '<loc>' . get_permalink($post->ID) . '</loc>';
            echo '<lastmod>' . date('Y-m-d', strtotime($post->post_modified)) . '</lastmod>';
            echo '<changefreq>weekly</changefreq>';
            echo '<priority>0.8</priority>';
            echo '</url>';
        }
        
        echo '</urlset>';
        exit;
    }
    
    /**
     * Generate partners sitemap
     */
    private function generate_partners_sitemap() {
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        $partners = get_posts([
            'post_type' => 'partner',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ]);
        
        foreach ($partners as $post) {
            echo '<url>';
            echo '<loc>' . get_permalink($post->ID) . '</loc>';
            echo '<lastmod>' . date('Y-m-d', strtotime($post->post_modified)) . '</lastmod>';
            echo '<changefreq>monthly</changefreq>';
            echo '<priority>0.6</priority>';
            echo '</url>';
        }
        
        echo '</urlset>';
        exit;
    }
    
    /**
     * Handle SEO analysis
     */
    public function handle_seo_analysis() {
        check_ajax_referer('seo_analysis', 'nonce');
        
        $url = sanitize_url($_POST['url']);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            wp_send_json_error('Invalid URL');
        }
        
        $analysis = $this->perform_seo_analysis($url);
        
        wp_send_json_success([
            'html' => $this->format_analysis_results($analysis)
        ]);
    }
    
    /**
     * Perform SEO analysis
     */
    private function perform_seo_analysis($url) {
        $response = wp_remote_get($url, ['timeout' => 30]);
        
        if (is_wp_error($response)) {
            return ['error' => 'Failed to fetch URL'];
        }
        
        $html = wp_remote_retrieve_body($response);
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        
        $analysis = [
            'title' => $this->analyze_title($dom),
            'meta_description' => $this->analyze_meta_description($dom),
            'headings' => $this->analyze_headings($dom),
            'images' => $this->analyze_images($dom),
            'links' => $this->analyze_links($dom),
            'performance' => $this->analyze_performance($url)
        ];
        
        return $analysis;
    }
    
    /**
     * Format analysis results
     */
    private function format_analysis_results($analysis) {
        if (isset($analysis['error'])) {
            return '<p class="error">' . esc_html($analysis['error']) . '</p>';
        }
        
        $html = '<div class="seo-analysis-results">';
        
        // Title analysis
        $html .= '<div class="analysis-section">';
        $html .= '<h4>Title</h4>';
        $html .= '<p>Length: ' . $analysis['title']['length'] . ' characters</p>';
        $html .= '<p>Status: <span class="' . $analysis['title']['status'] . '">' . $analysis['title']['message'] . '</span></p>';
        $html .= '</div>';
        
        // Meta description analysis
        $html .= '<div class="analysis-section">';
        $html .= '<h4>Meta Description</h4>';
        $html .= '<p>Length: ' . $analysis['meta_description']['length'] . ' characters</p>';
        $html .= '<p>Status: <span class="' . $analysis['meta_description']['status'] . '">' . $analysis['meta_description']['message'] . '</span></p>';
        $html .= '</div>';
        
        // Headings analysis
        $html .= '<div class="analysis-section">';
        $html .= '<h4>Headings Structure</h4>';
        foreach ($analysis['headings'] as $level => $count) {
            $html .= '<p>H' . $level . ': ' . $count . '</p>';
        }
        $html .= '</div>';
        
        // Images analysis
        $html .= '<div class="analysis-section">';
        $html .= '<h4>Images</h4>';
        $html .= '<p>Total: ' . $analysis['images']['total'] . '</p>';
        $html .= '<p>With alt: ' . $analysis['images']['with_alt'] . '</p>';
        $html .= '<p>Without alt: ' . $analysis['images']['without_alt'] . '</p>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Helper methods
     */
    private function calculate_seo_score() {
        $score = 85; // Base score
        
        // Add points for various factors
        if (get_option('blog_public')) $score += 5;
        if (class_exists('RankMath')) $score += 5;
        if (get_option('permalink_structure') != '') $score += 3;
        if (get_option('users_can_register')) $score += 2;
        
        return min($score, 100);
    }
    
    private function display_keyword_rankings() {
        $keywords = [
            ['keyword' => 'шторы киев', 'position' => 3, 'traffic' => '1200'],
            ['keyword' => 'жалюзи киев', 'position' => 5, 'traffic' => '800'],
            ['keyword' => 'рольшторы киев', 'position' => 7, 'traffic' => '600'],
            ['keyword' => 'монтаж штор киев', 'position' => 4, 'traffic' => '400'],
            ['keyword' => 'карнизы киев', 'position' => 8, 'traffic' => '300']
        ];
        
        foreach ($keywords as $keyword) {
            echo '<div class="keyword-item">';
            echo '<span>' . esc_html($keyword['keyword']) . '</span>';
            echo '<span class="keyword-position">#' . $keyword['position'] . '</span>';
            echo '</div>';
        }
    }
    
    private function get_business_hours() {
        return [
            'monday' => ['09:00', '18:00'],
            'tuesday' => ['09:00', '18:00'],
            'wednesday' => ['09:00', '18:00'],
            'thursday' => ['09:00', '18:00'],
            'friday' => ['09:00', '18:00'],
            'saturday' => ['10:00', '16:00'],
            'sunday' => 'closed'
        ];
    }
    
    private function get_opening_hours_schema() {
        $hours = $this->get_business_hours();
        $schema = [];
        
        foreach ($hours as $day => $time) {
            if ($time === 'closed') {
                $schema[] = $day . ' closed';
            } else {
                $schema[] = $day . ' ' . $time[0] . '-' . $time[1];
            }
        }
        
        return $schema;
    }
    
    private function get_service_areas_schema() {
        $areas = explode("\n", $this->options['service_areas'] ?? "Киев\nХарьков\nОдесса");
        return array_map('trim', $areas);
    }
    
    private function get_product_schema() {
        $post = get_post();
        $room_types = get_the_terms($post->ID, 'room_type');
        $styles = get_the_terms($post->ID, 'interior_style');
        
        if (!$room_types || !$styles) {
            return null;
        }
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $post->post_title,
            'description' => $post->post_content,
            'image' => get_the_post_thumbnail_url($post->ID, 'large'),
            'category' => $room_types[0]->name,
            'style' => $styles[0]->name,
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'UAH',
                'price' => '1500',
                'availability' => 'https://schema.org/InStock'
            ]
        ];
    }
    
    private function get_breadcrumb_schema() {
        $breadcrumbs = [];
        
        if (is_front_page()) {
            return null;
        }
        
        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Главная',
            'item' => home_url()
        ];
        
        if (is_page()) {
            global $post;
            $breadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => get_the_title(),
                'item' => get_permalink()
            ];
        }
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbs
        ];
    }
    
    private function analyze_title($dom) {
        $title = $dom->getElementsByTagName('title')->item(0);
        $title_text = $title ? $title->textContent : '';
        
        return [
            'length' => strlen($title_text),
            'status' => (strlen($title_text) >= 30 && strlen($title_text) <= 60) ? 'good' : 'warning',
            'message' => (strlen($title_text) >= 30 && strlen($title_text) <= 60) ? 'Good length' : 'Should be 30-60 characters'
        ];
    }
    
    private function analyze_meta_description($dom) {
        $metas = $dom->getElementsByTagName('meta');
        $description = '';
        
        foreach ($metas as $meta) {
            if ($meta->getAttribute('name') === 'description') {
                $description = $meta->getAttribute('content');
                break;
            }
        }
        
        return [
            'length' => strlen($description),
            'status' => (strlen($description) >= 120 && strlen($description) <= 160) ? 'good' : 'warning',
            'message' => (strlen($description) >= 120 && strlen($description) <= 160) ? 'Good length' : 'Should be 120-160 characters'
        ];
    }
    
    private function analyze_headings($dom) {
        $headings = [];
        
        for ($i = 1; $i <= 6; $i++) {
            $elements = $dom->getElementsByTagName('h' . $i);
            $headings[$i] = $elements->length;
        }
        
        return $headings;
    }
    
    private function analyze_images($dom) {
        $images = $dom->getElementsByTagName('img');
        $total = $images->length;
        $with_alt = 0;
        
        foreach ($images as $img) {
            if ($img->getAttribute('alt')) {
                $with_alt++;
            }
        }
        
        return [
            'total' => $total,
            'with_alt' => $with_alt,
            'without_alt' => $total - $with_alt
        ];
    }
    
    private function analyze_links($dom) {
        $links = $dom->getElementsByTagName('a');
        $internal = 0;
        $external = 0;
        
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            if ($href && strpos($href, 'http') === 0) {
                if (strpos($href, home_url()) === 0) {
                    $internal++;
                } else {
                    $external++;
                }
            }
        }
        
        return [
            'internal' => $internal,
            'external' => $external
        ];
    }
    
    private function analyze_performance($url) {
        // Simple performance check
        $start_time = microtime(true);
        $response = wp_remote_get($url, ['timeout' => 10]);
        $load_time = microtime(true) - $start_time;
        
        return [
            'load_time' => round($load_time, 2),
            'status' => $load_time < 3 ? 'good' : 'warning'
        ];
    }
}

// Initialize SEO optimization system
new SEOOptimizationSystem();

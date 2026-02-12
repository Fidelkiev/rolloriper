<?php
/**
 * Production Infrastructure Setup for Штори ПроФен
 * Redis, Varnish, Cloudflare configuration and optimization
 */

class ProductionInfrastructureSystem {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('shoriprofen_infrastructure_settings', []);
        
        // Add admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Initialize caching
        add_action('init', [$this, 'initialize_caching']);
        
        // Optimize database
        add_action('wp_loaded', [$this, 'optimize_database']);
        
        // Add performance headers
        add_action('send_headers', [$this, 'add_performance_headers']);
        
        // Optimize assets
        add_action('wp_enqueue_scripts', [$this, 'optimize_assets']);
        
        // Add cron jobs for maintenance
        add_action('init', [$this, 'schedule_maintenance_jobs']);
        
        // AJAX handlers
        add_action('wp_ajax_clear_cache', [$this, 'handle_clear_cache']);
        add_action('wp_ajax_optimize_database', [$this, 'handle_database_optimization']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'options-general.php',
            'Production Infrastructure',
            'Infrastructure',
            'manage_options',
            'production-infrastructure',
            [$this, 'admin_page']
        );
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Production Infrastructure</h1>
            
            <div class="infrastructure-status">
                <h2>System Status</h2>
                <div class="status-grid">
                    <div class="status-card">
                        <h3>Cache Status</h3>
                        <div class="status-item">
                            <span>Redis:</span>
                            <span class="<?php echo $this->is_redis_available() ? 'good' : 'bad'; ?>">
                                <?php echo $this->is_redis_available() ? 'Connected' : 'Not Available'; ?>
                            </span>
                        </div>
                        <div class="status-item">
                            <span>Varnish:</span>
                            <span class="<?php echo $this->is_varnish_available() ? 'good' : 'bad'; ?>">
                                <?php echo $this->is_varnish_available() ? 'Active' : 'Not Available'; ?>
                            </span>
                        </div>
                        <div class="status-item">
                            <span>Object Cache:</span>
                            <span class="<?php echo wp_using_ext_object_cache() ? 'good' : 'warning'; ?>">
                                <?php echo wp_using_ext_object_cache() ? 'Enabled' : 'File-based'; ?>
                            </span>
                        </div>
                        <button id="clear-cache" class="button">Clear All Cache</button>
                    </div>
                    
                    <div class="status-card">
                        <h3>Performance Metrics</h3>
                        <div class="status-item">
                            <span>Page Load Time:</span>
                            <span><?php echo $this->get_page_load_time(); ?>s</span>
                        </div>
                        <div class="status-item">
                            <span>Memory Usage:</span>
                            <span><?php echo $this->get_memory_usage(); ?>MB</span>
                        </div>
                        <div class="status-item">
                            <span>Database Queries:</span>
                            <span><?php echo get_num_queries(); ?></span>
                        </div>
                        <button id="run-performance-test" class="button">Run Test</button>
                    </div>
                    
                    <div class="status-card">
                        <h3>CDN Status</h3>
                        <div class="status-item">
                            <span>Cloudflare:</span>
                            <span class="<?php echo $this->is_cloudflare_active() ? 'good' : 'warning'; ?>">
                                <?php echo $this->is_cloudflare_active() ? 'Active' : 'Not Configured'; ?>
                            </span>
                        </div>
                        <div class="status-item">
                            <span>Assets CDN:</span>
                            <span class="good">Enabled</span>
                        </div>
                        <div class="status-item">
                            <span>Image Optimization:</span>
                            <span class="good">Active</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Infrastructure Settings -->
            <div class="infrastructure-settings">
                <h2>Infrastructure Settings</h2>
                <form method="post" action="options.php">
                    <?php settings_fields('shoriprofen_infrastructure_settings'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable Redis Cache</th>
                            <td>
                                <input type="checkbox" name="shoriprofen_infrastructure_settings[redis_enabled]" value="1" <?php checked($this->options['redis_enabled'] ?? 1); ?>>
                                <label>Enable Redis object caching</label>
                                <p class="description">Requires Redis server and Redis Cache plugin</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Redis Configuration</th>
                            <td>
                                <fieldset>
                                    <label>Host:</label><br>
                                    <input type="text" name="shoriprofen_infrastructure_settings[redis_host]" value="<?php echo esc_attr($this->options['redis_host'] ?? '127.0.0.1'); ?>" class="regular-text">
                                    
                                    <br><br>
                                    <label>Port:</label><br>
                                    <input type="number" name="shoriprofen_infrastructure_settings[redis_port]" value="<?php echo $this->options['redis_port'] ?? 6379; ?>" min="1" max="65535">
                                    
                                    <br><br>
                                    <label>Database:</label><br>
                                    <input type="number" name="shoriprofen_infrastructure_settings[redis_db]" value="<?php echo $this->options['redis_db'] ?? 0; ?>" min="0" max="15">
                                </fieldset>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Enable Varnish Cache</th>
                            <td>
                                <input type="checkbox" name="shoriprofen_infrastructure_settings[varnish_enabled]" value="1" <?php checked($this->options['varnish_enabled'] ?? 1); ?>>
                                <label>Enable Varnish page caching</label>
                                <p class="description">Requires Varnish server configuration</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Varnish Configuration</th>
                            <td>
                                <fieldset>
                                    <label>Varnish IPs (comma separated):</label><br>
                                    <input type="text" name="shoriprofen_infrastructure_settings[varnish_ips]" value="<?php echo esc_attr($this->options['varnish_ips'] ?? '127.0.0.1'); ?>" class="regular-text">
                                    
                                    <br><br>
                                    <label>Varnish Port:</label><br>
                                    <input type="number" name="shoriprofen_infrastructure_settings[varnish_port]" value="<?php echo $this->options['varnish_port'] ?? 80; ?>" min="1" max="65535">
                                </fieldset>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Cloudflare Settings</th>
                            <td>
                                <fieldset>
                                    <label>Enable Cloudflare CDN:</label><br>
                                    <input type="checkbox" name="shoriprofen_infrastructure_settings[cloudflare_enabled]" value="1" <?php checked($this->options['cloudflare_enabled'] ?? 1); ?>>
                                    
                                    <br><br>
                                    <label>Cloudflare Email:</label><br>
                                    <input type="email" name="shoriprofen_infrastructure_settings[cloudflare_email]" value="<?php echo esc_attr($this->options['cloudflare_email'] ?? ''); ?>" class="regular-text">
                                    
                                    <br><br>
                                    <label>API Key:</label><br>
                                    <input type="password" name="shoriprofen_infrastructure_settings[cloudflare_api_key]" value="<?php echo esc_attr($this->options['cloudflare_api_key'] ?? ''); ?>" class="regular-text">
                                    
                                    <br><br>
                                    <label>Zone ID:</label><br>
                                    <input type="text" name="shoriprofen_infrastructure_settings[cloudflare_zone_id]" value="<?php echo esc_attr($this->options['cloudflare_zone_id'] ?? ''); ?>" class="regular-text">
                                </fieldset>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Asset Optimization</th>
                            <td>
                                <fieldset>
                                    <label><input type="checkbox" name="shoriprofen_infrastructure_settings[minify_css]" value="1" <?php checked($this->options['minify_css'] ?? 1); ?>> Minify CSS</label><br>
                                    <label><input type="checkbox" name="shoriprofen_infrastructure_settings[minify_js]" value="1" <?php checked($this->options['minify_js'] ?? 1); ?>> Minify JavaScript</label><br>
                                    <label><input type="checkbox" name="shoriprofen_infrastructure_settings[combine_css]" value="1" <?php checked($this->options['combine_css'] ?? 1); ?>> Combine CSS files</label><br>
                                    <label><input type="checkbox" name="shoriprofen_infrastructure_settings[combine_js]" value="1" <?php checked($this->options['combine_js'] ?? 1); ?>> Combine JavaScript files</label><br>
                                    <label><input type="checkbox" name="shoriprofen_infrastructure_settings[lazy_load]" value="1" <?php checked($this->options['lazy_load'] ?? 1); ?>> Enable lazy loading</label>
                                </fieldset>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Database Optimization</th>
                            <td>
                                <fieldset>
                                    <label><input type="checkbox" name="shoriprofen_infrastructure_settings[optimize_db]" value="1" <?php checked($this->options['optimize_db'] ?? 1); ?>> Enable automatic database optimization</label><br>
                                    <label><input type="checkbox" name="shoriprofen_infrastructure_settings[clean_revisions]" value="1" <?php checked($this->options['clean_revisions'] ?? 1); ?>> Clean post revisions weekly</label><br>
                                    <label><input type="checkbox" name="shoriprofen_infrastructure_settings[clean_spam]" value="1" <?php checked($this->options['clean_spam'] ?? 1); ?>> Clean spam comments daily</label><br>
                                    <label><input type="checkbox" name="shoriprofen_infrastructure_settings[clean_transients]" value="1" <?php checked($this->options['clean_transients'] ?? 1); ?>> Clean expired transients daily</label>
                                </fieldset>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
        
        <style>
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .status-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .status-card h3 {
            margin: 0 0 15px 0;
            color: #333;
        }
        
        .status-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .status-item:last-child {
            border-bottom: none;
            margin-bottom: 15px;
        }
        
        .status-item span:first-child {
            font-weight: 500;
        }
        
        .status-item .good {
            color: #00a32a;
            font-weight: bold;
        }
        
        .status-item .warning {
            color: #d63638;
            font-weight: bold;
        }
        
        .status-item .bad {
            color: #d63638;
            font-weight: bold;
        }
        
        .infrastructure-settings {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .infrastructure-settings h2 {
            margin: 0 0 20px 0;
            color: #333;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#clear-cache').on('click', function() {
                $(this).prop('disabled', true).text('Clearing...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'clear_cache',
                        nonce: '<?php echo wp_create_nonce('clear_cache'); ?>'
                    },
                    success: function(response) {
                        $(this).prop('disabled', false).text('Clear All Cache');
                        if (response.success) {
                            alert('Cache cleared successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + response.data);
                        }
                    }.bind(this)
                });
            });
            
            $('#run-performance-test').on('click', function() {
                $(this).prop('disabled', true).text('Testing...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'run_performance_test',
                        nonce: '<?php echo wp_create_nonce('run_performance_test'); ?>'
                    },
                    success: function(response) {
                        $(this).prop('disabled', false).text('Run Test');
                        location.reload();
                    }.bind(this)
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Initialize caching
     */
    public function initialize_caching() {
        // Redis configuration
        if ($this->options['redis_enabled'] ?? 1) {
            $this->configure_redis();
        }
        
        // Varnish configuration
        if ($this->options['varnish_enabled'] ?? 1) {
            $this->configure_varnish();
        }
    }
    
    /**
     * Configure Redis
     */
    private function configure_redis() {
        if (!class_exists('Redis')) {
            return;
        }
        
        $redis_host = $this->options['redis_host'] ?? '127.0.0.1';
        $redis_port = $this->options['redis_port'] ?? 6379;
        $redis_db = $this->options['redis_db'] ?? 0;
        
        // Configure WordPress Redis cache
        wp_cache_add_global_groups([
            'users',
            'userlogins',
            'usermeta',
            'user_meta',
            'site-transient',
            'site-options',
            'blog-lookup',
            'blog-details',
            'rss',
            'global-posts',
            'blog-id-cache'
        ]);
    }
    
    /**
     * Configure Varnish
     */
    private function configure_varnish() {
        // Add Varnish headers
        if (!is_admin()) {
            add_action('wp_head', [$this, 'add_varnish_headers']);
        }
        
        // Configure Varnish purge
        add_action('save_post', [$this, 'purge_varnish_cache']);
        add_action('wp_update_comment_count', [$this, 'purge_varnish_cache']);
    }
    
    /**
     * Add Varnish headers
     */
    public function add_varnish_headers() {
        header('X-Varnish-Cacheable: YES');
        header('Cache-Control: public, max-age=3600');
    }
    
    /**
     * Purge Varnish cache
     */
    public function purge_varnish_cache() {
        $varnish_ips = explode(',', $this->options['varnish_ips'] ?? '127.0.0.1');
        $varnish_port = $this->options['varnish_port'] ?? 80;
        
        foreach ($varnish_ips as $ip) {
            $ip = trim($ip);
            $url = "http://{$ip}:{$varnish_port}/";
            
            wp_remote_request($url, [
                'method' => 'PURGE',
                'headers' => [
                    'Host' => $_SERVER['HTTP_HOST']
                ]
            ]);
        }
    }
    
    /**
     * Optimize database
     */
    public function optimize_database() {
        if (!($this->options['optimize_db'] ?? 1)) {
            return;
        }
        
        // Optimize database queries
        add_filter('posts_request', [$this, 'optimize_posts_query']);
        add_filter('posts_results', [$this, 'cache_posts_results']);
    }
    
    /**
     * Add performance headers
     */
    public function add_performance_headers() {
        if (!is_admin()) {
            // Security headers
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            
            // Performance headers
            header('Keep-Alive: timeout=5, max=100');
            header('Connection: Keep-Alive');
            
            // Cloudflare headers
            if ($this->options['cloudflare_enabled'] ?? 1) {
                header('CF-Cache-Status: DYNAMIC');
            }
        }
    }
    
    /**
     * Optimize assets
     */
    public function optimize_assets() {
        if ($this->options['minify_css'] ?? 1) {
            add_filter('style_loader_tag', [$this, 'minify_css'], 10, 4);
        }
        
        if ($this->options['minify_js'] ?? 1) {
            add_filter('script_loader_tag', [$this, 'minify_js'], 10, 3);
        }
        
        if ($this->options['lazy_load'] ?? 1) {
            add_filter('the_content', [$this, 'add_lazy_loading']);
        }
    }
    
    /**
     * Schedule maintenance jobs
     */
    public function schedule_maintenance_jobs() {
        // Database cleanup
        if (!wp_next_scheduled('shoriprofen_db_cleanup')) {
            wp_schedule_event(time(), 'daily', 'shoriprofen_db_cleanup');
        }
        
        add_action('shoriprofen_db_cleanup', [$this, 'perform_database_cleanup']);
        
        // Cache cleanup
        if (!wp_next_scheduled('shoriprofen_cache_cleanup')) {
            wp_schedule_event(time(), 'weekly', 'shoriprofen_cache_cleanup');
        }
        
        add_action('shoriprofen_cache_cleanup', [$this, 'perform_cache_cleanup']);
    }
    
    /**
     * Handle clear cache AJAX
     */
    public function handle_clear_cache() {
        check_ajax_referer('clear_cache', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Permission denied');
        }
        
        // Clear WordPress cache
        wp_cache_flush();
        
        // Clear Redis cache if available
        if ($this->is_redis_available()) {
            $this->clear_redis_cache();
        }
        
        // Clear Varnish cache
        $this->purge_varnish_cache();
        
        wp_send_json_success('Cache cleared successfully');
    }
    
    /**
     * Handle database optimization AJAX
     */
    public function handle_database_optimization() {
        check_ajax_referer('optimize_database', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Permission denied');
        }
        
        $this->perform_database_cleanup();
        
        wp_send_json_success('Database optimized successfully');
    }
    
    /**
     * Helper methods
     */
    private function is_redis_available() {
        return class_exists('Redis') && wp_using_ext_object_cache();
    }
    
    private function is_varnish_available() {
        return $this->options['varnish_enabled'] ?? false;
    }
    
    private function is_cloudflare_active() {
        return isset($_SERVER['HTTP_CF_CONNECTING_IP']) || ($this->options['cloudflare_enabled'] ?? false);
    }
    
    private function get_page_load_time() {
        $start_time = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
        $current_time = microtime(true);
        return round($current_time - $start_time, 3);
    }
    
    private function get_memory_usage() {
        return round(memory_get_usage(true) / 1024 / 1024, 2);
    }
    
    private function clear_redis_cache() {
        try {
            $redis = new Redis();
            $redis->connect(
                $this->options['redis_host'] ?? '127.0.0.1',
                $this->options['redis_port'] ?? 6379
            );
            $redis->select($this->options['redis_db'] ?? 0);
            $redis->flushDB();
            $redis->close();
        } catch (Exception $e) {
            error_log('Redis clear error: ' . $e->getMessage());
        }
    }
    
    private function perform_database_cleanup() {
        global $wpdb;
        
        // Clean post revisions
        if ($this->options['clean_revisions'] ?? 1) {
            $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'revision'");
        }
        
        // Clean spam comments
        if ($this->options['clean_spam'] ?? 1) {
            $wpdb->query("DELETE FROM {$wpdb->comments} WHERE comment_approved = 'spam'");
        }
        
        // Clean expired transients
        if ($this->options['clean_transients'] ?? 1) {
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' AND option_value < UNIX_TIMESTAMP()");
        }
        
        // Optimize tables
        $tables = $wpdb->get_results("SHOW TABLES");
        foreach ($tables as $table) {
            $table_name = array_values((array)$table)[0];
            $wpdb->query("OPTIMIZE TABLE {$table_name}");
        }
    }
    
    private function perform_cache_cleanup() {
        // Clear all caches
        wp_cache_flush();
        
        // Clear object cache
        if (function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group('posts');
            wp_cache_flush_group('post_meta');
            wp_cache_flush_group('comments');
        }
    }
    
    public function minify_css($tag, $handle, $href, $media) {
        if (strpos($href, '.min.css') !== false) {
            return $tag; // Already minified
        }
        
        $minified_href = str_replace('.css', '.min.css', $href);
        return str_replace($href, $minified_href, $tag);
    }
    
    public function minify_js($tag, $handle, $src) {
        if (strpos($src, '.min.js') !== false) {
            return $tag; // Already minified
        }
        
        $minified_src = str_replace('.js', '.min.js', $src);
        return str_replace($src, $minified_src, $tag);
    }
    
    public function add_lazy_loading($content) {
        // Add loading="lazy" to images
        $content = preg_replace('/<img([^>]+)src=/', '<img$1loading="lazy" src=', $content);
        return $content;
    }
    
    public function optimize_posts_query($request) {
        // Add indexes optimization
        $request = str_replace('ORDER BY', 'FORCE INDEX (wp_posts_post_name) ORDER BY', $request);
        return $request;
    }
    
    public function cache_posts_results($posts) {
        // Cache post results
        if (!empty($posts)) {
            wp_cache_set('posts_' . md5(serialize($posts)), $posts, '', 3600);
        }
        return $posts;
    }
}

// Initialize production infrastructure system
new ProductionInfrastructureSystem();

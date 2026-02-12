<?php
/**
 * Backup and Monitoring System for Штори ПроФен
 * Automated backups, performance monitoring, and security alerts
 */

class BackupMonitoringSystem {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('shoriprofen_backup_settings', []);
        
        // Add admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Schedule cron jobs
        add_action('init', [$this, 'schedule_cron_jobs']);
        
        // Cron job handlers
        add_action('shoriprofen_daily_backup', [$this, 'perform_daily_backup']);
        add_action('shoriprofen_weekly_backup', [$this, 'perform_weekly_backup']);
        add_action('shoriprofen_monitor_performance', [$this, 'monitor_performance']);
        add_action('shoriprofen_security_check', [$this, 'security_check']);
        
        // Admin dashboard widgets
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widgets']);
        
        // AJAX handlers
        add_action('wp_ajax_manual_backup', [$this, 'handle_manual_backup']);
        add_action('wp_ajax_get_backup_status', [$this, 'get_backup_status']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'options-general.php',
            'Backup & Monitoring',
            'Backup & Monitoring',
            'manage_options',
            'backup-monitoring',
            [$this, 'admin_page']
        );
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Backup & Monitoring</h1>
            
            <div class="backup-monitoring-grid">
                <!-- Backup Status -->
                <div class="status-card">
                    <h3>Backup Status</h3>
                    <div class="status-item">
                        <span>Last Daily Backup:</span>
                        <span><?php echo $this->get_last_backup_time('daily'); ?></span>
                    </div>
                    <div class="status-item">
                        <span>Last Weekly Backup:</span>
                        <span><?php echo $this->get_last_backup_time('weekly'); ?></span>
                    </div>
                    <div class="status-item">
                        <span>Total Backups:</span>
                        <span><?php echo $this->get_backup_count(); ?></span>
                    </div>
                    <button id="manual-backup" class="button button-primary">Create Backup Now</button>
                </div>
                
                <!-- Performance Status -->
                <div class="status-card">
                    <h3>Performance Status</h3>
                    <div class="status-item">
                        <span>Page Load Time:</span>
                        <span><?php echo $this->get_page_load_time(); ?>s</span>
                    </div>
                    <div class="status-item">
                        <span>Memory Usage:</span>
                        <span><?php echo $this->get_memory_usage(); ?>MB</span>
                    </div>
                    <div class="status-item">
                        <span>Database Size:</span>
                        <span><?php echo $this->get_database_size(); ?>MB</span>
                    </div>
                    <button id="run-performance-check" class="button">Run Performance Check</button>
                </div>
                
                <!-- Security Status -->
                <div class="status-card">
                    <h3>Security Status</h3>
                    <div class="status-item">
                        <span>WordPress Version:</span>
                        <span><?php echo get_bloginfo('version'); ?></span>
                    </div>
                    <div class="status-item">
                        <span>Last Security Check:</span>
                        <span><?php echo $this->get_last_security_check(); ?></span>
                    </div>
                    <div class="status-item">
                        <span>Security Issues:</span>
                        <span class="<?php echo $this->get_security_issues_count() > 0 ? 'warning' : 'ok'; ?>">
                            <?php echo $this->get_security_issues_count(); ?> found
                        </span>
                    </div>
                    <button id="run-security-check" class="button">Run Security Check</button>
                </div>
            </div>
            
            <!-- Backup Settings -->
            <div class="settings-section">
                <h2>Backup Settings</h2>
                <form method="post" action="options.php">
                    <?php settings_fields('shoriprofen_backup_settings'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable Daily Backups</th>
                            <td>
                                <input type="checkbox" name="shoriprofen_backup_settings[daily_enabled]" value="1" <?php checked($this->options['daily_enabled'] ?? 1); ?>>
                                <label>Enable automatic daily backups</label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Enable Weekly Backups</th>
                            <td>
                                <input type="checkbox" name="shoriprofen_backup_settings[weekly_enabled]" value="1" <?php checked($this->options['weekly_enabled'] ?? 1); ?>>
                                <label>Enable automatic weekly backups</label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Retention Days</th>
                            <td>
                                <input type="number" name="shoriprofen_backup_settings[retention_days]" value="<?php echo $this->options['retention_days'] ?? 30; ?>" min="1" max="365">
                                <p class="description">Number of days to keep backups</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Backup Location</th>
                            <td>
                                <select name="shoriprofen_backup_settings[location]">
                                    <option value="local" <?php selected($this->options['location'] ?? 'local', 'local'); ?>>Local Storage</option>
                                    <option value="cloud" <?php selected($this->options['location'] ?? 'local', 'cloud'); ?>>Cloud Storage</option>
                                    <option value="both" <?php selected($this->options['location'] ?? 'local', 'both'); ?>>Both</option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Email Notifications</th>
                            <td>
                                <input type="checkbox" name="shoriprofen_backup_settings[email_notifications]" value="1" <?php checked($this->options['email_notifications'] ?? 1); ?>>
                                <label>Send email notifications on backup completion</label>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button(); ?>
                </form>
            </div>
            
            <!-- Monitoring Settings -->
            <div class="settings-section">
                <h2>Monitoring Settings</h2>
                <form method="post" action="options.php">
                    <?php settings_fields('shoriprofen_monitoring_settings'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable Performance Monitoring</th>
                            <td>
                                <input type="checkbox" name="shoriprofen_backup_settings[performance_monitoring]" value="1" <?php checked($this->options['performance_monitoring'] ?? 1); ?>>
                                <label>Enable automatic performance monitoring</label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Enable Security Monitoring</th>
                            <td>
                                <input type="checkbox" name="shoriprofen_backup_settings[security_monitoring]" value="1" <?php checked($this->options['security_monitoring'] ?? 1); ?>>
                                <label>Enable automatic security checks</label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Alert Email</th>
                            <td>
                                <input type="email" name="shoriprofen_backup_settings[alert_email]" value="<?php echo $this->options['alert_email'] ?? get_option('admin_email'); ?>" class="regular-text">
                                <p class="description">Email address for alerts and notifications</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Performance Threshold</th>
                            <td>
                                <input type="number" name="shoriprofen_backup_settings[performance_threshold]" value="<?php echo $this->options['performance_threshold'] ?? 3; ?>" min="1" max="10" step="0.1">
                                <p class="description">Page load time threshold in seconds (alert if exceeded)</p>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
        
        <style>
        .backup-monitoring-grid {
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
        
        .status-item .warning {
            color: #d63638;
            font-weight: bold;
        }
        
        .status-item .ok {
            color: #00a32a;
            font-weight: bold;
        }
        
        .settings-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .settings-section h2 {
            margin: 0 0 20px 0;
            color: #333;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#manual-backup').on('click', function() {
                $(this).prop('disabled', true).text('Creating Backup...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'manual_backup',
                        nonce: '<?php echo wp_create_nonce('manual_backup'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $(this).text('Backup Created Successfully!');
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            $(this).prop('disabled', false).text('Create Backup Now');
                            alert('Error: ' + response.data);
                        }
                    }.bind(this),
                    error: function() {
                        $(this).prop('disabled', false).text('Create Backup Now');
                        alert('AJAX error occurred');
                    }.bind(this)
                });
            });
            
            $('#run-performance-check').on('click', function() {
                $(this).prop('disabled', true).text('Running Check...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'run_performance_check',
                        nonce: '<?php echo wp_create_nonce('run_performance_check'); ?>'
                    },
                    success: function(response) {
                        $(this).prop('disabled', false).text('Run Performance Check');
                        location.reload();
                    }.bind(this)
                });
            });
            
            $('#run-security-check').on('click', function() {
                $(this).prop('disabled', true).text('Running Check...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'run_security_check',
                        nonce: '<?php echo wp_create_nonce('run_security_check'); ?>'
                    },
                    success: function(response) {
                        $(this).prop('disabled', false).text('Run Security Check');
                        location.reload();
                    }.bind(this)
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Schedule cron jobs
     */
    public function schedule_cron_jobs() {
        // Daily backup at 2 AM
        if (!wp_next_scheduled('shoriprofen_daily_backup')) {
            wp_schedule_event(strtotime('2:00:00'), 'daily', 'shoriprofen_daily_backup');
        }
        
        // Weekly backup on Sunday at 3 AM
        if (!wp_next_scheduled('shoriprofen_weekly_backup')) {
            wp_schedule_event(strtotime('Sunday 3:00:00'), 'weekly', 'shoriprofen_weekly_backup');
        }
        
        // Performance monitoring every hour
        if (!wp_next_scheduled('shoriprofen_monitor_performance')) {
            wp_schedule_event(time(), 'hourly', 'shoriprofen_monitor_performance');
        }
        
        // Security check daily at 4 AM
        if (!wp_next_scheduled('shoriprofen_security_check')) {
            wp_schedule_event(strtotime('4:00:00'), 'daily', 'shoriprofen_security_check');
        }
    }
    
    /**
     * Perform daily backup
     */
    public function perform_daily_backup() {
        if (!($this->options['daily_enabled'] ?? 1)) {
            return;
        }
        
        $this->create_backup('daily');
    }
    
    /**
     * Perform weekly backup
     */
    public function perform_weekly_backup() {
        if (!($this->options['weekly_enabled'] ?? 1)) {
            return;
        }
        
        $this->create_backup('weekly');
    }
    
    /**
     * Create backup
     */
    private function create_backup($type) {
        $backup_dir = WP_CONTENT_DIR . '/backups';
        if (!file_exists($backup_dir)) {
            wp_mkdir_p($backup_dir);
        }
        
        $timestamp = current_time('Y-m-d_H-i-s');
        $filename = "backup_{$type}_{$timestamp}.zip";
        $filepath = $backup_dir . '/' . $filename;
        
        // Create backup using WordPress export functionality
        $this->create_full_backup($filepath);
        
        // Save backup record
        $backup_record = [
            'type' => $type,
            'filename' => $filename,
            'filepath' => $filepath,
            'size' => filesize($filepath),
            'created_at' => current_time('mysql'),
            'status' => 'completed'
        ];
        
        $backups = get_option('shoriprofen_backups', []);
        $backups[] = $backup_record;
        update_option('shoriprofen_backups', $backups);
        
        // Clean old backups
        $this->cleanup_old_backups();
        
        // Send notification
        if ($this->options['email_notifications'] ?? 1) {
            $this->send_backup_notification($backup_record);
        }
    }
    
    /**
     * Create full backup
     */
    private function create_full_backup($filepath) {
        $zip = new ZipArchive();
        
        if ($zip->open($filepath, ZipArchive::CREATE) === TRUE) {
            // Add WordPress files
            $this->add_files_to_zip($zip, ABSPATH, '', ['wp-content/cache', 'wp-content/backups']);
            
            // Add database export
            $db_file = WP_CONTENT_DIR . '/database.sql';
            $this->export_database($db_file);
            $zip->addFile($db_file, 'database.sql');
            unlink($db_file);
            
            $zip->close();
        }
    }
    
    /**
     * Add files to zip
     */
    private function add_files_to_zip($zip, $source, $base_path, $exclude = []) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($files as $file) {
            $file_path = $file->getRealPath();
            $relative_path = str_replace($source, $base_path, $file_path);
            
            // Skip excluded directories
            foreach ($exclude as $exclude_dir) {
                if (strpos($file_path, $exclude_dir) !== false) {
                    continue 2;
                }
            }
            
            if (is_dir($file_path)) {
                $zip->addEmptyDir($relative_path);
            } else {
                $zip->addFile($file_path, $relative_path);
            }
        }
    }
    
    /**
     * Export database
     */
    private function export_database($filepath) {
        global $wpdb;
        
        $tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
        $sql = '';
        
        foreach ($tables as $table) {
            $table_name = $table[0];
            $create_table = $wpdb->get_results("SHOW CREATE TABLE `{$table_name}`", ARRAY_A);
            $sql .= $create_table[0]['Create Table'] . ";\n\n";
            
            $rows = $wpdb->get_results("SELECT * FROM `{$table_name}`", ARRAY_A);
            foreach ($rows as $row) {
                $values = array_map([$wpdb, '_real_escape'], array_values($row));
                $sql .= "INSERT INTO `{$table_name}` VALUES ('" . implode("','", $values) . "');\n";
            }
            $sql .= "\n";
        }
        
        file_put_contents($filepath, $sql);
    }
    
    /**
     * Clean old backups
     */
    private function cleanup_old_backups() {
        $retention_days = $this->options['retention_days'] ?? 30;
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
        
        $backups = get_option('shoriprofen_backups', []);
        $updated_backups = [];
        
        foreach ($backups as $backup) {
            if ($backup['created_at'] > $cutoff_date) {
                $updated_backups[] = $backup;
            } else {
                // Delete old backup file
                if (file_exists($backup['filepath'])) {
                    unlink($backup['filepath']);
                }
            }
        }
        
        update_option('shoriprofen_backups', $updated_backups);
    }
    
    /**
     * Monitor performance
     */
    public function monitor_performance() {
        if (!($this->options['performance_monitoring'] ?? 1)) {
            return;
        }
        
        $metrics = [
            'page_load_time' => $this->measure_page_load_time(),
            'memory_usage' => memory_get_usage(true) / 1024 / 1024,
            'database_size' => $this->get_database_size(),
            'timestamp' => current_time('mysql')
        ];
        
        // Save metrics
        $performance_data = get_option('shoriprofen_performance_data', []);
        $performance_data[] = $metrics;
        
        // Keep only last 1000 records
        if (count($performance_data) > 1000) {
            $performance_data = array_slice($performance_data, -1000);
        }
        
        update_option('shoriprofen_performance_data', $performance_data);
        
        // Check thresholds
        $threshold = $this->options['performance_threshold'] ?? 3;
        if ($metrics['page_load_time'] > $threshold) {
            $this->send_performance_alert($metrics);
        }
    }
    
    /**
     * Security check
     */
    public function security_check() {
        if (!($this->options['security_monitoring'] ?? 1)) {
            return;
        }
        
        $issues = [];
        
        // Check WordPress version
        if (!function_exists('get_core_updates')) {
            require_once ABSPATH . 'wp-admin/includes/update.php';
        }
        $updates = get_core_updates();
        if (!empty($updates) && $updates[0]->response == 'upgrade') {
            $issues[] = 'WordPress core update available: ' . $updates[0]->version;
        }
        
        // Check plugin updates
        $plugin_updates = get_plugin_updates();
        if (!empty($plugin_updates)) {
            $issues[] = count($plugin_updates) . ' plugin updates available';
        }
        
        // Check file permissions
        if (!is_writable(WP_CONTENT_DIR)) {
            $issues[] = 'wp-content directory is not writable';
        }
        
        // Check debug mode
        if (WP_DEBUG && WP_DEBUG_DISPLAY) {
            $issues[] = 'Debug mode is enabled and displaying errors';
        }
        
        // Save security check results
        $security_data = [
            'issues' => $issues,
            'timestamp' => current_time('mysql')
        ];
        
        update_option('shoriprofen_last_security_check', $security_data);
        
        // Send alert if issues found
        if (!empty($issues)) {
            $this->send_security_alert($issues);
        }
    }
    
    /**
     * Handle manual backup
     */
    public function handle_manual_backup() {
        check_ajax_referer('manual_backup', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Permission denied');
        }
        
        $this->create_backup('manual');
        
        wp_send_json_success('Backup created successfully');
    }
    
    /**
     * Get backup status
     */
    public function get_backup_status() {
        check_ajax_referer('get_backup_status', 'nonce');
        
        $backups = get_option('shoriprofen_backups', []);
        $last_backup = end($backups);
        
        wp_send_json_success([
            'last_backup' => $last_backup,
            'total_backups' => count($backups)
        ]);
    }
    
    /**
     * Helper methods
     */
    private function get_last_backup_time($type) {
        $backups = get_option('shoriprofen_backups', []);
        $type_backups = array_filter($backups, function($backup) use ($type) {
            return $backup['type'] == $type;
        });
        
        if (empty($type_backups)) {
            return 'Never';
        }
        
        $last_backup = end($type_backups);
        return date('M j, Y H:i', strtotime($last_backup['created_at']));
    }
    
    private function get_backup_count() {
        $backups = get_option('shoriprofen_backups', []);
        return count($backups);
    }
    
    private function get_page_load_time() {
        $performance_data = get_option('shoriprofen_performance_data', []);
        if (empty($performance_data)) {
            return 'N/A';
        }
        
        $last_metric = end($performance_data);
        return round($last_metric['page_load_time'], 2);
    }
    
    private function get_memory_usage() {
        return round(memory_get_usage(true) / 1024 / 1024, 2);
    }
    
    private function get_database_size() {
        global $wpdb;
        $size = $wpdb->get_var("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
        return $size ?: 0;
    }
    
    private function get_last_security_check() {
        $security_data = get_option('shoriprofen_last_security_check');
        if (!$security_data) {
            return 'Never';
        }
        
        return date('M j, Y H:i', strtotime($security_data['timestamp']));
    }
    
    private function get_security_issues_count() {
        $security_data = get_option('shoriprofen_last_security_check');
        if (!$security_data) {
            return 0;
        }
        
        return count($security_data['issues']);
    }
    
    private function measure_page_load_time() {
        $start_time = microtime(true);
        
        // Make a simple request to measure load time
        $response = wp_remote_get(home_url(), ['timeout' => 10]);
        
        $end_time = microtime(true);
        return $end_time - $start_time;
    }
    
    private function send_backup_notification($backup) {
        $to = $this->options['alert_email'] ?? get_option('admin_email');
        $subject = "Backup Completed: {$backup['type']} backup created";
        
        $message = <<<HTML
<p>A {$backup['type']} backup has been successfully created.</p>
<p><strong>Details:</strong></p>
<ul>
    <li>Filename: {$backup['filename']}</li>
    <li>Size: {$this->format_bytes($backup['size'])}</li>
    <li>Created: {$backup['created_at']}</li>
</ul>
<p>You can access backups from the WordPress admin panel.</p>
HTML;
        
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        wp_mail($to, $subject, $message, $headers);
    }
    
    private function send_performance_alert($metrics) {
        $to = $this->options['alert_email'] ?? get_option('admin_email');
        $subject = 'Performance Alert: Slow page load detected';
        
        $message = <<<HTML
<p>A performance issue has been detected on your site.</p>
<p><strong>Metrics:</strong></p>
<ul>
    <li>Page Load Time: {$metrics['page_load_time']}s</li>
    <li>Memory Usage: {$metrics['memory_usage']}MB</li>
    <li>Database Size: {$metrics['database_size']}MB</li>
</ul>
<p>Please investigate and optimize your site performance.</p>
HTML;
        
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        wp_mail($to, $subject, $message, $headers);
    }
    
    private function send_security_alert($issues) {
        $to = $this->options['alert_email'] ?? get_option('admin_email');
        $subject = 'Security Alert: Issues detected';
        
        $issues_list = '<ul><li>' . implode('</li><li>', $issues) . '</li></ul>';
        
        $message = <<<HTML
<p>Security issues have been detected on your site.</p>
<p><strong>Issues:</strong></p>
{$issues_list}
<p>Please address these issues promptly to maintain site security.</p>
HTML;
        
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        wp_mail($to, $subject, $message, $headers);
    }
    
    private function format_bytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Add dashboard widgets
     */
    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'shoriprofen_backup_status',
            'Backup Status',
            [$this, 'dashboard_backup_widget']
        );
        
        wp_add_dashboard_widget(
            'shoriprofen_performance_status',
            'Performance Status',
            [$this, 'dashboard_performance_widget']
        );
    }
    
    public function dashboard_backup_widget() {
        $backups = get_option('shoriprofen_backups', []);
        $last_backup = end($backups);
        
        if ($last_backup) {
            echo '<p><strong>Last Backup:</strong> ' . date('M j, Y H:i', strtotime($last_backup['created_at'])) . '</p>';
            echo '<p><strong>Type:</strong> ' . ucfirst($last_backup['type']) . '</p>';
            echo '<p><strong>Size:</strong> ' . $this->format_bytes($last_backup['size']) . '</p>';
        } else {
            echo '<p>No backups found.</p>';
        }
        
        echo '<p><a href="' . admin_url('options-general.php?page=backup-monitoring') . '">View All Backups</a></p>';
    }
    
    public function dashboard_performance_widget() {
        $performance_data = get_option('shoriprofen_performance_data', []);
        $last_metric = end($performance_data);
        
        if ($last_metric) {
            echo '<p><strong>Page Load Time:</strong> ' . round($last_metric['page_load_time'], 2) . 's</p>';
            echo '<p><strong>Memory Usage:</strong> ' . round($last_metric['memory_usage'], 2) . 'MB</p>';
            echo '<p><strong>Database Size:</strong> ' . $last_metric['database_size'] . 'MB</p>';
        } else {
            echo '<p>No performance data available.</p>';
        }
        
        echo '<p><a href="' . admin_url('options-general.php?page=backup-monitoring') . '">View Performance Details</a></p>';
    }
}

// Initialize backup and monitoring system
new BackupMonitoringSystem();

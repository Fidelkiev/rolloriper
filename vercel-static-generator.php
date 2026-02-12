<?php
/**
 * Vercel Static Generator for Штори ПроФен
 * Converts WordPress site to static files for Vercel deployment
 */

class VercelStaticGenerator {
    
    private $output_dir;
    private $base_url;
    
    public function __construct() {
        $this->output_dir = __DIR__ . '/static-build';
        $this->base_url = 'https://shoriprofen.vercel.app';
        
        // Add admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // AJAX handler for generation
        add_action('wp_ajax_generate_static', [$this, 'handle_static_generation']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'options-general.php',
            'Vercel Static Generator',
            'Vercel Generator',
            'manage_options',
            'vercel-static-generator',
            [$this, 'admin_page']
        );
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Vercel Static Generator</h1>
            
            <div class="generator-info">
                <div class="info-card">
                    <h3>📦 Статическая генерация для Vercel</h3>
                    <p>Этот инструмент преобразует ваш WordPress сайт в статические файлы, готовые для деплоя на Vercel.</p>
                    
                    <div class="warning">
                        <strong>⚠️ Важно:</strong> Динамические функции (формы, корзина, админ-панель) не будут работать в статической версии.
                    </div>
                </div>
                
                <div class="info-card">
                    <h3>🚀 Что будет сгенерировано:</h3>
                    <ul>
                        <li>✅ Все страницы и посты</li>
                        <li>✅ Визуализации и галереи</li>
                        <li>✅ AR функционал (клиентская часть)</li>
                        <li>✅ Конфигуратор</li>
                        <li>✅ SEO оптимизация</li>
                        <li>✅ Многоязычность</li>
                    </ul>
                </div>
                
                <div class="info-card">
                    <h3>❌ Что не будет работать:</h3>
                    <ul>
                        <li>❌ Формы обратной связи</li>
                        <li>❌ Email подписка</li>
                        <li>❌ UGC загрузка</li>
                        <li>❌ Админ-панель</li>
                        <li>❌ Динамический контент</li>
                    </ul>
                </div>
            </div>
            
            <div class="generator-controls">
                <button id="generate-static" class="button button-primary">🚀 Сгенерировать статическую версию</button>
                <button id="download-static" class="button" style="display:none;">📥 Скачать архив</button>
                <div id="progress-bar" class="progress-bar" style="display:none;">
                    <div class="progress-fill"></div>
                    <span class="progress-text">0%</span>
                </div>
            </div>
            
            <div id="generation-log" class="generation-log" style="display:none;"></div>
        </div>
        
        <style>
        .generator-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .info-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .info-card h3 {
            margin: 0 0 15px 0;
            color: #333;
        }
        
        .info-card ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .info-card li {
            margin-bottom: 5px;
        }
        
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 10px;
            margin: 15px 0;
        }
        
        .generator-controls {
            text-align: center;
            margin: 30px 0;
        }
        
        .progress-bar {
            width: 100%;
            max-width: 500px;
            height: 30px;
            background: #f0f0f0;
            border-radius: 15px;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0073aa, #00a32a);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #333;
            font-weight: bold;
        }
        
        .generation-log {
            background: #1e1e1e;
            color: #fff;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
            margin-top: 20px;
        }
        
        .log-entry {
            margin-bottom: 5px;
            padding: 2px 0;
        }
        
        .log-entry.success {
            color: #00a32a;
        }
        
        .log-entry.error {
            color: #ff6b6b;
        }
        
        .log-entry.info {
            color: #74c0fc;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#generate-static').on('click', function() {
                $(this).prop('disabled', true).text('⏳ Генерация...');
                $('#progress-bar').show();
                $('#generation-log').show().empty();
                
                function updateProgress(percent, message, type) {
                    $('.progress-fill').css('width', percent + '%');
                    $('.progress-text').text(percent + '%');
                    
                    const logEntry = $('<div class="log-entry ' + type + '">' + message + '</div>');
                    $('#generation-log').append(logEntry);
                    $('#generation-log').scrollTop($('#generation-log')[0].scrollHeight);
                }
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'generate_static',
                        nonce: '<?php echo wp_create_nonce('generate_static'); ?>'
                    },
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percentComplete = Math.round((e.loaded / e.total) * 100);
                                updateProgress(percentComplete, 'Обработка файлов...', 'info');
                            }
                        });
                        return xhr;
                    },
                    success: function(response) {
                        if (response.success) {
                            updateProgress(100, '✅ Генерация завершена!', 'success');
                            $('#download-static').show();
                            window.location.href = response.data.download_url;
                        } else {
                            updateProgress(0, '❌ Ошибка: ' + response.data, 'error');
                        }
                        $('#generate-static').prop('disabled', false).text('🚀 Сгенерировать статическую версию');
                    },
                    error: function() {
                        updateProgress(0, '❌ Произошла ошибка при генерации', 'error');
                        $('#generate-static').prop('disabled', false).text('🚀 Сгенерировать статическую версию');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Handle static generation
     */
    public function handle_static_generation() {
        check_ajax_referer('generate_static', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        try {
            $this->generate_static_site();
            
            // Create ZIP archive
            $zip_path = $this->create_zip_archive();
            
            wp_send_json_success([
                'message' => 'Static site generated successfully',
                'download_url' => $this->get_download_url($zip_path)
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error('Generation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate static site
     */
    private function generate_static_site() {
        // Clean output directory
        $this->clean_output_directory();
        
        // Generate pages
        $this->generate_pages();
        
        // Generate posts
        $this->generate_posts();
        
        // Generate visualizations
        $this->generate_visualizations();
        
        // Generate partners
        $this->generate_partners();
        
        // Copy assets
        $this->copy_assets();
        
        // Generate config files
        $this->generate_config_files();
    }
    
    /**
     * Clean output directory
     */
    private function clean_output_directory() {
        if (file_exists($this->output_dir)) {
            $this->remove_directory($this->output_dir);
        }
        mkdir($this->output_dir, 0755, true);
    }
    
    /**
     * Generate pages
     */
    private function generate_pages() {
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ]);
        
        foreach ($pages as $page) {
            $this->generate_page($page);
        }
    }
    
    /**
     * Generate single page
     */
    private function generate_page($page) {
        $content = $this->get_rendered_content($page->ID);
        $slug = $page->post_name === 'home' ? 'index' : $page->post_name;
        
        $file_path = $this->output_dir . '/' . $slug . '.html';
        file_put_contents($file_path, $content);
    }
    
    /**
     * Generate posts
     */
    private function generate_posts() {
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ]);
        
        // Create blog directory
        $blog_dir = $this->output_dir . '/blog';
        mkdir($blog_dir, 0755, true);
        
        foreach ($posts as $post) {
            $content = $this->get_rendered_content($post->ID);
            $file_path = $blog_dir . '/' . $post->post_name . '.html';
            file_put_contents($file_path, $content);
        }
        
        // Generate blog index
        $this->generate_blog_index($posts);
    }
    
    /**
     * Generate visualizations
     */
    private function generate_visualizations() {
        $visualizations = get_posts([
            'post_type' => 'visualization',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ]);
        
        // Create visualizations directory
        $viz_dir = $this->output_dir . '/visualizations';
        mkdir($viz_dir, 0755, true);
        
        foreach ($visualizations as $viz) {
            $content = $this->get_rendered_content($viz->ID);
            $file_path = $viz_dir . '/' . $viz->post_name . '.html';
            file_put_contents($file_path, $content);
        }
        
        // Generate visualizations index
        $this->generate_visualizations_index($visualizations);
    }
    
    /**
     * Generate partners
     */
    private function generate_partners() {
        $partners = get_posts([
            'post_type' => 'partner',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ]);
        
        // Create partners directory
        $partners_dir = $this->output_dir . '/partners';
        mkdir($partners_dir, 0755, true);
        
        foreach ($partners as $partner) {
            $content = $this->get_rendered_content($partner->ID);
            $file_path = $partners_dir . '/' . $partner->post_name . '.html';
            file_put_contents($file_path, $content);
        }
    }
    
    /**
     * Copy assets
     */
    private function copy_assets() {
        $theme_dir = get_template_directory();
        $assets_dir = $this->output_dir . '/assets';
        
        // Copy CSS
        $css_source = $theme_dir . '/css';
        if (file_exists($css_source)) {
            $this->copy_directory($css_source, $assets_dir . '/css');
        }
        
        // Copy JS
        $js_source = $theme_dir . '/js';
        if (file_exists($js_source)) {
            $this->copy_directory($js_source, $assets_dir . '/js');
        }
        
        // Copy images
        $images_source = $theme_dir . '/images';
        if (file_exists($images_source)) {
            $this->copy_directory($images_source, $assets_dir . '/images');
        }
        
        // Copy uploads
        $uploads_dir = wp_upload_dir();
        if (file_exists($uploads_dir['basedir'])) {
            $this->copy_directory($uploads_dir['basedir'], $assets_dir . '/uploads');
        }
    }
    
    /**
     * Generate config files
     */
    private function generate_config_files() {
        // Generate vercel.json
        $vercel_config = [
            'version' => 2,
            'builds' => [
                [
                    'src' => '**/*.html',
                    'use' => '@vercel/static'
                ]
            ],
            'routes' => [
                [
                    'src' => '/(.*)',
                    'dest' => '/$1.html'
                ]
            ],
            'headers' => [
                [
                    'source' => '/assets/(.*)',
                    'headers' => [
                        ['key' => 'Cache-Control', 'value' => 'public, max-age=31536000, immutable']
                    ]
                ]
            ]
        ];
        
        file_put_contents(
            $this->output_dir . '/vercel.json',
            json_encode($vercel_config, JSON_PRETTY_PRINT)
        );
        
        // Generate .htaccess equivalent for Vercel
        $redirects = [];
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ]);
        
        foreach ($pages as $page) {
            if ($page->post_name !== 'home') {
                $redirects[] = "/{$page->post_name} /{$page->post_name}.html 200";
            }
        }
        
        file_put_contents(
            $this->output_dir . '/_redirects',
            implode("\n", $redirects)
        );
    }
    
    /**
     * Get rendered content
     */
    private function get_rendered_content($post_id) {
        global $post;
        $post = get_post($post_id);
        setup_postdata($post);
        
        ob_start();
        include get_template_directory() . '/page.php';
        $content = ob_get_clean();
        
        wp_reset_postdata();
        
        // Replace dynamic URLs with static ones
        $content = str_replace(site_url(), $this->base_url, $content);
        $content = str_replace(home_url(), $this->base_url, $content);
        
        return $content;
    }
    
    /**
     * Create ZIP archive
     */
    private function create_zip_archive() {
        $zip = new ZipArchive();
        $zip_path = __DIR__ . '/shoriprofen-static.zip';
        
        if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $this->add_files_to_zip($zip, $this->output_dir, '');
            $zip->close();
        }
        
        return $zip_path;
    }
    
    /**
     * Add files to ZIP
     */
    private function add_files_to_zip($zip, $source, $base_path) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($files as $file) {
            $file_path = $file->getRealPath();
            $relative_path = str_replace($source, $base_path, $file_path);
            
            if (is_dir($file_path)) {
                $zip->addEmptyDir($relative_path);
            } else {
                $zip->addFile($file_path, $relative_path);
            }
        }
    }
    
    /**
     * Get download URL
     */
    private function get_download_url($zip_path) {
        return content_url('plugins/shoriprofen-static/shoriprofen-static.zip');
    }
    
    /**
     * Helper methods
     */
    private function remove_directory($dir) {
        if (!file_exists($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->remove_directory($path) : unlink($path);
        }
        rmdir($dir);
    }
    
    private function copy_directory($source, $dest) {
        if (!file_exists($dest)) {
            mkdir($dest, 0755, true);
        }
        
        $files = scandir($source);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $source_path = $source . '/' . $file;
                $dest_path = $dest . '/' . $file;
                
                if (is_dir($source_path)) {
                    $this->copy_directory($source_path, $dest_path);
                } else {
                    copy($source_path, $dest_path);
                }
            }
        }
    }
    
    private function generate_blog_index($posts) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Блог - Штори ПроФен</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body>
            <h1>Блог</h1>
            <?php foreach ($posts as $post): ?>
                <article>
                    <h2><a href="<?php echo $this->base_url; ?>/blog/<?php echo $post->post_name; ?>.html"><?php echo $post->post_title; ?></a></h2>
                    <div><?php echo wp_trim_words($post->post_content, 30); ?></div>
                </article>
            <?php endforeach; ?>
        </body>
        </html>
        <?php
        $content = ob_get_clean();
        file_put_contents($this->output_dir . '/blog/index.html', $content);
    }
    
    private function generate_visualizations_index($visualizations) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Визуализации - Штори ПроФен</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body>
            <h1>Визуализации</h1>
            <div class="grid">
                <?php foreach ($visualizations as $viz): ?>
                    <div class="item">
                        <?php if (has_post_thumbnail($viz->ID)): ?>
                            <img src="<?php echo get_the_post_thumbnail_url($viz->ID, 'medium'); ?>" alt="<?php echo $viz->post_title; ?>">
                        <?php endif; ?>
                        <h3><a href="<?php echo $this->base_url; ?>/visualizations/<?php echo $viz->post_name; ?>.html"><?php echo $viz->post_title; ?></a></h3>
                    </div>
                <?php endforeach; ?>
            </div>
        </body>
        </html>
        <?php
        $content = ob_get_clean();
        file_put_contents($this->output_dir . '/visualizations/index.html', $content);
    }
}

// Initialize Vercel static generator
new VercelStaticGenerator();

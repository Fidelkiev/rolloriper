<?php
/**
 * Multilingual Setup with Polylang for Штори ПроФен
 * Ukrainian, Russian, and English language support
 */

class MultilingualSetupSystem {
    
    private $options;
    private $languages = [
        'uk' => [
            'name' => 'Ukrainian',
            'slug' => 'uk',
            'locale' => 'uk_UA',
            'flag' => '🇺🇦',
            'default' => true
        ],
        'ru' => [
            'name' => 'Russian',
            'slug' => 'ru',
            'locale' => 'ru_RU',
            'flag' => '🇷🇺'
        ],
        'en' => [
            'name' => 'English',
            'slug' => 'en',
            'locale' => 'en_US',
            'flag' => '🇬🇧'
        ]
    ];
    
    public function __construct() {
        $this->options = get_option('shoriprofen_multilingual_settings', []);
        
        // Add admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Initialize Polylang settings
        add_action('init', [$this, 'initialize_polylang']);
        
        // Add language switcher
        add_action('wp_footer', [$this, 'add_language_switcher']);
        
        // Translate content
        add_filter('the_title', [$this, 'translate_title'], 10, 2);
        add_filter('the_content', [$this, 'translate_content']);
        add_filter('get_the_excerpt', [$this, 'translate_excerpt']);
        
        // Translate custom fields
        add_filter('carbon_get_post_meta', [$this, 'translate_meta_value'], 10, 3);
        
        // Add language-specific CSS
        add_action('wp_head', [$this, 'add_language_css']);
        
        // AJAX handlers
        add_action('wp_ajax_translate_content', [$this, 'handle_translation']);
        add_action('wp_ajax_nopriv_translate_content', [$this, 'handle_translation']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'options-general.php',
            'Multilingual Setup',
            'Multilingual',
            'manage_options',
            'multilingual-setup',
            [$this, 'admin_page']
        );
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Multilingual Setup</h1>
            
            <div class="language-status">
                <h2>Language Status</h2>
                <div class="language-grid">
                    <?php foreach ($this->languages as $code => $lang): ?>
                        <div class="language-card <?php echo $this->is_language_active($code) ? 'active' : 'inactive'; ?>">
                            <div class="language-header">
                                <span class="flag"><?php echo $lang['flag']; ?></span>
                                <h3><?php echo $lang['name']; ?></h3>
                                <?php if ($lang['default'] ?? false): ?>
                                    <span class="default-badge">Default</span>
                                <?php endif; ?>
                            </div>
                            <div class="language-stats">
                                <div class="stat">
                                    <span class="label">Posts:</span>
                                    <span class="value"><?php echo $this->get_translated_posts_count($code); ?></span>
                                </div>
                                <div class="stat">
                                    <span class="label">Pages:</span>
                                    <span class="value"><?php echo $this->get_translated_pages_count($code); ?></span>
                                </div>
                                <div class="stat">
                                    <span class="label">Products:</span>
                                    <span class="value"><?php echo $this->get_translated_products_count($code); ?></span>
                                </div>
                            </div>
                            <div class="language-actions">
                                <?php if (!$this->is_language_active($code)): ?>
                                    <button class="button button-primary" onclick="activateLanguage('<?php echo $code; ?>')">Activate</button>
                                <?php else: ?>
                                    <button class="button" onclick="manageTranslations('<?php echo $code; ?>')">Manage</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Translation Tools -->
            <div class="translation-tools">
                <h2>Translation Tools</h2>
                <div class="tools-grid">
                    <div class="tool-card">
                        <h3>Auto Translate</h3>
                        <p>Automatically translate content using AI</p>
                        <button id="auto-translate" class="button button-primary">Start Auto Translation</button>
                    </div>
                    
                    <div class="tool-card">
                        <h3>Import/Export</h3>
                        <p>Import translations from CSV or export for editing</p>
                        <div class="tool-actions">
                            <button id="import-translations" class="button">Import</button>
                            <button id="export-translations" class="button">Export</button>
                        </div>
                    </div>
                    
                    <div class="tool-card">
                        <h3>Translation Status</h3>
                        <p>View translation progress and missing translations</p>
                        <button id="translation-status" class="button">View Status</button>
                    </div>
                </div>
            </div>
            
            <!-- Settings -->
            <div class="multilingual-settings">
                <h2>Multilingual Settings</h2>
                <form method="post" action="options.php">
                    <?php settings_fields('shoriprofen_multilingual_settings'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable Language Switcher</th>
                            <td>
                                <input type="checkbox" name="shoriprofen_multilingual_settings[switcher_enabled]" value="1" <?php checked($this->options['switcher_enabled'] ?? 1); ?>>
                                <label>Show language switcher in footer</label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Switcher Style</th>
                            <td>
                                <select name="shoriprofen_multilingual_settings[switcher_style]">
                                    <option value="dropdown" <?php selected($this->options['switcher_style'] ?? 'dropdown', 'dropdown'); ?>>Dropdown</option>
                                    <option value="flags" <?php selected($this->options['switcher_style'] ?? 'dropdown', 'flags'); ?>>Flags Only</option>
                                    <option value="names" <?php selected($this->options['switcher_style'] ?? 'dropdown', 'names'); ?>>Names Only</option>
                                    <option value="both" <?php selected($this->options['switcher_style'] ?? 'dropdown', 'both'); ?>>Flags + Names</option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">URL Structure</th>
                            <td>
                                <select name="shoriprofen_multilingual_settings[url_structure]">
                                    <option value="subdirectory" <?php selected($this->options['url_structure'] ?? 'subdirectory', 'subdirectory'); ?>>Subdirectory (/en/)</option>
                                    <option value="subdomain" <?php selected($this->options['url_structure'] ?? 'subdirectory', 'subdomain'); ?>>Subdomain (en.site.com)</option>
                                    <option value="parameter" <?php selected($this->options['url_structure'] ?? 'subdirectory', 'parameter'); ?>>Parameter (?lang=en)</option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Auto Redirect</th>
                            <td>
                                <input type="checkbox" name="shoriprofen_multilingual_settings[auto_redirect]" value="1" <?php checked($this->options['auto_redirect'] ?? 1); ?>>
                                <label>Redirect users to their preferred language based on browser settings</label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Hide Default Language</th>
                            <td>
                                <input type="checkbox" name="shoriprofen_multilingual_settings[hide_default]" value="1" <?php checked($this->options['hide_default'] ?? 0); ?>>
                                <label>Don't show default language in URL (e.g., /page/ instead of /uk/page/)</label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Translation Service</th>
                            <td>
                                <select name="shoriprofen_multilingual_settings[translation_service]">
                                    <option value="google" <?php selected($this->options['translation_service'] ?? 'google', 'google'); ?>>Google Translate</option>
                                    <option value="deepl" <?php selected($this->options['translation_service'] ?? 'google', 'deepl'); ?>>DeepL</option>
                                    <option value="openai" <?php selected($this->options['translation_service'] ?? 'google', 'openai'); ?>>OpenAI GPT</option>
                                </select>
                                <p class="description">Service to use for automatic translations</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">API Key</th>
                            <td>
                                <input type="text" name="shoriprofen_multilingual_settings[api_key]" value="<?php echo esc_attr($this->options['api_key'] ?? ''); ?>" class="regular-text">
                                <p class="description">API key for the selected translation service</p>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button(); ?>
                </form>
            </div>
            
            <!-- Translation Editor -->
            <div class="translation-editor">
                <h2>Translation Editor</h2>
                <div class="editor-controls">
                    <select id="translation-post-type">
                        <option value="post">Posts</option>
                        <option value="page">Pages</option>
                        <option value="visualization">Visualizations</option>
                        <option value="partner">Partners</option>
                    </select>
                    <input type="text" id="translation-search" placeholder="Search content..." class="regular-text">
                    <button id="load-translations" class="button button-primary">Load Content</button>
                </div>
                <div id="translation-content" class="translation-content"></div>
            </div>
        </div>
        
        <style>
        .language-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .language-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .language-card.active {
            border-color: #00a32a;
            background: #f0f6f0;
        }
        
        .language-card.inactive {
            opacity: 0.7;
        }
        
        .language-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .flag {
            font-size: 1.5em;
        }
        
        .language-header h3 {
            margin: 0;
            flex: 1;
        }
        
        .default-badge {
            background: #0073aa;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
        }
        
        .language-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .stat {
            text-align: center;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        
        .stat .label {
            display: block;
            font-size: 0.9em;
            color: #666;
        }
        
        .stat .value {
            display: block;
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
        }
        
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .tool-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .tool-card h3 {
            margin: 0 0 10px 0;
        }
        
        .tool-card p {
            margin: 0 0 15px 0;
            color: #666;
        }
        
        .tool-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .multilingual-settings, .translation-editor {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .editor-controls {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
        }
        
        .translation-content {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            min-height: 200px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            function activateLanguage(code) {
                // Implement language activation
                console.log('Activating language:', code);
            }
            
            function manageTranslations(code) {
                // Load translation management interface
                console.log('Managing translations for:', code);
            }
            
            $('#auto-translate').on('click', function() {
                $(this).prop('disabled', true).text('Translating...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'auto_translate',
                        nonce: '<?php echo wp_create_nonce('auto_translate'); ?>'
                    },
                    success: function(response) {
                        $(this).prop('disabled', false).text('Start Auto Translation');
                        if (response.success) {
                            alert('Translation completed successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + response.data);
                        }
                    }.bind(this)
                });
            });
            
            $('#load-translations').on('click', function() {
                var postType = $('#translation-post-type').val();
                var search = $('#translation-search').val();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'load_translations',
                        post_type: postType,
                        search: search,
                        nonce: '<?php echo wp_create_nonce('load_translations'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#translation-content').html(response.data.html);
                        } else {
                            $('#translation-content').html('<p>Error: ' + response.data + '</p>');
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Initialize Polylang settings
     */
    public function initialize_polylang() {
        if (!class_exists('Polylang')) {
            return;
        }
        
        // Configure Polylang settings
        $polylang_options = [
            'default_lang' => 'uk',
            'hide_default' => $this->options['hide_default'] ?? 0,
            'force_lang' => $this->get_force_lang_value(),
            'rewrite' => $this->options['url_structure'] ?? 'subdirectory',
            'browser' => $this->options['auto_redirect'] ?? 1,
            'domains' => [],
            'version' => '2.8'
        ];
        
        update_option('polylang', $polylang_options);
        
        // Register languages
        foreach ($this->languages as $code => $lang) {
            $this->register_language($code, $lang);
        }
        
        // Create language switcher menu
        $this->create_language_switcher_menu();
    }
    
    /**
     * Register language
     */
    private function register_language($code, $lang) {
        $existing_lang = get_term_by('slug', $code, 'language');
        
        if (!$existing_lang) {
            $term_data = [
                'slug' => $code,
                'name' => $lang['name'],
                'description' => $lang['locale']
            ];
            
            $term = wp_insert_term($lang['name'], 'language', $term_data);
            
            if (!is_wp_error($term)) {
                $term_id = $term['term_id'];
                update_term_meta($term_id, '_rtl', 0);
                update_term_meta($term_id, '_rtl', 0);
                update_term_meta($term_id, '_locale', $lang['locale']);
                update_term_meta($term_id, '_flag', $code . '.png');
                
                if ($lang['default'] ?? false) {
                    update_option('polylang_default_lang', $code);
                }
            }
        }
    }
    
    /**
     * Create language switcher menu
     */
    private function create_language_switcher_menu() {
        wp_nav_menu([
            'theme_location' => 'language_switcher',
            'menu_class' => 'language-switcher',
            'container' => false,
            'fallback_cb' => false
        ]);
    }
    
    /**
     * Add language switcher to footer
     */
    public function add_language_switcher() {
        if (!($this->options['switcher_enabled'] ?? 1)) {
            return;
        }
        
        $current_lang = $this->get_current_language();
        $style = $this->options['switcher_style'] ?? 'dropdown';
        
        ?>
        <div class="language-switcher-wrapper">
            <?php if ($style === 'dropdown'): ?>
                <div class="language-dropdown">
                    <button class="dropdown-toggle">
                        <span class="flag"><?php echo $this->languages[$current_lang]['flag']; ?></span>
                        <span class="name"><?php echo $this->languages[$current_lang]['name']; ?></span>
                        <span class="arrow">▼</span>
                    </button>
                    <ul class="dropdown-menu">
                        <?php foreach ($this->languages as $code => $lang): ?>
                            <?php if ($code !== $current_lang): ?>
                                <li>
                                    <a href="<?php echo $this->get_language_url($code); ?>">
                                        <span class="flag"><?php echo $lang['flag']; ?></span>
                                        <span class="name"><?php echo $lang['name']; ?></span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php elseif ($style === 'flags'): ?>
                <div class="language-flags">
                    <?php foreach ($this->languages as $code => $lang): ?>
                        <a href="<?php echo $this->get_language_url($code); ?>" class="flag-link <?php echo $code === $current_lang ? 'active' : ''; ?>">
                            <span class="flag"><?php echo $lang['flag']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($style === 'names'): ?>
                <div class="language-names">
                    <?php foreach ($this->languages as $code => $lang): ?>
                        <a href="<?php echo $this->get_language_url($code); ?>" class="name-link <?php echo $code === $current_lang ? 'active' : ''; ?>">
                            <?php echo $lang['name']; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: // both ?>
                <div class="language-both">
                    <?php foreach ($this->languages as $code => $lang): ?>
                        <a href="<?php echo $this->get_language_url($code); ?>" class="both-link <?php echo $code === $current_lang ? 'active' : ''; ?>">
                            <span class="flag"><?php echo $lang['flag']; ?></span>
                            <span class="name"><?php echo $lang['name']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .language-switcher-wrapper {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 1000;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 10px;
        }
        
        .language-dropdown {
            position: relative;
        }
        
        .dropdown-toggle {
            background: none;
            border: 1px solid #ddd;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .dropdown-menu {
            position: absolute;
            bottom: 100%;
            left: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-width: 150px;
            list-style: none;
            margin: 0;
            padding: 0;
            display: none;
        }
        
        .dropdown-menu li {
            margin: 0;
        }
        
        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            text-decoration: none;
            color: #333;
        }
        
        .dropdown-menu a:hover {
            background: #f0f0f0;
        }
        
        .language-flags, .language-names, .language-both {
            display: flex;
            gap: 10px;
        }
        
        .flag-link, .name-link, .both-link {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            border: 1px solid transparent;
        }
        
        .flag-link:hover, .name-link:hover, .both-link:hover {
            background: #f0f0f0;
        }
        
        .flag-link.active, .name-link.active, .both-link.active {
            border-color: #0073aa;
            background: #f0f6f0;
        }
        
        .flag {
            font-size: 1.2em;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('.dropdown-toggle').on('click', function(e) {
                e.stopPropagation();
                $('.dropdown-menu').toggle();
            });
            
            $(document).on('click', function() {
                $('.dropdown-menu').hide();
            });
        });
        </script>
        <?php
    }
    
    /**
     * Translate title
     */
    public function translate_title($title, $id) {
        return $this->translate_text($title, $id, 'title');
    }
    
    /**
     * Translate content
     */
    public function translate_content($content) {
        global $post;
        return $this->translate_text($content, $post->ID, 'content');
    }
    
    /**
     * Translate excerpt
     */
    public function translate_excerpt($excerpt) {
        global $post;
        return $this->translate_text($excerpt, $post->ID, 'excerpt');
    }
    
    /**
     * Translate meta value
     */
    public function translate_meta_value($value, $name, $post_id) {
        return $this->translate_text($value, $post_id, 'meta_' . $name);
    }
    
    /**
     * Translate text
     */
    private function translate_text($text, $post_id, $field) {
        $current_lang = $this->get_current_language();
        
        if ($current_lang === 'uk') {
            return $text; // Default language
        }
        
        $translation = $this->get_translation($post_id, $field, $current_lang);
        
        if ($translation) {
            return $translation;
        }
        
        // Auto-translate if enabled
        if ($this->options['auto_translate'] ?? false) {
            return $this->auto_translate($text, $current_lang);
        }
        
        return $text;
    }
    
    /**
     * Get translation
     */
    private function get_translation($post_id, $field, $lang) {
        $translations = get_post_meta($post_id, '_translations', true);
        
        if (is_array($translations) && isset($translations[$lang][$field])) {
            return $translations[$lang][$field];
        }
        
        return null;
    }
    
    /**
     * Auto translate
     */
    private function auto_translate($text, $target_lang) {
        $service = $this->options['translation_service'] ?? 'google';
        $api_key = $this->options['api_key'] ?? '';
        
        if (empty($api_key)) {
            return $text;
        }
        
        switch ($service) {
            case 'google':
                return $this->google_translate($text, $target_lang, $api_key);
            case 'deepl':
                return $this->deepl_translate($text, $target_lang, $api_key);
            case 'openai':
                return $this->openai_translate($text, $target_lang, $api_key);
            default:
                return $text;
        }
    }
    
    /**
     * Google Translate
     */
    private function google_translate($text, $target_lang, $api_key) {
        $url = 'https://translation.googleapis.com/language/translate/v2';
        
        $params = [
            'key' => $api_key,
            'q' => $text,
            'source' => 'uk',
            'target' => $target_lang === 'ru' ? 'ru' : 'en',
            'format' => 'text'
        ];
        
        $response = wp_remote_get($url . '?' . http_build_query($params));
        
        if (is_wp_error($response)) {
            return $text;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['data']['translations'][0]['translatedText'])) {
            return $body['data']['translations'][0]['translatedText'];
        }
        
        return $text;
    }
    
    /**
     * Add language-specific CSS
     */
    public function add_language_css() {
        $current_lang = $this->get_current_language();
        
        switch ($current_lang) {
            case 'ru':
                echo '<style>body { font-family: "Arial", sans-serif; }</style>';
                break;
            case 'en':
                echo '<style>body { font-family: "Helvetica", sans-serif; }</style>';
                break;
            default:
                echo '<style>body { font-family: "Inter", sans-serif; }</style>';
        }
    }
    
    /**
     * Handle translation AJAX
     */
    public function handle_translation() {
        check_ajax_referer('translate_content', 'nonce');
        
        $post_id = intval($_POST['post_id']);
        $field = sanitize_text_field($_POST['field']);
        $translation = sanitize_textarea_field($_POST['translation']);
        $lang = sanitize_text_field($_POST['lang']);
        
        $translations = get_post_meta($post_id, '_translations', true) ?: [];
        
        if (!isset($translations[$lang])) {
            $translations[$lang] = [];
        }
        
        $translations[$lang][$field] = $translation;
        
        update_post_meta($post_id, '_translations', $translations);
        
        wp_send_json_success('Translation saved');
    }
    
    /**
     * Helper methods
     */
    private function is_language_active($code) {
        return get_term_by('slug', $code, 'language') !== false;
    }
    
    private function get_current_language() {
        if (function_exists('pll_current_language')) {
            return pll_current_language('slug');
        }
        
        return 'uk'; // Default
    }
    
    private function get_language_url($code) {
        if (function_exists('pll_home_url')) {
            return pll_home_url($code);
        }
        
        return home_url('/' . $code . '/');
    }
    
    private function get_force_lang_value() {
        $structure = $this->options['url_structure'] ?? 'subdirectory';
        
        switch ($structure) {
            case 'subdirectory':
                return 1;
            case 'subdomain':
                return 2;
            case 'parameter':
                return 3;
            default:
                return 1;
        }
    }
    
    private function get_translated_posts_count($lang) {
        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'lang' => $lang,
            'posts_per_page' => -1
        ];
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    private function get_translated_pages_count($lang) {
        $args = [
            'post_type' => 'page',
            'post_status' => 'publish',
            'lang' => $lang,
            'posts_per_page' => -1
        ];
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    private function get_translated_products_count($lang) {
        $args = [
            'post_type' => 'visualization',
            'post_status' => 'publish',
            'lang' => $lang,
            'posts_per_page' => -1
        ];
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
}

// Initialize multilingual setup system
new MultilingualSetupSystem();

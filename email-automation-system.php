<?php
/**
 * Email Automation System for Штори ПроФен
 * Welcome series, abandoned cart, and marketing emails
 */

class EmailAutomationSystem {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('shoriprofen_email_settings', []);
        
        // Add admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Register AJAX handlers
        add_action('wp_ajax_subscribe_newsletter', [$this, 'handle_newsletter_subscription']);
        add_action('wp_ajax_nopriv_subscribe_newsletter', [$this, 'handle_newsletter_subscription']);
        
        // Add subscription form to footer
        add_action('wp_footer', [$this, 'add_subscription_form']);
        
        // Schedule cron jobs
        add_action('init', [$this, 'schedule_cron_jobs']);
        
        // Cron job handlers
        add_action('shoriprofen_send_welcome_emails', [$this, 'send_welcome_emails']);
        add_action('shoriprofen_send_abandoned_cart_emails', [$this, 'send_abandoned_cart_emails']);
        add_action('shoriprofen_send_marketing_emails', [$this, 'send_marketing_emails']);
        
        // Track user activity
        add_action('wp_footer', [$this, 'track_user_activity']);
        
        // Handle form submissions
        add_action('wp_ajax_submit_contact_form', [$this, 'handle_contact_form']);
        add_action('wp_ajax_nopriv_submit_contact_form', [$this, 'handle_contact_form']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'options-general.php',
            'Email Automation',
            'Email Automation',
            'manage_options',
            'email-automation',
            [$this, 'admin_page']
        );
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Email Automation Settings</h1>
            
            <div class="email-automation-tabs">
                <div class="tab-nav">
                    <button class="tab-button active" data-tab="welcome">Welcome Series</button>
                    <button class="tab-button" data-tab="abandoned">Abandoned Cart</button>
                    <button class="tab-button" data-tab="marketing">Marketing</button>
                    <button class="tab-button" data-tab="settings">Settings</button>
                </div>
                
                <div class="tab-content">
                    <!-- Welcome Series Tab -->
                    <div class="tab-pane active" id="welcome">
                        <h2>Welcome Email Series</h2>
                        <form method="post" action="options.php">
                            <?php settings_fields('shoriprofen_email_welcome'); ?>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Enable Welcome Series</th>
                                    <td>
                                        <input type="checkbox" name="shoriprofen_email_settings[welcome_enabled]" value="1" <?php checked($this->options['welcome_enabled'] ?? 0); ?>>
                                        <label>Enable automatic welcome email series</label>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">Email 1 - Immediate Welcome</th>
                                    <td>
                                        <fieldset>
                                            <label>Subject:</label><br>
                                            <input type="text" name="shoriprofen_email_settings[welcome1_subject]" value="<?php echo esc_attr($this->options['welcome1_subject'] ?? 'Welcome to Штори ПроФен!'); ?>" class="regular-text">
                                            
                                            <br><br>
                                            <label>Content:</label><br>
                                            <?php 
                                            wp_editor(
                                                $this->options['welcome1_content'] ?? $this->get_default_welcome_email_1(),
                                                'welcome1_content',
                                                ['textarea_name' => 'shoriprofen_email_settings[welcome1_content]']
                                            );
                                            ?>
                                        </fieldset>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">Email 2 - Day 1</th>
                                    <td>
                                        <fieldset>
                                            <label>Subject:</label><br>
                                            <input type="text" name="shoriprofen_email_settings[welcome2_subject]" value="<?php echo esc_attr($this->options['welcome2_subject'] ?? 'Find Your Perfect Style'); ?>" class="regular-text">
                                            
                                            <br><br>
                                            <label>Content:</label><br>
                                            <?php 
                                            wp_editor(
                                                $this->options['welcome2_content'] ?? $this->get_default_welcome_email_2(),
                                                'welcome2_content',
                                                ['textarea_name' => 'shoriprofen_email_settings[welcome2_content]']
                                            );
                                            ?>
                                        </fieldset>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">Email 3 - Day 3</th>
                                    <td>
                                        <fieldset>
                                            <label>Subject:</label><br>
                                            <input type="text" name="shoriprofen_email_settings[welcome3_subject]" value="<?php echo esc_attr($this->options['welcome3_subject'] ?? 'Special Offer Just for You'); ?>" class="regular-text">
                                            
                                            <br><br>
                                            <label>Content:</label><br>
                                            <?php 
                                            wp_editor(
                                                $this->options['welcome3_content'] ?? $this->get_default_welcome_email_3(),
                                                'welcome3_content',
                                                ['textarea_name' => 'shoriprofen_email_settings[welcome3_content]']
                                            );
                                            ?>
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                            
                            <?php submit_button(); ?>
                        </form>
                    </div>
                    
                    <!-- Abandoned Cart Tab -->
                    <div class="tab-pane" id="abandoned">
                        <h2>Abandoned Cart Emails</h2>
                        <form method="post" action="options.php">
                            <?php settings_fields('shoriprofen_email_abandoned'); ?>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Enable Abandoned Cart</th>
                                    <td>
                                        <input type="checkbox" name="shoriprofen_email_settings[abandoned_enabled]" value="1" <?php checked($this->options['abandoned_enabled'] ?? 0); ?>>
                                        <label>Enable abandoned cart recovery emails</label>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">First Email (1 hour)</th>
                                    <td>
                                        <fieldset>
                                            <label>Subject:</label><br>
                                            <input type="text" name="shoriprofen_email_settings[abandoned1_subject]" value="<?php echo esc_attr($this->options['abandoned1_subject'] ?? 'Did you forget something?'); ?>" class="regular-text">
                                            
                                            <br><br>
                                            <label>Content:</label><br>
                                            <?php 
                                            wp_editor(
                                                $this->options['abandoned1_content'] ?? $this->get_default_abandoned_email_1(),
                                                'abandoned1_content',
                                                ['textarea_name' => 'shoriprofen_email_settings[abandoned1_content]']
                                            );
                                            ?>
                                        </fieldset>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">Second Email (24 hours)</th>
                                    <td>
                                        <fieldset>
                                            <label>Subject:</label><br>
                                            <input type="text" name="shoriprofen_email_settings[abandoned2_subject]" value="<?php echo esc_attr($this->options['abandoned2_subject'] ?? 'Your cart is waiting'); ?>" class="regular-text">
                                            
                                            <br><br>
                                            <label>Content:</label><br>
                                            <?php 
                                            wp_editor(
                                                $this->options['abandoned2_content'] ?? $this->get_default_abandoned_email_2(),
                                                'abandoned2_content',
                                                ['textarea_name' => 'shoriprofen_email_settings[abandoned2_content]']
                                            );
                                            ?>
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                            
                            <?php submit_button(); ?>
                        </form>
                    </div>
                    
                    <!-- Marketing Tab -->
                    <div class="tab-pane" id="marketing">
                        <h2>Marketing Emails</h2>
                        <form method="post" action="options.php">
                            <?php settings_fields('shoriprofen_email_marketing'); ?>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Enable Marketing</th>
                                    <td>
                                        <input type="checkbox" name="shoriprofen_email_settings[marketing_enabled]" value="1" <?php checked($this->options['marketing_enabled'] ?? 0); ?>>
                                        <label>Enable marketing email campaigns</label>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">Weekly Newsletter</th>
                                    <td>
                                        <fieldset>
                                            <label>Subject:</label><br>
                                            <input type="text" name="shoriprofen_email_settings[newsletter_subject]" value="<?php echo esc_attr($this->options['newsletter_subject'] ?? 'Your Weekly Design Inspiration'); ?>" class="regular-text">
                                            
                                            <br><br>
                                            <label>Content:</label><br>
                                            <?php 
                                            wp_editor(
                                                $this->options['newsletter_content'] ?? $this->get_default_newsletter_template(),
                                                'newsletter_content',
                                                ['textarea_name' => 'shoriprofen_email_settings[newsletter_content]']
                                            );
                                            ?>
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                            
                            <?php submit_button(); ?>
                        </form>
                    </div>
                    
                    <!-- Settings Tab -->
                    <div class="tab-pane" id="settings">
                        <h2>Email Settings</h2>
                        <form method="post" action="options.php">
                            <?php settings_fields('shoriprofen_email_settings'); ?>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">From Email</th>
                                    <td>
                                        <input type="email" name="shoriprofen_email_settings[from_email]" value="<?php echo esc_attr($this->options['from_email'] ?? get_option('admin_email')); ?>" class="regular-text">
                                        <p class="description">Email address to send from</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">From Name</th>
                                    <td>
                                        <input type="text" name="shoriprofen_email_settings[from_name]" value="<?php echo esc_attr($this->options['from_name'] ?? 'Штори ПроФен'); ?>" class="regular-text">
                                        <p class="description">Name to send from</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">Test Email</th>
                                    <td>
                                        <input type="email" name="test_email" placeholder="Enter test email address" class="regular-text">
                                        <button type="button" id="send-test-email" class="button">Send Test Email</button>
                                    </td>
                                </tr>
                            </table>
                            
                            <?php submit_button(); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .email-automation-tabs {
            margin-top: 20px;
        }
        
        .tab-nav {
            display: flex;
            border-bottom: 1px solid #ccc;
            margin-bottom: 20px;
        }
        
        .tab-button {
            background: none;
            border: none;
            padding: 12px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            font-weight: 500;
        }
        
        .tab-button.active {
            border-bottom-color: #0073aa;
            color: #0073aa;
        }
        
        .tab-pane {
            display: none;
        }
        
        .tab-pane.active {
            display: block;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('.tab-button').on('click', function() {
                $('.tab-button').removeClass('active');
                $('.tab-pane').removeClass('active');
                
                $(this).addClass('active');
                $('#' + $(this).data('tab')).addClass('active');
            });
            
            $('#send-test-email').on('click', function() {
                var email = $('input[name="test_email"]').val();
                if (!email) {
                    alert('Please enter a test email address');
                    return;
                }
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'send_test_email',
                        email: email,
                        nonce: '<?php echo wp_create_nonce('send_test_email'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Test email sent successfully!');
                        } else {
                            alert('Error: ' + response.data);
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Handle newsletter subscription
     */
    public function handle_newsletter_subscription() {
        check_ajax_referer('subscribe_newsletter', 'nonce');
        
        $email = sanitize_email($_POST['email']);
        $name = sanitize_text_field($_POST['name']);
        
        if (!is_email($email)) {
            wp_send_json_error('Invalid email address');
        }
        
        // Check if already subscribed
        if (email_exists($email)) {
            wp_send_json_error('You are already subscribed');
        }
        
        // Create subscriber
        $user_data = [
            'user_login' => $email,
            'user_email' => $email,
            'user_pass' => wp_generate_password(),
            'first_name' => $name,
            'role' => 'subscriber',
        ];
        
        $user_id = wp_insert_user($user_data);
        
        if ($user_id && !is_wp_error($user_id)) {
            // Schedule welcome emails
            $this->schedule_welcome_emails($user_id);
            
            // Send immediate welcome email
            $this->send_welcome_email($user_id, 1);
            
            wp_send_json_success([
                'message' => 'Thank you for subscribing! Check your email for a welcome message.',
                'discount_code' => 'WELCOME10'
            ]);
        } else {
            wp_send_json_error('Subscription failed');
        }
    }
    
    /**
     * Schedule welcome emails
     */
    private function schedule_welcome_emails($user_id) {
        if (!($this->options['welcome_enabled'] ?? 0)) {
            return;
        }
        
        // Email 2 - after 1 day
        wp_schedule_single_event(time() + DAY_IN_SECONDS, 'shoriprofen_send_welcome_email', [$user_id, 2]);
        
        // Email 3 - after 3 days
        wp_schedule_single_event(time() + (3 * DAY_IN_SECONDS), 'shoriprofen_send_welcome_email', [$user_id, 3]);
    }
    
    /**
     * Send welcome email
     */
    public function send_welcome_email($user_id, $email_number) {
        $user = get_userdata($user_id);
        if (!$user) {
            return;
        }
        
        $subject_key = "welcome{$email_number}_subject";
        $content_key = "welcome{$email_number}_content";
        
        $subject = $this->options[$subject_key] ?? $this->get_default_welcome_subject($email_number);
        $content = $this->options[$content_key] ?? $this->get_default_welcome_content($email_number);
        
        // Replace placeholders
        $subject = $this->replace_placeholders($subject, $user);
        $content = $this->replace_placeholders($content, $user);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . ($this->options['from_name'] ?? 'Штори ПроФен') . ' <' . ($this->options['from_email'] ?? get_option('admin_email')) . '>'
        ];
        
        wp_mail($user->user_email, $subject, $content, $headers);
    }
    
    /**
     * Get default welcome email 1
     */
    private function get_default_welcome_email_1() {
        return <<<HTML
<p>Hi {{name}},</p>
<p>Welcome to Штори ПроФен! We're excited to help you find the perfect window treatments for your home.</p>
<p>As a special thank you for joining us, here's a 10% discount on your first order:</p>
<p><strong>Discount code: WELCOME10</strong></p>
<p>Explore our <a href="{{site_url}}/designfinder/">Design & Inspiration</a> section to discover beautiful ideas for your space, or try our <a href="{{site_url}}/configurator/">interactive configurator</a> to create your perfect solution.</p>
<p>Best regards,<br>The Штори ПроФен Team</p>
HTML;
    }
    
    /**
     * Get default welcome email 2
     */
    private function get_default_welcome_email_2() {
        return <<<HTML
<p>Hi {{name}},</p>
<p>Yesterday you joined the Штори ПроФен family, and we wanted to share some inspiration with you!</p>
<p>Did you know we offer:</p>
<ul>
    <li>🥽 AR visualization to see products in your space</li>
    <li>🎨 Custom configurations for any window type</li>
    <li>🏠 Professional installation services</li>
    <li>💰 Free design consultations</li>
</ul>
<p><a href="{{site_url}}/designfinder/">Browse our gallery</a> to see how we've transformed spaces like yours.</p>
<p>Have questions? Reply to this email - we're here to help!</p>
<p>Best regards,<br>The Штори ПроФен Team</p>
HTML;
    }
    
    /**
     * Get default welcome email 3
     */
    private function get_default_welcome_email_3() {
        return <<<HTML
<p>Hi {{name}},</p>
<p>It's been a few days since you joined us, and we have something special for you!</p>
<p>For the next 48 hours, use this exclusive discount:</p>
<p><strong>Extra 5% OFF + Free Installation</strong></p>
<p><strong>Code: VIP15</strong></p>
<p>This is perfect timing if you're ready to transform your windows with our beautiful, functional solutions.</p>
<p><a href="{{site_url}}/configurator/">Start designing</a> your perfect window treatments today!</p>
<p>This offer expires in 48 hours - don't miss out!</p>
<p>Best regards,<br>The Штори ПроФен Team</p>
HTML;
    }
    
    /**
     * Get default abandoned cart email 1
     */
    private function get_default_abandoned_email_1() {
        return <<<HTML
<p>Hi {{name}},</p>
<p>We noticed you left some items in your cart. Did you get distracted?</p>
<p>Your configuration is still waiting for you:</p>
<p><a href="{{cart_url}}">Complete your order</a></p>
<p>Remember, all our products come with:</p>
<ul>
    <li>✅ 2-year warranty</li>
    <li>✅ Free shipping on orders over 5000 UAH</li>
    <li>✅ Professional installation available</li>
</ul>
<p>Need help? Reply to this email or call us at +380 44 123 4567.</p>
<p>Best regards,<br>The Штори ПроФен Team</p>
HTML;
    }
    
    /**
     * Get default abandoned cart email 2
     */
    private function get_default_abandoned_email_2() {
        return <<<HTML
<p>Hi {{name}},</p>
<p>Your perfect window solution is still waiting in your cart!</p>
<p>Just a reminder that your configuration includes:</p>
<p>{{cart_items}}</p>
<p><a href="{{cart_url}}">Complete your order now</a></p>
<p>As a special incentive, use code <strong>CART10</strong> for an additional 10% off your order.</p>
<p>This offer expires in 24 hours.</p>
<p>Best regards,<br>The Штори ПроФен Team</p>
HTML;
    }
    
    /**
     * Get default newsletter template
     */
    private function get_default_newsletter_template() {
        return <<<HTML
<p>Hi {{name}},</p>
<p>Here's your weekly dose of design inspiration from Штори ПроФен!</p>
<h3>This Week's Featured Room: Modern Kitchen</h3>
<p>Discover how our roller blinds can transform your kitchen into a bright, functional space.</p>
<p><a href="{{site_url}}/designfinder/?room=kitchen&style=modern">Explore Modern Kitchen Ideas</a></p>
<h3>AR Tip of the Week</h3>
<p>Did you know you can use our AR feature to see how different colors look in your actual room? Try it on any product page!</p>
<h3>Customer Spotlight</h3>
<p>"The configurator made it so easy to find exactly what I needed. The AR feature helped me visualize it in my space before ordering!" - Maria K., Kyiv</p>
<p><a href="{{site_url}}/configurator/">Start Your Own Design</a></p>
<p>Best regards,<br>The Штори ПроФен Team</p>
HTML;
    }
    
    /**
     * Replace placeholders in email content
     */
    private function replace_placeholders($content, $user) {
        $placeholders = [
            '{{name}}' => $user->first_name ?: $user->display_name,
            '{{email}}' => $user->user_email,
            '{{site_url}}' => home_url(),
            '{{cart_url}}' => home_url('/cart/'),
            '{{discount_code}}' => 'WELCOME10'
        ];
        
        return str_replace(array_keys($placeholders), array_values($placeholders), $content);
    }
    
    /**
     * Add subscription form to footer
     */
    public function add_subscription_form() {
        if (is_user_logged_in()) {
            return; // Don't show to logged-in users
        }
        ?>
        <div id="newsletter-popup" class="newsletter-popup" style="display: none;">
            <div class="newsletter-content">
                <button class="newsletter-close">&times;</button>
                <h3>Get 10% Off Your First Order!</h3>
                <p>Subscribe to our newsletter for exclusive offers and design inspiration.</p>
                <form id="newsletter-form">
                    <input type="text" name="name" placeholder="Your Name" required>
                    <input type="email" name="email" placeholder="Your Email" required>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
                <p class="newsletter-disclaimer">We respect your privacy. Unsubscribe at any time.</p>
            </div>
        </div>
        
        <style>
        .newsletter-popup {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
            z-index: 9999;
            max-width: 350px;
            padding: 0;
        }
        
        .newsletter-content {
            padding: 20px;
            position: relative;
        }
        
        .newsletter-close {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #999;
        }
        
        .newsletter-content h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .newsletter-content p {
            margin: 0 0 15px 0;
            font-size: 14px;
            color: #666;
        }
        
        .newsletter-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .newsletter-disclaimer {
            font-size: 12px;
            color: #999;
            margin-top: 10px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Show popup after 5 seconds
            setTimeout(function() {
                if (!localStorage.getItem('newsletter_closed')) {
                    $('#newsletter-popup').fadeIn();
                }
            }, 5000);
            
            // Close popup
            $('.newsletter-close').on('click', function() {
                $('#newsletter-popup').fadeOut();
                localStorage.setItem('newsletter_closed', 'true');
            });
            
            // Handle form submission
            $('#newsletter-form').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: $(this).serialize() + '&action=subscribe_newsletter&nonce=' + '<?php echo wp_create_nonce('subscribe_newsletter'); ?>',
                    success: function(response) {
                        if (response.success) {
                            $('#newsletter-popup').html('<div class="success-message">' + response.data.message + '</div>');
                            setTimeout(function() {
                                $('#newsletter-popup').fadeOut();
                            }, 3000);
                        } else {
                            alert(response.data);
                        }
                    }
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
        // Schedule welcome emails
        if (!wp_next_scheduled('shoriprofen_send_welcome_emails')) {
            wp_schedule_event(time(), 'hourly', 'shoriprofen_send_welcome_emails');
        }
        
        // Schedule abandoned cart emails
        if (!wp_next_scheduled('shoriprofen_send_abandoned_cart_emails')) {
            wp_schedule_event(time(), 'hourly', 'shoriprofen_send_abandoned_cart_emails');
        }
        
        // Schedule marketing emails
        if (!wp_next_scheduled('shoriprofen_send_marketing_emails')) {
            wp_schedule_event(time(), 'daily', 'shoriprofen_send_marketing_emails');
        }
    }
    
    /**
     * Track user activity
     */
    public function track_user_activity() {
        if (!is_user_logged_in()) {
            return;
        }
        
        $user_id = get_current_user_id();
        $page_url = $_SERVER['REQUEST_URI'];
        
        // Track page views
        $this->track_user_activity_data($user_id, 'page_view', $page_url);
        
        // Track configurator activity
        if (strpos($page_url, 'configurator') !== false) {
            $this->track_user_activity_data($user_id, 'configurator_visit', $page_url);
        }
        
        // Track AR usage
        if (strpos($page_url, 'ar') !== false) {
            $this->track_user_activity_data($user_id, 'ar_usage', $page_url);
        }
    }
    
    /**
     * Track user activity data
     */
    private function track_user_activity_data($user_id, $activity_type, $data) {
        $activities = get_user_meta($user_id, 'user_activities', true) ?: [];
        
        $activities[] = [
            'type' => $activity_type,
            'data' => $data,
            'timestamp' => current_time('mysql')
        ];
        
        // Keep only last 100 activities
        if (count($activities) > 100) {
            $activities = array_slice($activities, -100);
        }
        
        update_user_meta($user_id, 'user_activities', $activities);
    }
    
    /**
     * Handle contact form submission
     */
    public function handle_contact_form() {
        check_ajax_referer('submit_contact_form', 'nonce');
        
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $message = sanitize_textarea_field($_POST['message']);
        
        if (!is_email($email)) {
            wp_send_json_error('Invalid email address');
        }
        
        $to = get_option('admin_email');
        $subject = 'Contact Form: ' . $name;
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        $email_content = <<<HTML
<p><strong>Name:</strong> {$name}</p>
<p><strong>Email:</strong> {$email}</p>
<p><strong>Message:</strong></p>
<p>{$message}</p>
HTML;
        
        if (wp_mail($to, $subject, $email_content, $headers)) {
            wp_send_json_success('Thank you for your message! We will get back to you soon.');
        } else {
            wp_send_json_error('Failed to send message. Please try again.');
        }
    }
}

// Initialize the email automation system
new EmailAutomationSystem();

<?php
/**
 * Plugin Name: فرم‌ساز پیامکی
 * Plugin URI: https://hoseinmos.com
 * Description: ایجاد فرم‌های سفارشی با قابلیت ارسال پیامک و ذخیره اطلاعات
 * Version: 1.0.0
 * Author: hoseinmos
 * Author URI: https://hoseinmos.com
 * Text Domain: sms-form-builder
 * Domain Path: /languages
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SMSFB_VERSION', '1.0.0');
define('SMSFB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SMSFB_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SMSFB_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Main plugin class
class SMSFormBuilder {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_smsfb_submit_form', array($this, 'ajax_submit_form'));
        add_action('wp_ajax_nopriv_smsfb_submit_form', array($this, 'ajax_submit_form'));
        add_action('wp_ajax_smsfb_get_cities', array($this, 'ajax_get_cities'));
        add_action('wp_ajax_nopriv_smsfb_get_cities', array($this, 'ajax_get_cities'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Initialize plugin
    }
    
    public function activate() {
        $this->create_tables();
    }
    
    public function deactivate() {
        // Cleanup if needed
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Forms table
        $forms_table = $wpdb->prefix . 'smsfb_forms';
        $sql_forms = "CREATE TABLE $forms_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_name varchar(100) NOT NULL,
            form_fields text NOT NULL,
            required_fields text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY form_name (form_name)
        ) $charset_collate;";
        
        // Submissions table
        $submissions_table = $wpdb->prefix . 'smsfb_submissions';
        $sql_submissions = "CREATE TABLE $submissions_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_name varchar(100) NOT NULL,
            form_data text NOT NULL,
            ip_address varchar(45) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_forms);
        dbDelta($sql_submissions);
    }
    
    public function admin_menu() {
        add_menu_page(
            'فرم‌ساز پیامکی',
            'فرم‌ساز پیامکی',
            'manage_options',
            'smsfb_main',
            array($this, 'admin_main_page'),
            'dashicons-forms',
            30
        );
        
        add_submenu_page(
            'smsfb_main',
            'فرم‌های ساخته‌شده',
            'فرم‌های ساخته‌شده',
            'manage_options',
            'smsfb_forms',
            array($this, 'admin_forms_page')
        );
        
        add_submenu_page(
            'smsfb_main',
            'ارسال‌ها',
            'ارسال‌ها',
            'manage_options',
            'smsfb_submissions',
            array($this, 'admin_submissions_page')
        );
        
        add_submenu_page(
            'smsfb_main',
            'تنظیمات',
            'تنظیمات',
            'manage_options',
            'smsfb_settings',
            array($this, 'admin_settings_page')
        );
        
        add_submenu_page(
            'smsfb_main',
            'شورت‌کدها',
            'شورت‌کدها',
            'manage_options',
            'smsfb_shortcodes',
            array($this, 'admin_shortcodes_page')
        );
    }
    
    public function enqueue_public_scripts() {
        wp_enqueue_script('smsfb-public', SMSFB_PLUGIN_URL . 'assets/js/public.js', array('jquery'), SMSFB_VERSION, true);
        wp_enqueue_style('smsfb-public', SMSFB_PLUGIN_URL . 'assets/css/public.css', array(), SMSFB_VERSION);
        
        wp_localize_script('smsfb-public', 'smsfb_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('smsfb_nonce')
        ));
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'smsfb') !== false) {
            wp_enqueue_script('smsfb-admin', SMSFB_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), SMSFB_VERSION, true);
            wp_enqueue_style('smsfb-admin', SMSFB_PLUGIN_URL . 'assets/css/admin.css', array(), SMSFB_VERSION);
        }
    }
    
    public function ajax_submit_form() {
        check_ajax_referer('smsfb_nonce', 'nonce');
        
        $form_name = sanitize_text_field($_POST['form_name']);
        $form_data = $_POST['form_data'];
        
        // Validate required fields
        $required_fields = $this->get_form_required_fields($form_name);
        $errors = array();
        
        foreach ($required_fields as $field) {
            if (empty($form_data[$field])) {
                $errors[] = "فیلد {$field} الزامی است.";
            }
        }
        
        if (!empty($errors)) {
            wp_send_json_error(array('message' => implode('<br>', $errors)));
        }
        
        // Save to database
        $saved = $this->save_submission($form_name, $form_data);
        
        if ($saved) {
            // Send SMS
            $this->send_sms($form_name, $form_data);
            
            wp_send_json_success(array('message' => 'با موفقیت ارسال شد'));
        } else {
            wp_send_json_error(array('message' => 'خطا در ذخیره اطلاعات'));
        }
    }
    
    public function ajax_get_cities() {
        check_ajax_referer('smsfb_nonce', 'nonce');
        
        $province = sanitize_text_field($_POST['province']);
        $cities = $this->get_cities_by_province($province);
        
        wp_send_json_success($cities);
    }
    
    private function get_form_required_fields($form_name) {
        global $wpdb;
        $table = $wpdb->prefix . 'smsfb_forms';
        $result = $wpdb->get_var($wpdb->prepare("SELECT required_fields FROM $table WHERE form_name = %s", $form_name));
        
        return $result ? json_decode($result, true) : array();
    }
    
    private function save_submission($form_name, $form_data) {
        global $wpdb;
        $table = $wpdb->prefix . 'smsfb_submissions';
        
        $data = array(
            'form_name' => $form_name,
            'form_data' => json_encode($form_data),
            'ip_address' => $this->get_client_ip()
        );
        
        return $wpdb->insert($table, $data);
    }
    
    private function send_sms($form_name, $form_data) {
        $settings = get_option('smsfb_settings', array());
        
        if (empty($settings['api_key']) || empty($settings['sender_number']) || empty($settings['admin_number'])) {
            return false;
        }
        
        $message = $this->build_sms_message($form_name, $form_data);
        
        $url = 'https://api.kavenegar.com/v1/' . $settings['api_key'] . '/sms/send.json';
        $data = array(
            'receptor' => $settings['admin_number'],
            'sender' => $settings['sender_number'],
            'message' => $message
        );
        
        $response = wp_remote_post($url, array(
            'body' => $data,
            'timeout' => 30
        ));
        
        return !is_wp_error($response);
    }
    
    private function build_sms_message($form_name, $form_data) {
        $settings = get_option('smsfb_settings', array());
        $message = "فرم جدید: {$form_name}\n";
        
        $form_fields = $this->get_form_fields($form_name);
        foreach ($form_fields as $field) {
            if (isset($form_data[$field]) && !empty($form_data[$field])) {
                $field_label = $this->get_field_label($field);
                $message .= "{$field_label}: {$form_data[$field]}\n";
            }
        }
        
        return $message;
    }
    
    private function get_form_fields($form_name) {
        global $wpdb;
        $table = $wpdb->prefix . 'smsfb_forms';
        $result = $wpdb->get_var($wpdb->prepare("SELECT form_fields FROM $table WHERE form_name = %s", $form_name));
        
        return $result ? json_decode($result, true) : array();
    }
    
    private function get_field_label($field) {
        $labels = array(
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'phone' => 'شماره تماس',
            'email' => 'ایمیل',
            'description' => 'توضیح',
            'province' => 'استان',
            'city' => 'شهر'
        );
        
        return isset($labels[$field]) ? $labels[$field] : $field;
    }
    
    private function get_cities_by_province($province) {
        $json_file = SMSFB_PLUGIN_PATH . 'data/provinces_cities.json';
        if (file_exists($json_file)) {
            $data = json_decode(file_get_contents($json_file), true);
            return isset($data[$province]) ? $data[$province] : array();
        }
        return array();
    }
    
    private function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    // Admin page methods
    public function admin_main_page() {
        include SMSFB_PLUGIN_PATH . 'admin/main-page.php';
    }
    
    public function admin_forms_page() {
        include SMSFB_PLUGIN_PATH . 'admin/forms-page.php';
    }
    
    public function admin_submissions_page() {
        include SMSFB_PLUGIN_PATH . 'admin/submissions-page.php';
    }
    
    public function admin_settings_page() {
        include SMSFB_PLUGIN_PATH . 'admin/settings-page.php';
    }
    
    public function admin_shortcodes_page() {
        include SMSFB_PLUGIN_PATH . 'admin/shortcodes-page.php';
    }
}

// Initialize plugin
new SMSFormBuilder();

// Shortcode
function smsfb_form_shortcode($atts) {
    $atts = shortcode_atts(array(
        'name' => ''
    ), $atts);
    
    if (empty($atts['name'])) {
        return '<p>نام فرم مشخص نشده است.</p>';
    }
    
    ob_start();
    include SMSFB_PLUGIN_PATH . 'templates/form-template.php';
    return ob_get_clean();
}
add_shortcode('sms_form', 'smsfb_form_shortcode');
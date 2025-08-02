<?php
if (!defined('ABSPATH')) {
    exit;
}

// Handle settings save
if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'smsfb_settings')) {
    $settings = array(
        'api_key' => sanitize_text_field($_POST['api_key']),
        'sender_number' => sanitize_text_field($_POST['sender_number']),
        'admin_number' => sanitize_text_field($_POST['admin_number']),
        'sms_message_template' => sanitize_textarea_field($_POST['sms_message_template'])
    );
    
    update_option('smsfb_settings', $settings);
    echo '<div class="notice notice-success"><p>تنظیمات با موفقیت ذخیره شد.</p></div>';
}

$settings = get_option('smsfb_settings', array());
$api_key = isset($settings['api_key']) ? $settings['api_key'] : '';
$sender_number = isset($settings['sender_number']) ? $settings['sender_number'] : '';
$admin_number = isset($settings['admin_number']) ? $settings['admin_number'] : '';
$sms_message_template = isset($settings['sms_message_template']) ? $settings['sms_message_template'] : 'فرم جدید: {form_name}
نام: {first_name}
نام خانوادگی: {last_name}
شماره تماس: {phone}
ایمیل: {email}
توضیح: {description}
استان: {province}
شهر: {city}';
?>

<div class="wrap">
    <h1>تنظیمات فرم‌ساز پیامکی</h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('smsfb_settings'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">API Key کاوه‌نگار</th>
                <td>
                    <input type="text" name="api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                    <p class="description">کلید API کاوه‌نگار خود را وارد کنید</p>
                </td>
            </tr>
            <tr>
                <th scope="row">شماره فرستنده</th>
                <td>
                    <input type="text" name="sender_number" value="<?php echo esc_attr($sender_number); ?>" class="regular-text">
                    <p class="description">شماره تلفن فرستنده پیامک (مثال: 09123456789)</p>
                </td>
            </tr>
            <tr>
                <th scope="row">شماره مدیریت</th>
                <td>
                    <input type="text" name="admin_number" value="<?php echo esc_attr($admin_number); ?>" class="regular-text">
                    <p class="description">شماره تلفن که پیامک‌ها برای آن ارسال می‌شود</p>
                </td>
            </tr>
            <tr>
                <th scope="row">قالب پیامک</th>
                <td>
                    <textarea name="sms_message_template" rows="10" cols="50" class="large-text"><?php echo esc_textarea($sms_message_template); ?></textarea>
                    <p class="description">
                        متغیرهای قابل استفاده:<br>
                        {form_name} - نام فرم<br>
                        {first_name} - نام<br>
                        {last_name} - نام خانوادگی<br>
                        {phone} - شماره تماس<br>
                        {email} - ایمیل<br>
                        {description} - توضیح<br>
                        {province} - استان<br>
                        {city} - شهر
                    </p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="ذخیره تنظیمات">
        </p>
    </form>
    
    <div class="smsfb-test-section">
        <h2>تست اتصال</h2>
        <p>برای تست اتصال به کاوه‌نگار، یک پیامک تست ارسال کنید:</p>
        <button type="button" id="test-sms" class="button">ارسال پیامک تست</button>
        <div id="test-result"></div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#test-sms').click(function() {
        var button = $(this);
        var result = $('#test-result');
        
        button.prop('disabled', true).text('در حال ارسال...');
        result.html('');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'smsfb_test_sms',
                nonce: '<?php echo wp_create_nonce('smsfb_test_sms'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    result.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                } else {
                    result.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                result.html('<div class="notice notice-error"><p>خطا در اتصال</p></div>');
            },
            complete: function() {
                button.prop('disabled', false).text('ارسال پیامک تست');
            }
        });
    });
});
</script>
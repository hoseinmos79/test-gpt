<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$forms = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}smsfb_forms ORDER BY created_at DESC");
?>

<div class="wrap">
    <h1>شورت‌کدها</h1>
    
    <div class="smsfb-shortcodes-container">
        <?php if ($forms): ?>
            <div class="smsfb-shortcodes-list">
                <h2>شورت‌کدهای موجود</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>نام فرم</th>
                            <th>شورت‌کد</th>
                            <th>کپی</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($forms as $form): ?>
                            <tr>
                                <td><?php echo esc_html($form->form_name); ?></td>
                                <td><code>[sms_form name="<?php echo esc_attr($form->form_name); ?>"]</code></td>
                                <td>
                                    <button type="button" class="button button-small copy-shortcode" data-shortcode='[sms_form name="<?php echo esc_attr($form->form_name); ?>"]'>
                                        کپی
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="smsfb-usage">
                <h2>نحوه استفاده</h2>
                <div class="smsfb-usage-content">
                    <h3>1. قرار دادن در صفحه یا پست</h3>
                    <p>شورت‌کد مورد نظر را در محتوای صفحه یا پست خود قرار دهید:</p>
                    <code>[sms_form name="نام_فرم"]</code>
                    
                    <h3>2. قرار دادن در قالب</h3>
                    <p>برای قرار دادن در قالب، از کد PHP زیر استفاده کنید:</p>
                    <code>&lt;?php echo do_shortcode('[sms_form name="نام_فرم"]'); ?&gt;</code>
                    
                    <h3>3. قرار دادن در ویجت</h3>
                    <p>شورت‌کد را در ویجت متن قرار دهید.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="smsfb-no-forms">
                <h2>هیچ فرمی یافت نشد</h2>
                <p>برای استفاده از شورت‌کد، ابتدا یک فرم ایجاد کنید.</p>
                <a href="<?php echo admin_url('admin.php?page=smsfb_forms'); ?>" class="button button-primary">ایجاد فرم جدید</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.copy-shortcode').click(function() {
        var shortcode = $(this).data('shortcode');
        var button = $(this);
        
        // Create temporary textarea
        var textarea = $('<textarea>').val(shortcode).appendTo('body').select();
        document.execCommand('copy');
        textarea.remove();
        
        // Show feedback
        var originalText = button.text();
        button.text('کپی شد!').prop('disabled', true);
        
        setTimeout(function() {
            button.text(originalText).prop('disabled', false);
        }, 2000);
    });
});
</script>
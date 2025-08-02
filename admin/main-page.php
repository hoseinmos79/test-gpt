<?php
if (!defined('ABSPATH')) {
    exit;
}

$total_forms = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}smsfb_forms");
$total_submissions = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}smsfb_submissions");
$recent_submissions = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}smsfb_submissions ORDER BY created_at DESC LIMIT 5");
?>

<div class="wrap">
    <h1>فرم‌ساز پیامکی</h1>
    
    <div class="smsfb-dashboard">
        <div class="smsfb-stats">
            <div class="stat-box">
                <h3><?php echo $total_forms; ?></h3>
                <p>تعداد فرم‌ها</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $total_submissions; ?></h3>
                <p>تعداد ارسال‌ها</p>
            </div>
        </div>
        
        <div class="smsfb-recent">
            <h2>آخرین ارسال‌ها</h2>
            <?php if ($recent_submissions): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>فرم</th>
                            <th>IP</th>
                            <th>تاریخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_submissions as $submission): ?>
                            <tr>
                                <td><?php echo esc_html($submission->form_name); ?></td>
                                <td><?php echo esc_html($submission->ip_address); ?></td>
                                <td><?php echo esc_html($submission->created_at); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>هنوز ارسالی ثبت نشده است.</p>
            <?php endif; ?>
        </div>
        
        <div class="smsfb-actions">
            <a href="<?php echo admin_url('admin.php?page=smsfb_forms'); ?>" class="button button-primary">مدیریت فرم‌ها</a>
            <a href="<?php echo admin_url('admin.php?page=smsfb_submissions'); ?>" class="button">مشاهده ارسال‌ها</a>
            <a href="<?php echo admin_url('admin.php?page=smsfb_settings'); ?>" class="button">تنظیمات</a>
        </div>
    </div>
</div>
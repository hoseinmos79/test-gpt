<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Handle export
if (isset($_GET['export']) && isset($_GET['form_name'])) {
    $form_name = sanitize_text_field($_GET['form_name']);
    $submissions = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}smsfb_submissions WHERE form_name = %s ORDER BY created_at DESC",
        $form_name
    ));
    
    if ($submissions) {
        // Create Excel file
        require_once SMSFB_PLUGIN_PATH . 'includes/excel-export.php';
        $exporter = new SMSFB_Excel_Export();
        $exporter->export_submissions($submissions, $form_name);
        exit;
    }
}

// Get forms for filter
$forms = $wpdb->get_results("SELECT DISTINCT form_name FROM {$wpdb->prefix}smsfb_submissions ORDER BY form_name");

// Get submissions with filter
$form_filter = isset($_GET['form_name']) ? sanitize_text_field($_GET['form_name']) : '';
$where_clause = $form_filter ? $wpdb->prepare("WHERE form_name = %s", $form_filter) : "";

$submissions = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}smsfb_submissions $where_clause ORDER BY created_at DESC");
?>

<div class="wrap">
    <h1>ارسال‌ها</h1>
    
    <div class="smsfb-submissions-container">
        <div class="smsfb-filter">
            <form method="get" action="">
                <input type="hidden" name="page" value="smsfb_submissions">
                <select name="form_name">
                    <option value="">همه فرم‌ها</option>
                    <?php foreach ($forms as $form): ?>
                        <option value="<?php echo esc_attr($form->form_name); ?>" <?php selected($form_filter, $form->form_name); ?>>
                            <?php echo esc_html($form->form_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" value="فیلتر" class="button">
            </form>
        </div>
        
        <?php if ($submissions): ?>
            <div class="smsfb-export">
                <?php if ($form_filter): ?>
                    <a href="?page=smsfb_submissions&export=1&form_name=<?php echo urlencode($form_filter); ?>" class="button button-primary">
                        دانلود اکسل
                    </a>
                <?php endif; ?>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>فرم</th>
                        <th>اطلاعات</th>
                        <th>IP</th>
                        <th>تاریخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td><?php echo esc_html($submission->form_name); ?></td>
                            <td>
                                <?php 
                                $form_data = json_decode($submission->form_data, true);
                                if ($form_data) {
                                    $field_labels = array(
                                        'first_name' => 'نام',
                                        'last_name' => 'نام خانوادگی',
                                        'phone' => 'شماره تماس',
                                        'email' => 'ایمیل',
                                        'description' => 'توضیح',
                                        'province' => 'استان',
                                        'city' => 'شهر'
                                    );
                                    
                                    $display_data = array();
                                    foreach ($form_data as $key => $value) {
                                        $label = $field_labels[$key] ?? $key;
                                        $display_data[] = "<strong>{$label}:</strong> " . esc_html($value);
                                    }
                                    echo implode('<br>', $display_data);
                                }
                                ?>
                            </td>
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
</div>
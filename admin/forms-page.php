<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Handle form actions
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'add_form' && wp_verify_nonce($_POST['_wpnonce'], 'smsfb_add_form')) {
        $form_name = sanitize_text_field($_POST['form_name']);
        $form_fields = isset($_POST['form_fields']) ? $_POST['form_fields'] : array();
        $required_fields = isset($_POST['required_fields']) ? $_POST['required_fields'] : array();
        
        $wpdb->insert(
            $wpdb->prefix . 'smsfb_forms',
            array(
                'form_name' => $form_name,
                'form_fields' => json_encode($form_fields),
                'required_fields' => json_encode($required_fields)
            )
        );
        
        echo '<div class="notice notice-success"><p>فرم با موفقیت اضافه شد.</p></div>';
    }
    
    if ($_POST['action'] === 'delete_form' && wp_verify_nonce($_POST['_wpnonce'], 'smsfb_delete_form')) {
        $form_id = intval($_POST['form_id']);
        $wpdb->delete($wpdb->prefix . 'smsfb_forms', array('id' => $form_id));
        
        echo '<div class="notice notice-success"><p>فرم با موفقیت حذف شد.</p></div>';
    }
}

$forms = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}smsfb_forms ORDER BY created_at DESC");
$available_fields = array(
    'first_name' => 'نام',
    'last_name' => 'نام خانوادگی',
    'phone' => 'شماره تماس',
    'email' => 'ایمیل',
    'description' => 'توضیح کوتاه',
    'province' => 'استان',
    'city' => 'شهر'
);
?>

<div class="wrap">
    <h1>فرم‌های ساخته‌شده</h1>
    
    <div class="smsfb-forms-container">
        <div class="smsfb-add-form">
            <h2>افزودن فرم جدید</h2>
            <form method="post" action="">
                <?php wp_nonce_field('smsfb_add_form'); ?>
                <input type="hidden" name="action" value="add_form">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">نام فرم</th>
                        <td>
                            <input type="text" name="form_name" class="regular-text" required>
                            <p class="description">نام یکتا برای فرم (فقط حروف انگلیسی و اعداد)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">فیلدهای فرم</th>
                        <td>
                            <?php foreach ($available_fields as $field_key => $field_label): ?>
                                <label>
                                    <input type="checkbox" name="form_fields[]" value="<?php echo $field_key; ?>">
                                    <?php echo $field_label; ?>
                                </label><br>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">فیلدهای اجباری</th>
                        <td>
                            <?php foreach ($available_fields as $field_key => $field_label): ?>
                                <label>
                                    <input type="checkbox" name="required_fields[]" value="<?php echo $field_key; ?>">
                                    <?php echo $field_label; ?>
                                </label><br>
                            <?php endforeach; ?>
                            <p class="description">فیلدهای انتخاب شده اجباری خواهند بود</p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="افزودن فرم">
                </p>
            </form>
        </div>
        
        <div class="smsfb-forms-list">
            <h2>فرم‌های موجود</h2>
            <?php if ($forms): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>نام فرم</th>
                            <th>فیلدها</th>
                            <th>فیلدهای اجباری</th>
                            <th>شورت‌کد</th>
                            <th>تاریخ ایجاد</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($forms as $form): ?>
                            <tr>
                                <td><?php echo esc_html($form->form_name); ?></td>
                                <td>
                                    <?php 
                                    $fields = json_decode($form->form_fields, true);
                                    $field_labels = array();
                                    foreach ($fields as $field) {
                                        $field_labels[] = $available_fields[$field] ?? $field;
                                    }
                                    echo esc_html(implode(', ', $field_labels));
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $required = json_decode($form->required_fields, true);
                                    $required_labels = array();
                                    foreach ($required as $field) {
                                        $required_labels[] = $available_fields[$field] ?? $field;
                                    }
                                    echo esc_html(implode(', ', $required_labels));
                                    ?>
                                </td>
                                <td><code>[sms_form name="<?php echo esc_attr($form->form_name); ?>"]</code></td>
                                <td><?php echo esc_html($form->created_at); ?></td>
                                <td>
                                    <form method="post" action="" style="display:inline;">
                                        <?php wp_nonce_field('smsfb_delete_form'); ?>
                                        <input type="hidden" name="action" value="delete_form">
                                        <input type="hidden" name="form_id" value="<?php echo $form->id; ?>">
                                        <button type="submit" class="button button-small" onclick="return confirm('آیا مطمئن هستید؟')">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>هنوز فرمی ایجاد نشده است.</p>
            <?php endif; ?>
        </div>
    </div>
</div>